<?php
/**
 * Created by: MinutePHP framework
 */
namespace App\Controller\Admin\Migrations {

    use App\Config\BootLoader;
    use Minute\Routing\RouteEx;
    use Minute\Shell\Shell;
    use Minute\View\Helper;
    use Minute\View\View;

    class Run {
        /**
         * @var Shell
         */
        private $shell;
        /**
         * @var BootLoader
         */
        private $bootLoader;

        /**
         * MigrationStatus constructor.
         *
         * @param Shell $shell
         * @param BootLoader $bootLoader
         */
        public function __construct(Shell $shell, BootLoader $bootLoader) {
            $this->shell      = $shell;
            $this->bootLoader = $bootLoader;
        }

        public function index() {
            $base = $this->bootLoader->getBaseDir();
            chdir($base);

            $cmd = sprintf('%s/vendor/bin/phinx migrate', $base);

            print "<pre>";
            $this->shell->run($cmd);
            print "</pre>";

            exit;
        }
    }
}