<?php
/**
 * Created by: MinutePHP framework
 */

namespace App\Controller\Admin\Aws {

    use App\Controller\Generic\DefaultPostHandler;
    use Illuminate\Support\Str;
    use Minute\Aws\Client;
    use Minute\Config\Config;
    use Minute\Error\AwsError;
    use Minute\Error\PrintableError;
    use Minute\Http\HttpRequestEx;
    use Minute\Routing\RouteEx;

    class Cdn {
        /**
         * @var DefaultPostHandler
         */
        private $postHandler;
        /**
         * @var Client
         */
        private $client;
        /**
         * @var Config
         */
        private $config;

        /**
         * Cdn constructor.
         *
         * @param DefaultPostHandler $postHandler
         * @param Client $client
         * @param Config $config
         */
        public function __construct(DefaultPostHandler $postHandler, Client $client, Config $config) {
            $this->postHandler = $postHandler;
            $this->client      = $client;
            $this->config      = $config;
        }

        public function index(string $_mode, array $_models, RouteEx $_route, array $_parents, HttpRequestEx $request, string $alias) {
            //added headers and OPTIONS in Allowed methods
            $this->postHandler->index($_mode, $_models, $_route, $_parents, $alias);

            if ($settings = json_decode($request->getParameter('items')[0]['data_json'], true)) {
                if ($bucket = $settings['uploads']['upload_bucket']) {
                    try {
                        $s3    = $this->client->getS3Client();
                        $rules = [['AllowedOrigins' => ['*', 'http://*', 'https://*'], 'AllowedMethods' => ['GET', 'POST', 'OPTIONS'], 'AllowedHeaders' => ['*'],
                                   'ExposeHeaders' => ['ETag', 'Access-Control-Allow-Origin'], 'MaxAgeSeconds' => 3000]];

                        if (!$s3->doesBucketExist($bucket) || !$s3->headBucket(array('Bucket' => $bucket))) {
                            $s3->createBucket(array('Bucket' => $bucket, 'ACL' => 'public-read'));
                            $s3->waitUntil('BucketExists', array('Bucket' => $bucket));
                        }

                        $s3->putBucketPolicy(['Bucket' => $bucket, 'Policy' => json_encode([
                            'Statement' => [[
                                                'Sid' => 'PublicReadForGetBucketObjects',
                                                'Action' => ['s3:GetObject'],
                                                'Effect' => 'Allow',
                                                'Resource' => ["arn:aws:s3:::{$bucket}", "arn:aws:s3:::{$bucket}/*"],
                                                'Principal' => ['AWS' => ["*"]]
                                            ]
                            ]])
                        ]);

                        $s3->putBucketCors(['Bucket' => $bucket, 'CORSConfiguration' => ['CORSRules' => $rules]]);
                    } catch (\Throwable $e) {
                        throw new PrintableError("Cannot create bucket: $bucket. Try a different bucket name");
                    }
                }

                $cloudfront = $this->client->getCloudfrontClient();

                foreach (['uploads', 'static'] as $type) {
                    $cdn = $settings[$type];

                    if ($cdn['cloudfront_enabled'] ?? null) {
                        $setupS3  = $type === 'uploads';
                        $domain   = $this->config->getPublicVars('domain');
                        $origin   = $setupS3 ? sprintf('%s.s3.amazonaws.com', $bucket) : "www.$domain";
                        $originId = sprintf('%s-%s', $setupS3 ? 'S3' : 'Custom', $domain);
                        $cdn_host = $cdn['cdn_host'];
                        $headers  = ['CustomHeaders' => [
                            // Quantity is required
                            'Quantity' => 3,
                            'Items' => [
                                ['HeaderName' => 'Access-Control-Allow-Headers', 'HeaderValue' => 'Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'],
                                ['HeaderName' => 'Access-Control-Allow-Methods', 'HeaderValue' => 'POST, GET, OPTIONS'],
                                ['HeaderName' => 'Access-Control-Allow-Origin', 'HeaderValue' => '*'],
                            ],
                        ]];

                        if ($setupS3) {
                            $item = array_merge(['Id' => $originId, 'DomainName' => $origin, 'S3OriginConfig' => ['OriginAccessIdentity' => '']], $headers);
                        } else {
                            $item = array_merge(['Id' => $originId, 'DomainName' => $origin,
                                                 'CustomOriginConfig' => ['HTTPPort' => 80, 'HTTPSPort' => 443, 'OriginProtocolPolicy' => 'match-viewer']], $headers);
                        }

                        $params = ['DistributionConfig' => [
                            'Aliases' => ['Quantity' => 1, 'Items' => [$cdn_host]],
                            'CacheBehaviors' => ['Quantity' => 0],
                            'Comment' => "Created by Minute Framework",
                            'Enabled' => true,
                            'CallerReference' => Str::random(8),
                            'DefaultCacheBehavior' => [
                                'MinTTL' => 3600,
                                'ViewerProtocolPolicy' => 'allow-all',
                                'TargetOriginId' => $originId,
                                'TrustedSigners' => [
                                    'Enabled' => false,
                                    'Quantity' => 0
                                ],
                                'ForwardedValues' => [
                                    'QueryString' => true,
                                    'Cookies' => [
                                        'Forward' => 'none'
                                    ],
                                    'Headers' => [
                                        'Quantity' => 1,
                                        'Items' => ['Origin'],
                                    ],
                                ]
                            ],
                            'DefaultRootObject' => '/',
                            'Origins' => [
                                'Quantity' => 1,
                                'Items' => [$item]
                            ],
                            'PriceClass' => 'PriceClass_All'
                        ]];

                        try {
                            $cdn_cname = null;

                            foreach ($cloudfront->getIterator('ListDistributions') as $distribution) {
                                if ($distribution['Aliases']['Items'][0] == $cdn_host) {
                                    $cdn_cname = $distribution['DomainName'];
                                    break;
                                }
                            }

                            if (empty($cdn_cname)) {
                                $result    = $cloudfront->createDistribution($params);
                                $cdn_cname = $result['Distribution']['DomainName'] ?? '';
                            }

                            if (!empty($cdn_cname)) {
                                $this->config->set(Client::AWS_KEY . "/$type/cdn_cname", $cdn_cname, true);
                            }
                        } catch (\Throwable $e) {
                            if (!preg_match('/already associated/', $e->getMessage())) {
                                throw new PrintableError("Cannot create CDN: $cdn_host: " . $e->getMessage());
                            }
                        }
                    }
                }
            }
        }
    }
}