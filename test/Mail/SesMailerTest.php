<?php

namespace Test\Mail {

    use Auryn\Injector;
    use Minute\Event\RawMailEvent;
    use Minute\Mail\SesMailer;
    use Mockery as m;
    use Mockery\Adapter\Phpunit\MockeryTestCase;

    class SesMailerTest extends MockeryTestCase {

        public function testSendMail() {
            $message = new \Swift_Message('subject', 'html');
            $message->setFrom(['from@localhost' => 'From From']);
            $message->setTo(['to@localhost' => 'To To']);
            $eventMock = new RawMailEvent($message);

            $sesMock = m::mock('Aws\Ses\SesClient');
            $sesMock->shouldReceive('sendRawEmail')->withArgs(function ($data) {
                $msg = base64_decode($data['RawMessage']['Data']);
                $this->assertContains('From From <from@localhost>', $msg, 'From is present');
                $this->assertContains('To To <to@localhost>', $msg, 'From is present');

                return true;
            })->andReturn(true);

            $clientMock = m::mock('Minute\Aws\Client', ['getSesClient' => $sesMock]);

            /** @var SesMailer $sesMailer */
            $sesMailer = (new Injector())->make('Minute\Mail\SesMailer', [':client' => $clientMock]);
            $sesMailer->sendMail($eventMock);

            $this->assertTrue($eventMock->isHandled(), 'Message handled');
        }
    }
}