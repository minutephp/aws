<?php
/**
 * Created by: MinutePHP framework
 */
namespace App\Controller\Admin\Aws {

    use Minute\Config\Config;
    use Minute\Crypto\JwtEx;
    use Minute\Database\Database;
    use Minute\Deployer\Deployer;
    use Minute\Error\AwsError;
    use Minute\File\TmpDir;
    use Minute\Shell\Shell;

    class DbCopy {
        /**
         * @var Config
         */
        private $config;
        /**
         * @var JwtEx
         */
        private $jwt;
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
         * DbCopy constructor.
         *
         * @param Config $config
         * @param JwtEx $jwt
         * @param Database $database
         * @param Shell $shell
         * @param TmpDir $tmpDir
         */
        public function __construct(Config $config, JwtEx $jwt, Database $database, Shell $shell, TmpDir $tmpDir) {
            $this->config   = $config;
            $this->jwt      = $jwt;
            $this->database = $database;
            $this->shell    = $shell;
            $this->tmpDir   = $tmpDir;
        }

        public function index($tweak) {
            header('Content-Type: text/plain');

            $cnf = $this->tmpDir->getTempDir('mysql') . '/.htpasswd'; //just in case!
            $sql = $this->tmpDir->getTempDir('mysql') . '/.htdump';

            try {
                if ($rds = $this->config->get(Deployer::RDS_KEY)) {
                    if ($remote = (array) $this->jwt->decode($rds)) {
                        $local = $this->database->getDsn();
                        $cred  = sprintf("[mysqldump]\nuser=%s\npassword=%s\nhost=%s\n\n[mysql]\nuser=%s\npassword=%s\nhost=%s\n",
                            $local['username'], $local['password'], $local['host'],
                            $remote['RDS_USERNAME'], $remote['RDS_PASSWORD'], $remote['RDS_HOSTNAME']);

                        file_put_contents($cnf, $cred);

                        printf("Copying local database to RDS instance (%s)..\n\n", $remote['RDS_HOSTNAME']);

                        $cnf = realpath($cnf);
                        $cmd = sprintf('mysqldump --defaults-extra-file="%s" --opt --default-character-set=UTF8 --single-transaction %s > "%s"', $cnf, $local['database'], $sql);
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
                            } else {
                                print "**Error** Unable to connect to remote mysql server. Please make sure \"mysqldump\" and \"mysql\" are in your PATH.\n\n";
                            }
                        } else {
                            print "**Error** Unable to run \"mysqldump\" on localhost. Please make sure \"mysqldump\" and \"mysql\" are in your PATH.\n\n";
                        }
                    }
                }
            } finally {
                @unlink($cnf);
                @unlink($sql);
            }
        }
    }
}

