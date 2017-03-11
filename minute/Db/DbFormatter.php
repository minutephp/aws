<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 3/10/2017
 * Time: 8:43 AM
 */
namespace Minute\Db {

    use Minute\Database\Database;
    use Minute\File\TmpDir;
    use Minute\Shell\Shell;

    class DbFormatter {
        /**
         * @var Database
         */
        private $database;
        /**
         * @var Shell
         */
        private $shell;
        /**
         * @var TmpDir
         */
        private $tmpDir;

        /**
         * DbFormatter constructor.
         *
         * @param Database $database
         * @param Shell $shell
         * @param TmpDir $tmpDir
         */
        public function __construct(Database $database, Shell $shell, TmpDir $tmpDir) {
            $this->database = $database;
            $this->shell    = $shell;
            $this->tmpDir   = $tmpDir;
        }

        public function format($remote, $tweak) {
            header('Content-Type: text/plain');

            $cnf = $this->tmpDir->getTempDir('mysql') . '/.htpasswd'; //just in case!
            $sql = $this->tmpDir->getTempDir('mysql') . '/.htdump';

            try {
                $local = $this->database->getDsn();
                $cred  = sprintf("[mysqldump]\nuser=%s\npassword=%s\nhost=%s\n\n[mysql]\nuser=%s\npassword=%s\nhost=%s\n",
                    $local['username'], $local['password'], $local['host'],
                    $remote['RDS_USERNAME'], $remote['RDS_PASSWORD'], $remote['RDS_HOSTNAME']);

                file_put_contents($cnf, $cred);

                printf("Copying local database to RDS instance (%s)..\n\n", $remote['RDS_HOSTNAME']);

                $cnf = realpath($cnf);
                $cmd = sprintf('mysqldump --defaults-extra-file="%s" --opt --default-character-set=UTF8 --single-transaction --quick %s > "%s"', $cnf, $local['database'], $sql);
                $run = $this->shell->raw($cmd);

                if ($run['code'] === 0) {
                    if ($tweak === 'true') {
                        $dump = file_get_contents($sql);
                        $dump = preg_replace('/\)\s+ENGINE=MyISAM/', ') ENGINE=InnoDB', $dump);
                        file_put_contents($sql, $dump);
                    }

                    $cmd  = sprintf('mysql --defaults-extra-file="%s"  -v %s < "%s"', $cnf, $remote['RDS_DB_NAME'], realpath($sql));
                    $run  = $this->shell->run($cmd);
                    $pass = $run['code'] === 0;

                    printf("Mysql exit code: %s\n\n", $run['code']);

                    if (!empty($pass)) {
                        print "**Success** All done! RDS instance successfully initialized!\n";

                        return true;
                    } else {
                        print "**Error** Unable to connect to remote mysql server. Please make sure \"mysqldump\" and \"mysql\" are in your PATH.\n\n";
                    }
                } else {
                    print "**Error** Unable to run \"mysqldump\" on localhost. Please make sure \"mysqldump\" and \"mysql\" are in your PATH.\n\n";
                }
            } finally {
                @unlink($cnf);
                @unlink($sql);
            }

            return false;
        }
    }
}