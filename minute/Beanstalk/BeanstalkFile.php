<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 8/28/2016
 * Time: 4:29 AM
 */
namespace Minute\Beanstalk {

    use Minute\Event\DockerEvent;

    class BeanstalkFile {
        public function create(DockerEvent $event) {
            $files = glob(__DIR__ . '/data/*');

            foreach ($files as $file) {
                $event->addContent(sprintf('.ebextensions/%s', basename($file)), file_get_contents($file));
            }
        }
    }
}