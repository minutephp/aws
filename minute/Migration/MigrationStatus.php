<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 10/6/2016
 * Time: 2:14 AM
 */
namespace Minute\Migration {

    use App\Config\BootLoader;
    use Minute\Event\ImportEvent;
    use Minute\Shell\Shell;

    class MigrationStatus {
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

        public function getStatus(ImportEvent $event) {
            $base = $this->bootLoader->getBaseDir();
            chdir($base);

            $cmd     = sprintf('%s/vendor/bin/phinx status', $base);
            $status  = $this->shell->exec($cmd);
            $results = ['pending' => $status['code'] === 1, 'migrations' => []];

            if ($lines = $status['output'] ?? null) {
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (preg_match('/^(up|down)\s+(\d+)\s+/', $line, $matches)) {
                        $parts = preg_split('/\s+/', $line);

                        if (($parts[0] === 'up') && (count($parts) === 7)) {
                            array_unshift($results['migrations'], ['type' => $parts[0], 'id' => $parts[1], 'started' => "$parts[2] $parts[3]", 'name' => $parts[6]]);
                        } elseif (($parts[0] === 'down') && (count($parts) === 3)) {
                            array_unshift($results['migrations'], ['type' => $parts[0], 'id' => $parts[1], 'name' => $parts[2]]);
                        }
                    }
                }
            }

            $event->setContent($results ?? []);
        }
    }
}