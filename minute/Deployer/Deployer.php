<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 8/29/2016
 * Time: 2:51 PM
 */

namespace Minute\Deployer {

    use App\Config\BootLoader;
    use Aws\S3\S3Client;
    use Illuminate\Support\Str;
    use Minute\Aws\Client;
    use Minute\Config\Config;
    use Minute\Crypto\JwtEx;
    use Minute\Error\AwsError;
    use Minute\Event\Dispatcher;
    use Minute\Event\DockerEvent;
    use Minute\View\StringView;
    use Minute\Zip\ZipFile;

    class Deployer {
        const RDS_KEY = Client::AWS_KEY . '/deployment/instances/rds';

        protected $region = 'us-east-1';
        /**
         * @var Client
         */
        private $client;
        /**
         * @var \Minute\Zip\ZipFile
         */
        private $zipFile;
        /**
         * @var Config
         */
        private $config;
        /**
         * @var Dispatcher
         */
        private $dispatcher;
        /**
         * @var JwtEx
         */
        private $jwt;
        /**
         * @var BootLoader
         */
        private $bootLoader;
        /**
         * @var StringView
         */
        private $view;

        /**
         * Deployer constructor.
         *
         * @param Client $client
         * @param ZipFile $zipFile
         * @param Config $config
         * @param Dispatcher $dispatcher
         * @param JwtEx $jwt
         * @param BootLoader $bootLoader
         * @param StringView $view
         */
        public function __construct(Client $client, ZipFile $zipFile, Config $config, Dispatcher $dispatcher, JwtEx $jwt, BootLoader $bootLoader, StringView $view) {
            set_time_limit(0);

            $this->client     = $client;
            $this->zipFile    = $zipFile;
            $this->config     = $config;
            $this->dispatcher = $dispatcher;
            $this->jwt        = $jwt;
            $this->bootLoader = $bootLoader;
            $this->view       = $view;
        }

        public function deploy($settings) {
            $appName = $settings['app_name'];
            $dryRun  = $settings['dry_run'] ?? '' === 'true';
            $html    = '';

            if ($db = $this->createDb("$appName-db", $settings['rds']['size'], $settings['rds']['instance'])) {
                if (!empty($db['RDS_PASSWORD'])) {
                    $this->config->set(self::RDS_KEY, $this->jwt->encode((object) $db), true);
                } else {
                    $rds = $this->config->get(self::RDS_KEY);
                    $db  = $this->jwt->decode($rds);
                }

                $settings['rds'] = array_merge($settings['rds'], (array) $db);
            }

            foreach (['web', 'worker'] as $type) {
                if (!empty($settings[$type]['instance'])) {
                    $tags = $this->config->getPublicVars();;
                    $tags['email'] = $this->config->get('private/owner_email', sprintf('webmaster@%s', $tags['domain']));

                    $event = new DockerEvent($settings, $type, $tags);
                    $this->dispatcher->fire(DockerEvent::DOCKER_INCLUDE_FILES, $event);
                    $zip = $this->zipFile->create($event->getFiles(), "$appName-$type-deploy.zip", $event->getTags());

                    if (!$dryRun) {
                        if ($version = $this->createApp($appName, $type, $zip)) {
                            if ($settings['cdn_reload'] == 'true') {
                                if ($static = $this->config->get(Client::AWS_KEY . "/static")) {
                                    if ($static['cloudfront_enabled'] == 'true') {
                                        $cloudfront = $this->client->getCloudfrontClient();

                                        foreach ($cloudfront->getIterator('ListDistributions') as $distribution) {
                                            if ($distribution['DomainName'] == $static['cdn_cname']) {
                                                $reference = "refresh-" . $this->getGitVersion();

                                                $cloudfront->createInvalidation([
                                                    'DistributionId' => $distribution['Id'],
                                                    'InvalidationBatch' => [
                                                        'Paths' => ['Quantity' => 1, 'Items' => ['/static/*']],
                                                        'CallerReference' => $reference
                                                    ]
                                                ]);

                                                break;
                                            }
                                        }
                                    }
                                }
                            }

                            if ($instance = $settings[$type]['instance'] ?? null) {
                                $env    = $this->createEnv($appName, $version, $type, $instance, $settings['keypair'], $settings['singleInstance'] !== true);
                                $domain = $this->config->getPublicVars('domain');

                                if (($type === 'web') && !empty($env)) {
                                    $html .= sprintf('<table class="table table-striped"><caption>Your DNS (Route 53) settings are:</caption>');
                                    $html .= sprintf('<thead><tr><td>Name</td><td>Type</td><td>Value</td></tr></thead><tbody>');
                                    $html .= sprintf('<tr><th>*.%s</th><th>%s</th><th>%s</th></tr>', $domain, 'CNAME', $env->get('CNAME') ?: 'ELB address');

                                    foreach (['static', 'uploads'] as $cname) {
                                        if ($conf = $this->config->get(Client::AWS_KEY . "/$cname")) {
                                            if (!empty($conf['cdn_cname'])) {
                                                $html .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>', $conf['cdn_host'], 'CNAME', $conf['cdn_cname']);
                                            }
                                        }
                                    }

                                    $html .= sprintf('</tbody></table>');
                                    $html .= sprintf('<div class="alert alert-danger"><small>*</small> You may need to setup your database ONCE after the first deployment (required only once per site)</div>');
                                    //$html .= sprintf('<p><small>*</small> Create a S3 bucket named "%s" to redirect all traffic to www.%s (naked domain redirect)</p>', $domain, $domain);
                                    $html .= sprintf('<p><small>*</small> For `https` sites, make sure port 443 is open and <abbr title="Elastic load balancer">ELB</abbr> is listening on it.</p>');
                                    $html .= sprintf('<p><small>*</small> You can copy-paste this URL (see address bar) as a Web-hook in your Git repository (for automatic deployments on `git push`)</p>');

                                    $html = sprintf('<div class="container">%s</div>', $html);
                                }
                            }
                        }
                    } else {
                        $tmp  = $this->bootLoader->getBaseDir() . '/public/tmp';
                        $name = basename($zip);
                        @mkdir($tmp, 0777, true);
                        @copy($zip, "$tmp/" . $name);
                        $html .= sprintf('<p><a href="/tmp/%s">Download deployment files for %s</a></p>', $name, $type);
                    }
                }
            }

            return $this->view->setContentWithLayout($html);
        }

