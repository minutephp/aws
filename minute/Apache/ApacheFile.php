<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 8/28/2016
 * Time: 1:21 AM
 */
namespace Minute\Apache {

    use Minute\Aws\Client;
    use Minute\Config\Config;
    use Minute\Event\DockerEvent;

    class ApacheFile {
        /**
         * @var Config
         */
        private $config;

        /**
         * DockerFile constructor.
         *
         * @param Config $config
         */
        public function __construct(Config $config) {
            $this->config = $config;
        }

        public function config(DockerEvent $event) {
            $settings = $event->getSettings();
            $rewrites = '';

            $event->addContent('Dockerfile', file_get_contents(sprintf('%s/data/Dockerfile', __DIR__)));

            if ($settings['repo_type'] === 'private') {
                $event->addContent('id_rsa', $settings['repo_ppk']);
                $event->addContent('config', "Host *\n\tUser                   root\n\tStrictHostKeyChecking  no");
                $event->addContent('Dockerfile', "ADD id_rsa /root/.ssh/id_rsa\nADD config /root/.ssh/config");
                $event->addContent('Dockerfile', "RUN chmod 0400 /root/.ssh/*");
            }

            $event->addContent('Dockerfile', "RUN echo CACHE_BUSTER_" . rand(1, 9999) . " > /dev/null");
            $event->addContent('Dockerfile', "RUN git clone {$settings['repo_url']} /var/www");

            if ($event->getType() === 'worker') {
                $event->addContent('cron.yaml', file_get_contents(sprintf('%s/data/cron.yaml', __DIR__)));
                $event->addContent('daemon.php', file_get_contents(sprintf('%s/data/daemon.php', __DIR__)));
                $event->addContent('Dockerfile', 'ADD daemon.php /var/www/cron/index.php');
            }

            $event->addContent('Dockerfile', file_get_contents(sprintf('%s/data/configure', __DIR__)));
            $event->addContent('minute.ini', file_get_contents(sprintf('%s/data/minute.ini', __DIR__)));

            if (!empty($settings['tweaks'])) {
                $event->addContent('Dockerfile', 'RUN cd /var/www && find . -type f -print0 | xargs -0 dos2unix -k -q; exit 0');
            }

            if (!empty($settings['https_only'])) {
                $rewrites .= "RewriteCond %{HTTP:X-Forwarded-Proto} !https\n\t" .
                             "RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]\n\n\t";
            }

            if (!empty($settings['cdn_enabled'])) {
                if ($cdn = $this->config->get(Client::AWS_KEY . '/static/cdn_cname')) {
                    $rewrites .= "RewriteCond %{HTTP:X-Forwarded-Proto} https  [OR]\n\t" .
                                 "RewriteCond %{HTTPS} on\n\t" .
                                 "RewriteRule ^/static/(.*) https://$cdn/static/$1 [R=301,L]\n\n\t" .
                                 "RewriteCond %{HTTP:X-Forwarded-Proto} !https\n\t" .
                                 "RewriteRule ^/static/(.*) http://$cdn/static/$1 [R=301,L]\n\n";
                }
            }

            $hash = array_merge(['path' => '/var/www/public', 'rewrites' => $rewrites], $this->config->getPublicVars());
            $dir  = realpath(sprintf('%s/data', __DIR__));

            $event->addTags($hash);

            if (($event->getType() === 'worker')) {
                $event->addContent('apache.conf', file_get_contents("$dir/worker.conf"));
            } else {
                $event->addContent('apache.conf', file_get_contents("$dir/web.conf"));
            }

            $event->addContent('rewrite.conf', file_get_contents("$dir/rewrite.conf"));
        }

        public function finish(DockerEvent $event) {
            $event->addContent('Dockerfile', "ADD apache.conf     /etc/apache2/sites-enabled/default.conf");
            $event->addContent('Dockerfile', "ADD rewrite.conf    /etc/apache2/rules/rewrite.conf");
            $event->addContent('Dockerfile', "RUN rm -f /root/.ssh/config");
        }
    }
}