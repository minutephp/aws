<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 7/8/2016
 * Time: 12:14 PM
 */
namespace Minute\Mail {

    use Aws\Ses\Exception\SesException;
    use Minute\Aws\Client;
    use Minute\Debug\Debugger;
    use Minute\Error\AwsError;
    use Minute\Event\Dispatcher;
    use Minute\Event\RawMailEvent;
    use Minute\Event\UserErrorEvent;
    use Minute\File\TmpDir;
    use Minute\Log\LoggerEx;

    class SesMailer {
        /**
         * @var Client
         */
        private $client;
        /**
         * @var LoggerEx
         */
        private $loggerEx;
        /**
         * @var Dispatcher
         */
        private $dispatcher;
        /**
         * @var Debugger
         */
        private $debugger;
        /**
         * @var TmpDir
         */
        private $tmpDir;

        /**
         * SesMailer constructor.
         *
         * @param Client $client
         * @param LoggerEx $loggerEx
         * @param Dispatcher $dispatcher
         * @param Debugger $debugger
         * @param TmpDir $tmpDir
         */
        public function __construct(Client $client, LoggerEx $loggerEx, Dispatcher $dispatcher, Debugger $debugger, TmpDir $tmpDir) {
            $this->client     = $client;
            $this->loggerEx   = $loggerEx;
            $this->dispatcher = $dispatcher;
            $this->debugger   = $debugger;
            $this->tmpDir     = $tmpDir;
        }

        public function sendMail(RawMailEvent $event) {
            $message   = $event->getMessage();
            $sesClient = $this->client->getSesClient();

            try {
                if ($this->debugger->enabled()) {
                    $dir = sprintf('%s/%s', $this->tmpDir->getTempDir('mails'), date('d-M-Y'));
                    $fn  = sprintf('%s/%s-%s.txt', $dir, microtime(), array_keys($message->getTo())[0]);

                    @mkdir($dir, 0777, true);
                    @file_put_contents($fn, $message->toString() . "\n\nMessage body:\n" . $message->getBody());

                    $event->setHandled(true);
                } else {
                    if ($sesClient->sendRawEmail(['RawMessage' => ['Data' => ($message->toString())]])) { //may need Source and Destinations
                        $event->setHandled(true);
                    }
                }
            } catch (SesException $e) {
                if (preg_match('/blacklist/i', $e->getMessage())) {
                    $this->dispatcher->fire(UserErrorEvent::USER_MAIL_BOUNCE, new UserErrorEvent(['email' => $message->getTo()]));
                } else {
                    throw new AwsError($e->getMessage());
                }
            }
        }
    }
}