        protected function createApp($appName, $type, $zip) {
            $eb = $this->client->getBeanstalkClient();

            try {
                $eb->createApplication(['ApplicationName' => $appName, 'Description' => 'MinutePHP automatic website deployment']);
            } catch (\Throwable $e) {
                if (!preg_match("/already exists/", $e->getMessage())) {
                    throw new $e;
                }
            }

            try {

                /** @var S3Client $s3 */
                $s3      = $this->client->getS3Client();
                $config  = $this->config->get(Client::AWS_KEY . '/services/s3');
                $bucket  = $config['bucket'] ?? sprintf('www.%s', $this->config->getPublicVars('domain'));
                $key     = sprintf("tmp/deploy/%s", basename($zip));
                $version = "$type-" . $this->getGitVersion();

                $s3->putObject(['Bucket' => $bucket, 'Key' => $key, 'SourceFile' => $zip, 'ACL' => 'private']);
            } catch (\Throwable $e) {
                throw new AwsError("Unable to upload zip file to S3: " . $e->getMessage());
            }

            try {
                $eb->createApplicationVersion([
                    'ApplicationName' => $appName,
                    'Description' => 'Created with MinutePHP',
                    'VersionLabel' => $version,
                    'AutoCreateApplication' => true,
                    'SourceBundle' => ['S3Bucket' => $bucket, 'S3Key' => $key]
                ]);
            } catch (\Throwable $e) {
                if (!preg_match("/already exists/", $e->getMessage())) {
                    throw new $e;
                }
            }

            return $version;
        }

