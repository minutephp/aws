<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 7/8/2016
 * Time: 7:57 PM
 */
namespace Minute\Menu {

    use Minute\Config\Config;
    use Minute\Database\Database;
    use Minute\Deployer\Deployer;
    use Minute\Event\ImportEvent;

    class AwsMenu {
        /**
         * @var Database
         */
        private $database;
        /**
         * @var Config
         */
        private $config;

        /**
         * AwsMenu constructor.
         *
         * @param Database $database
         * @param Config $config
         */
        public function __construct(Database $database, Config $config) {
            $this->database = $database;
            $this->config   = $config;
        }

        public function adminLinks(ImportEvent $event) {
            $online = $this->database->hasRdsAccess();
            $links  = ['amazon' => ['title' => 'AWS', 'icon' => 'fa-amazon', 'priority' => 900]];

            if (!$online) {
                $links = array_merge($links, [
                    'aws' => ['title' => 'IAM Access', 'href' => '/admin/aws/setup', 'icon' => 'fa-lock', 'priority' => 1, 'parent' => 'amazon'],
                    'cdn' => ['title' => 'Uploads & CDN', 'href' => '/admin/aws/cdn', 'icon' => 'fa-cloud', 'priority' => 4, 'parent' => 'amazon'],
                    'deploy' => ['title' => 'Deploy site', 'href' => '/admin/aws/deploy', 'icon' => 'fa-cloud-upload', 'priority' => 2, 'parent' => 'amazon']
                ]);

                if ($rds = $this->config->get(Deployer::RDS_KEY)) {
                    $links['setup-db'] = ['title' => 'Setup database', 'href' => '/admin/aws/db', 'icon' => 'fa-database', 'priority' => 5, 'parent' => 'amazon'];
                }
            } else {
                $links = array_merge($links, [
                    'migrations' => ['title' => 'Db Migrations', 'href' => '/admin/aws/migrations', 'icon' => 'fa-database', 'priority' => 1, 'parent' => 'amazon'],
                ]);
            }

            $event->addContent($links);
        }
    }
}
