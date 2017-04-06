<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 3/29/2017
 * Time: 6:28 PM
 */

namespace Minute\CDN {

    use Minute\Aws\Client;
    use Minute\Config\Config;

    class UrlResolver {
        /**
         * @var Config
         */
        private $config;

        /**
         * UrlResolver constructor.
         *
         * @param Config $config
         */
        public function __construct(Config $config) {
            $this->config = $config;
        }

        public function getUploadUrl(string $bucket, string $key) {
            $uploads = $this->config->get(Client::AWS_KEY . '/uploads', []);

            if (!empty($uploads['cloudfront_enabled']) && !empty($uploads['cdn_cname'])) {
                return sprintf('https://%s/%s', ($uploads['cdn_host'] ?? '') ?: $uploads['cdn_cname'], ltrim($key, '/'));
            } else {
                return sprintf('https://s3.amazonaws.com/%s/%s', $bucket, ltrim($key, '/'));
            }
        }
    }
}