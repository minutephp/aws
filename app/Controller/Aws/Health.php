<?php
/**
 * Created by: MinutePHP framework
 */
namespace App\Controller\Aws {

    use Minute\Database\Database;
    use Minute\Error\AwsError;
    use Minute\Routing\RouteEx;
    use Minute\View\Helper;
    use Minute\View\View;

    class Health {
        /**
         * @var Database
         */
        private $database;

        /**
         * Health constructor.
         *
         * @param Database $database
         */
        public function __construct(Database $database) {
            $this->database = $database;
        }

        public function index() {
            $sth = $this->database->getPdo()->query('SELECT NOW()');
            $sth->execute();
            $row = $sth->fetch();

            return $row[0];
        }
    }
}