<?php

namespace Test\Deploy {

    use Auryn\Injector;
    use Minute\Deploy\Deployer;

    class DeployerTest extends \PHPUnit_Framework_TestCase {

        public function testDeploy() {
            /** @var Deployer $deployer */
            $deployer = (new Injector())->make('Minute\Deploy\Deployer');
            $deployer->deploy('Deployer', 'minutephp-2');
        }
    }
}