<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 11/5/2016
 * Time: 11:04 AM
 */

namespace Minute\Todo {

    use Minute\Config\Config;
    use Minute\Event\ImportEvent;

    class AwsTodo {
        /**
         * @var TodoMaker
         */
        private $todoMaker;
        /**
         * @var Config
         */
        private $config;

        /**
         * MailerTodo constructor.
         *
         * @param TodoMaker $todoMaker - This class is only called by TodoEvent (so we assume TodoMaker is be available)
         * @param Config $config
         */
        public function __construct(TodoMaker $todoMaker, Config $config) {
            $this->todoMaker = $todoMaker;
            $this->config    = $config;
        }

        public function getTodoList(ImportEvent $event) {
            $domain = $this->config->getPublicVars('domain');
            $host   = $this->config->getPublicVars('host');
            $cdnUrl = 'https://console.aws.amazon.com/cloudfront/home';

            $cloud[] = ['name' => 'Enable S3 bucket for uploads', 'description' => 'To manage user uploads',
                        'status' => $this->config->get('aws/uploads/upload_bucket') ? 'complete' : 'incomplete', 'link' => '/admin/aws/cdn'];

            $cloud[] = ['name' => 'Setup CDN for uploads', 'description' => 'For better performance',
                        'status' => $this->config->get('aws/uploads/cdn_cname') ? 'complete' : 'incomplete', 'link' => '/admin/aws/cdn'];

            if ($this->config->get('aws/uploads/cdn_host')) {
                $cloud[] = $this->todoMaker->createManualItem("cdn-upload-ssl", "Replace default Cloudfront certificate with Custom SSL Certificate", 'To load uploads via https', $cdnUrl);
            }

            $cloud[] = ['name' => 'Setup CDN for "static" urls', 'description' => 'For faster site loading of everything under "/static" folder',
                        'status' => $this->config->get('aws/static/cdn_cname') ? 'complete' : 'incomplete', 'link' => '/admin/aws/cdn'];

            if ($this->config->get('aws/static/cdn_host')) {
                $cloud[] = $this->todoMaker->createManualItem("cdn-static-ssl", "Replace default Cloudfront certificate with Custom SSL Certificate", 'To load static assets via https', $cdnUrl);
            }

            $ses[] = $this->todoMaker->createManualItem("add-to-verified-senders", "Add $domain to verified senders", 'In Amazon SES', 'http://console.aws.amazon.com/ses/home?#verified-senders-domain:');
            $ses[] = $this->todoMaker->createManualItem("add-dkim", "Add SPF / DKIM keys for $domain", 'In Route 53', 'http://console.aws.amazon.com/ses/home?#verified-senders-domain:');
            $ses[] = $this->todoMaker->createManualItem("check-mx", "Check $domain on MXToolBox", 'For higher email delivery rates', "http://mxtoolbox.com/domain/$domain/");

            $google = 'https://www.google.com/search?q=Configuring+Amazon+SNS+Notifications+for+Amazon+SES';
            $ses[]  = $this->todoMaker->createManualItem("ses-bounce-handler", "Create a SNS topic to handle bounces", "Endpoint: $host/_aws/sns/bounce", $google);
            $ses[]  = $this->todoMaker->createManualItem("ses-spam-handler", "Create a SNS topic to handle spam complaints", "Endpoint: $host/_aws/sns/spam", $google);

            $ssl[] = $this->todoMaker->createManualItem("setup-ssl-for-site", "Setup ssl for site using AWS Certificate manager", 'Create a wildcard SSL in AWS to add SSL support',
                'https://console.aws.amazon.com/acm/home?region=us-east-1');

            $event->addContent(['AWS Cloud' => $cloud]);
            $event->addContent(['AWS Email' => $ses]);
            $event->addContent(['SSL Support' => $ssl]);
        }
    }
}