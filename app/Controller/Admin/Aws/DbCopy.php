<?php
/**
 * Created by: MinutePHP framework
 */
namespace App\Controller\Admin\Aws {

    use Minute\Config\Config;
    use Minute\Crypto\JwtEx;
    use Minute\Db\DbFormatter;
    use Minute\Deployer\Deployer;

    class DbCopy {
        /**
         * @var Config
         */
        private $config;
        /**
         * @var JwtEx
         */
        private $jwt;
        /**
         * @var DbFormatter
         */
        private $dbFormatter;

        /**
         * DbCopy constructor.
         *
         * @param Config $config
         * @param DbFormatter $dbFormatter
         * @param JwtEx $jwt
         */
        public function __construct(Config $config, DbFormatter $dbFormatter, JwtEx $jwt) {
            $this->config      = $config;
            $this->dbFormatter = $dbFormatter;
            $this->jwt         = $jwt;
        }

        public function index($tweak) {
            $rds = $this->config->get(Deployer::RDS_KEY);

            if ($remote = (array) $this->jwt->decode($rds)) {
                $this->dbFormatter->format($remote, $tweak);
            }

            return 'OK';
        }
    }
}

