<?php

namespace Test\Controller\Aws {

    use Auryn\Injector;
    use Minute\Deploy\Deployer;

    class DeployTest extends \PHPUnit_Framework_TestCase {

        public function testDeployer() {
            /** @var Deployer $deployer */
            $deployer = (new Injector())->make('Minute\Deploy\Deployer');
            $deployer->deploy('minutephp-best');

        }
    }
}