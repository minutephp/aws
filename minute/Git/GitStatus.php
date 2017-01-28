<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 9/6/2016
 * Time: 8:38 AM
 */
namespace Minute\Git {

    use App\Config\BootLoader;
    use Minute\Event\ImportEvent;

    class GitStatus {
        /**
         * @var BootLoader
         */
        private $bootLoader;

        /**
         * GitStatus constructor.
         *
         * @param BootLoader $bootLoader
         */
        public function __construct(BootLoader $bootLoader) {
            $this->bootLoader = $bootLoader;
        }

        public function getStatus(ImportEvent $event) {
            chdir($this->bootLoader->getBaseDir());
            $output = `git status`;
            $status = ['status' => preg_match('/Changes not staged/', $output) ? 'uncommitted' : 'ok'];
            $event->setContent($status);
        }
    }
}