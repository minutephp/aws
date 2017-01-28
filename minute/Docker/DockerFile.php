<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 8/25/2016
 * Time: 5:26 AM
 */
namespace Minute\Docker {

    use Minute\Aws\Client;
    use Minute\Config\Config;
    use Minute\Database\Database;
    use Minute\Error\AwsError;
    use Minute\Event\DockerEvent;

    class DockerFile {
        /**
         * @var Database
         */
        private $database;
        /**
         * @var Config
         */
        private $config;

        /**
         * DockerFile constructor.
         *
         * @param Database $database
         * @param Config $config
         */
        public function __construct(Database $database, Config $config) {
            $this->database = $database;
            $this->config   = $config;
        }

        public function create(DockerEvent $event) {
            $settings = $event->getSettings();

            $event->addContent('Dockerfile', sprintf("FROM %s\n", $settings['deployment']['docker_image'] ?? 'ubuntu:latest'));
            $event->addContent('Dockerfile', file_get_contents(sprintf('%s/data/Dockerfile', __DIR__)));
            $event->addContent('supervisord.conf', file_get_contents(sprintf('%s/data/supervisord.conf', __DIR__)));
            $event->addContent('evasive.conf', file_get_contents(sprintf('%s/data/evasive.conf', __DIR__)));

            foreach (['RDS_DB_NAME', 'RDS_HOSTNAME', 'RDS_PASSWORD', 'RDS_USERNAME'] as $var) {
                if ($value = $settings['rds'][$var]) {
                    $event->addContent('Dockerfile', sprintf('ENV %s %s', $var, $value));
                } else {
                    throw new AwsError("Cannot get $var. You may need to modify your RDS database and update config manually.");
                }
            }
        }

        public function finish(DockerEvent $event) {
            $event->addContent('Dockerfile', 'RUN apt-get autoremove -y');

            if ($event->getType() === 'worker') {
                $event->addContent('Dockerfile', 'EXPOSE 8000');
                //$event->addContent('Dockerfile', 'CMD ["php", "-S", "0.0.0.0:8000", "-t", "/tmp/php-daemon"]');
            } else {
                $event->addContent('Dockerfile', 'EXPOSE 80');
            }

            //$event->addContent('Dockerfile', 'CMD ["/usr/sbin/apache2", "-D", "FOREGROUND"]');
            $event->addContent('Dockerfile', 'CMD ["/usr/bin/supervisord"]');
        }
    }
}