        protected function createDb($ident, $size, $instance) {
            $rds = $this->client->getRdsClient();
            $ec2 = $this->client->getEc2Client();
            $db  = 'ebdb';
            $env = [];
            $sg  = "$ident-sg";

            try {
                $group = $ec2->createSecurityGroup(['GroupName' => $sg, 'Description' => "RDS: $ident"]);
                if ($group_id = $group['GroupId']) { //only eb sg should be allowed as source - will fix this in next version!
                    $ec2->authorizeSecurityGroupIngress(['GroupId' => $group['GroupId'], 'IpProtocol' => 'TCP', 'FromPort' => 3306, 'ToPort' => 3306, 'CidrIp' => '0.0.0.0/0']);
                }
            } catch (\Throwable $e) {
                if (preg_match("/already exists/", $e->getMessage())) {
                    $groups   = $ec2->describeSecurityGroups(['Filters' => [['Name' => 'group-name', 'Values' => [$sg]]]]);
                    $group_id = $groups->get('SecurityGroups')[0]['GroupId'];
                }
            }

            try {
                $pass = Str::random(10);

                $rds->createDBInstance([
                    'Engine' => 'MySQL',
                    'AllocatedStorage' => $size ?: 5,
                    'DBInstanceClass' => $instance ?: 'db.t2.micro',
                    'DBInstanceIdentifier' => $ident,
                    'DBName' => 'ebdb',
                    'MasterUsername' => 'root',
                    'MasterUserPassword' => $pass,
                    'VpcSecurityGroupIds' => [$group_id ?? 'default'],
                ]);

                $rds->waitUntil('DBInstanceAvailable', ['DBInstanceIdentifier' => $ident]);
                $env = ['RDS_USERNAME' => 'root', 'RDS_PASSWORD' => $pass, 'RDS_DB_NAME' => $db];
            } catch (\Throwable $e) {
            }

            $result   = $rds->describeDBInstances(['DBInstanceIdentifier' => $ident]);
            $endpoint = $result['DBInstances'][0]['Endpoint']['Address'];

            //return ['RDS_USERNAME' => 'root', 'RDS_PASSWORD' => 'reset1234', 'RDS_DB_NAME' => $db, 'RDS_HOSTNAME' => $endpoint];
            return !empty($endpoint) ? array_merge($env, ['RDS_HOSTNAME' => $endpoint]) : null;
        }

        protected function createEnv($app, $version, $type, $instance, $keyPair = '', $autoScaling = true) {
            $eb    = $this->client->getBeanstalkClient();
            $ident = "$app-$type";

            foreach ($eb->listAvailableSolutionStacks() as $solutions) {
                foreach ($solutions as $solution) {
                    if (preg_match('/docker/i', $solution)) {
                        $template = $solution;
                        break(2);
                    }
                }
            }

            if (!empty($template)) {
                try {
                    $options = ['OptionSettings' => [
                        ['Namespace' => 'aws:autoscaling:launchconfiguration', 'OptionName' => 'InstanceType', 'Value' => $instance],
                        ['Namespace' => 'aws:autoscaling:launchconfiguration', 'OptionName' => 'IamInstanceProfile', 'Value' => 'aws-elasticbeanstalk-ec2-role'],
                        ['Namespace' => 'aws:elasticbeanstalk:application', 'OptionName' => 'Application Healthcheck URL', 'Value' => '/_aws/health'],
                    ]];

                    if (!empty($keyPair)) {
                        array_push($options['OptionSettings'], ['Namespace' => 'aws:autoscaling:launchconfiguration', 'OptionName' => 'EC2KeyName', 'Value' => $keyPair]);
                    }

                    if (!$autoScaling) {
                        array_push($options['OptionSettings'], ['Namespace' => 'aws:elasticbeanstalk:environment', 'OptionName' => 'EnvironmentType', 'Value' => 'SingleInstance']);
                    }

                    //die('web worker needs to specify http path (URL on localhost where messages will be forwarded as HTTP POST requests.)');
                    ///die('also HTTP connections');

                    $params = array_merge(
                        ['ApplicationName' => $app, 'EnvironmentName' => $ident, 'SolutionStackName' => $template, 'VersionLabel' => $version],
                        ['Tier' => $type === 'worker' ? ['Type' => 'SQS/HTTP', 'Name' => 'Worker'] : ['Type' => 'Standard', 'Name' => 'WebServer']],
                        $options
                    );

                    $env = $eb->createEnvironment($params);
                } catch (\Throwable $e) {
                    if (preg_match("/already exists/", $e->getMessage())) {
                        $env = $eb->updateEnvironment(['ApplicationName' => $app, 'EnvironmentName' => $ident, 'VersionLabel' => $version]);
                    } else {
                        throw new AwsError($e->getMessage());
                    }
                }
            }

            return $env ?? null;
        }

        private function getGitVersion() {
            return trim(`git rev-parse HEAD`) ?: Str::random(16);
        }
    }
}