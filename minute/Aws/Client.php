<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 5/14/2016
 * Time: 5:08 PM
 */

namespace Minute\Aws {

    use Aws\CloudFront\CloudFrontClient;
    use Aws\Ec2\Ec2Client;
    use Aws\ElasticBeanstalk\ElasticBeanstalkClient;
    use Aws\Rds\RdsClient;
    use Aws\S3\S3Client;
    use Aws\Ses\SesClient;
    use Aws\Sqs\SqsClient;
    use Minute\Config\Config;
    use Minute\Error\AwsError;

    class Client {
        const AWS_KEY = "aws";
        /**
         * @var array
         */
        protected $config;

        /**
         * Client constructor.
         *
         * @param Config $config
         */
        public function __construct(Config $config) {
            $this->config = $config;
        }

        /**
         * @param $service
         * @param array $config
         *
         * @return mixed
         * @throws AwsError
         */
        public function getClient($service, $config = null) {
            if ($config = $config ?: $this->config->get(self::AWS_KEY . "/services/$service")) {
                $map = ['s3' => S3Client::class, 'ses' => SesClient::class, 'beanstalk' => ElasticBeanstalkClient::class, 'ec2' => Ec2Client::class, 'cloudfront' => CloudFrontClient::class,
                        'rds' => RdsClient::class, 'sqs' => SqsClient::class];

                if ($obj = $map[$service] ?? $config['class'] ?? null) {
                    $defaults    = ['version' => 'latest', 'region' => $config['region'] ?? 'us-east-1', 'http' => ['verify' => false]];
                    $credentials = array_intersect_key($config, array_flip(['key', 'secret']));
                    $params      = array_merge($defaults, ['credentials' => $credentials]);

                    $client = new $obj($params);
                } else {
                    throw new AwsError("AWS $service is not supported");
                }
            } else {
                throw new AwsError("AWS $service has not been configured");
            }

            return $client ?? null;
        }

        /**
         * @param array $config
         *
         * @return S3Client
         * @throws AwsError
         */
        public function getS3Client($config = null) {
            $client = $this->getClient('s3', $config);
            $client->registerStreamWrapper();

            return $client;
        }

        /**
         * @param array $config
         *
         * @return SqsClient
         * @throws AwsError
         */
        public function getSqsClient($config = null) {
            return $this->getClient('sqs', $config);
        }

        /**
         * @param array $config
         *
         * @return SesClient
         * @throws AwsError
         */
        public function getSesClient($config = null) {
            return $this->getClient('ses', $config);
        }

        /**
         * @param array $config
         *
         * @return Ec2Client
         * @throws AwsError
         */
        public function getEc2Client($config = null) {
            return $this->getClient('ec2', $config);
        }

        /**
         * @param array $config
         *
         * @return ElasticBeanstalkClient
         * @throws AwsError
         */
        public function getBeanstalkClient($config = null) {
            return $this->getClient('beanstalk', $config);
        }

        /**
         * @param array $config
         *
         * @return RdsClient
         * @throws AwsError
         */
        public function getRdsClient($config = null) {
            return $this->getClient('rds', $config);
        }

        /**
         * @param array $config
         *
         * @return CloudFrontClient
         * @throws AwsError
         */
        public function getCloudfrontClient($config = null) {
            return $this->getClient('cloudfront', $config);
        }
    }
}