<?php
/**
 * Created by: MinutePHP framework
 */
namespace App\Controller\Aws {

    use Minute\Config\Config;
    use Minute\Deployer\Deployer;
    use Minute\Error\AwsError;
    use Minute\Http\HttpRequestEx;
    use Minute\Model\ModelEx;

    class Deploy {
        /**
         * @var Deployer
         */
        private $deployer;
        /**
         * @var Config
         */
        private $config;

        /**
         * Deploy constructor.
         *
         * @param Config $config
         * @param Deployer $deployer
         */
        public function __construct(Config $config, Deployer $deployer) {
            $this->deployer = $deployer;
            $this->config   = $config;
        }

        public function index(HttpRequestEx $request) {
            /** @var ModelEx $config */
            if ($data = $this->config->get('aws')) {
                if ($deployment = $data['deployment'] ?? null) {
                    if ($deployment['secret'] === $request->getParameter('key')) {
                        $deployment['dry_run'] = $request->getParameter('dry_run');

                        return $this->deployer->deploy($deployment);
                    } else {
                        throw new AwsError('Url is invalid. Key mismatch.');
                    }
                }
            }

            throw new AwsError('Unable to deploy with current settings.');
        }
    }
}