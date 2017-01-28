<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 11/5/2016
 * Time: 8:11 PM
 */
namespace App\Controller\Aws {

    use App\Model\User;
    use Minute\Event\Dispatcher;
    use Minute\Event\UserMailEvent;
    use Minute\Log\LoggerEx;
    use GuzzleHttp\Client;
    use Illuminate\Database\Eloquent\Builder;
    use Minute\Model\CollectionEx;

    class SnsHandler {
        /**
         * @var Dispatcher
         */
        private $dispatcher;
        /**
         * @var LoggerEx
         */
        private $logger;

        /**
         * SnsHandler constructor.
         *
         * @param Dispatcher $dispatcher
         * @param LoggerEx $logger
         */
        public function __construct(Dispatcher $dispatcher, LoggerEx $logger) {
            $this->dispatcher = $dispatcher;
            $this->logger     = $logger;
        }

        public function bounce() {
            if ($email = $this->decodeSns()) {
                if ($user_ids = $this->findUsers($email)) {
                    foreach ($user_ids as $user_id) {
                        $this->dispatcher->fire(UserMailEvent::USER_MAIL_BOUNCED, new UserMailEvent($user_id, ['email' => $email]));
                    }
                }
            }

            exit('OK');
        }

        public function spam() {
            if ($email = $this->decodeSns()) {
                if ($user_ids = $this->findUsers($email)) {
                    foreach ($user_ids as $user_id) {
                        $this->dispatcher->fire(UserMailEvent::USER_MAIL_SPAM_CLICK, new UserMailEvent($user_id, ['email' => $email]));
                    }
                }
            }

            exit('OK');
        }

        protected function findUsers($email) {
            /** @var CollectionEx $users */
            $users = User::select('user_id')->where('contact_email', '=', $email)->orWhere(function (Builder $builder) use ($email) {
                $builder->where('contact_email', '=', null)->where('email', '=', $email);
            })->get();

            return $users->pluck('user_id')->toArray();
        }

        protected function decodeSns() {
            if ($body = @file_get_contents('php://input')) {
                if ($sns = json_decode($body)) {
                    //$this->logger->debug(var_export($body, true));

                    if ($sns->type === 'SubscriptionConfirmation') {
                        if ($client = new Client(['defaults' => ['timeout' => 3, 'connect_timeout' => 5]])) {
                            $client->get($sns->SubscribeURL)->getBody(); //tell sns we're listening
                        }
                    } elseif ($sns->type === 'Notification') {
                        if ($type = preg_match('/complaint/i', $sns->notificationType) ? 'complaint' : (preg_match('/bounce/i', $sns->notificationType) ? 'bounce' : null)) {
                            if ($email = $type === 'bounce' ? $sns->Bounce->bouncedRecipients->emailAddress : $sns->Complaint->complainedRecipients->emailAddress) {
                                return $email;
                            }
                        }
                    }
                }
            }

            return false;
        }
    }
}