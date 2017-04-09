<?php

namespace App\Conversations;

use Mpociot\BotMan\BotMan;
use Mpociot\BotMan\Answer;
use Mpociot\BotMan\Button;
use Mpociot\BotMan\Question;
use App\Notifications\ActivateChat;
use App\Data\Repositories\Chats as ChatsRepository;
use App\Data\Repositories\Users as UsersRepository;
use App\Data\Repositories\Clients as ClientsRepository;

class AuthenticateUser extends Conversation
{
    const GIVE_ME_THE_CODE = "{%firstName}, could you, please, check your inbox and give me the code I just sent you?";

    const EMAIL_QUESTION   = '{%firstName}, could you, please, provide the e-mail address of your account with us?';

    const ILL_TRY_AGAIN = "Nice, then try again, I'll wait";

    const DID_NOT_UNDESTAND = "I will not pretend I understood what did just happen";

    const SORRY_COULDNT_HELP = "Oh, I'm so sorry I could not help you, {%firstName} {%lastName}";

    const NICE_TO_MEET = "Nice to meet you, {%firstName}";

    const THANKS_FOR_INFORMATION = 'Great, thanks a lot for all the information.';

    const HOW_CAN_I_HELP = 'What can I help you with?';

    const ANSWER_FORGET = 'forget';

    const ANSWER_SEND = 'send';

    const ANSWER_WAIT = 'wait';

    const NOT_THE_CODE = 'Nope, this does not look like the code I sent you';

    const WELCOME_BACK = "Welcome back, {%firstName}!";

    const CUSTOMER_NOT_FOUND = "Sorry, but this e-mail does not belongs to a registered account, all our clients have @gmail.com addresses.";

    const EMAILING_CODE = "Please wait, I'm emailing you some code...";

    const DONE = "Done.";

    /**
     * @var bool
     */
    protected $aborted = false;

    protected $firstName;

    protected $email;

    protected $currentBot;

    protected $username;

    protected $lastName;

    private $activation_code;

    private $chat;

    public function __construct(BotMan $currentBot)
    {
        parent::__construct($currentBot);
    }

    private function activateChat()
    {
        $this->getChatsRepository()->activateChat($this->getChat());
    }

    public function askForFirstName()
    {
        if ($this->hasFirstName()) {
            return $this->askForEmail();
        }

        $this->ask('Hello! What is your name?', function(Answer $answer) {
            $this->firstName = $answer->getText();

            $this->say(self::NICE_TO_MEET);

            $this->askForEmail();
        });
    }

    public function askForEmail($question = null)
    {
        if ($this->emailIsValid()) {
            return $this->askForActivationCode();
        }

        $this->ask($question ?: static::EMAIL_QUESTION, function(Answer $answer) {
            if(! $this->emailIsValid()) {
                $this->email = $answer->getText();

                if (! ($this->emailIsValid() && $this->isCustomer() && $this->register())) {
                    return $this->askForEmail();
                }
            }

            $this->askForActivationCode();
        });
    }

    private function chatIsActivated()
    {
        return ! is_null($this->getChat()) && $this->chat->is_activated;
    }

    protected function emailIsValid()
    {
        return filter_var($this->email, FILTER_VALIDATE_EMAIL);
    }

    private function getChat()
    {
        return $this->loadChat();
    }

    private function getChatsRepository()
    {
        return app(ChatsRepository::class);
    }

    private function getClientsRepository()
    {
        return app(ClientsRepository::class);
    }

    private function getMessage()
    {
        return $this->currentBot->getMessage();
    }

    private function getUsersRepository()
    {
        return app(UsersRepository::class);
    }

    private function getNotifiable()
    {
        $user = $this->getUsersRepository()->getNewModel();

        $user->email = $this->email;

        $user->activation_code = $this->makeActivationCode();

        return $user;
    }

    /**
     * @return $this
     */
    private function getWrongActivationCodeQuestion()
    {
        $question = Question::create('What do you want me to do?')
                            ->fallback('Sorry I just had a problem')
                            ->callbackId('ask_reason')
                            ->addButtons([
                                             Button::create('Send me a new code')->value('send'),
                                             Button::create('Wait, I will try again')->value('wait'),
                                             Button::create('Nah, forget it')->value('forget'),
                                         ])
        ;

        return $question;
    }

    /**
     * @return $this
     */
    private function greetsTheUser()
    {
        return $this->say(self::WELCOME_BACK);
    }

    private function askForActivationCode($sendCode = true, $message = null)
    {
        if ($this->chatIsActivated()) {
            return true;
        }

        if ($sendCode) {
            $this->sendActivationCode();
        }

        return $this->ask($message ?: static::GIVE_ME_THE_CODE, function(Answer $answer) {
            if (trim($answer->getText()) !== $this->getChat()->activation_code) {
                return $this->wrongActivationCode();
            }

            $this->activateChat();

            $this->say(self::THANKS_FOR_INFORMATION);

            $this->say(self::HOW_CAN_I_HELP);

            return true;
        });
    }

    private function hasFirstName()
    {
        return $this->firstName;
    }

    private function initialize()
    {
        if ($user = $this->currentBot->getUser()) {
            $this->username = $user->getUsername();

            $this->lastName = $user->getLastName();

            if ($this->firstName = $user->getFirstName()) {
                return true;
            }
        }

        $this->loadChat();
    }

    protected function isCustomer()
    {
        if ($this->getClientsRepository()->findByEmail($this->email)) {
            return true;
        }

        $this->say(self::CUSTOMER_NOT_FOUND);
    }

    private function knowsUser()
    {
        if ($chat = $this->getChatsRepository()->findChatByMessage($this->getMessage())) {
            return $this->chatIsActivated();
        }

        return false;
    }

    private function loadChat()
    {
        $this->chat = $this->getChatsRepository()->findChatByMessage($this->getMessage());

        return $this->chat;
    }

    /**
     * @return mixed
     */
    private function makeActivationCode()
    {
        $this->chat = $this->getChatsRepository()->makeActivationCodeForChat($this->chat);

        return $this->getChat()->activation_code;
    }

    protected function register()
    {
        return $this->chat = $this
                                ->getChatsRepository()
                                ->registerUserAndChat(
                                    $this->firstName,
                                    $this->lastName,
                                    $this->username,
                                    $this->email,
                                    $this->currentBot
                                );
    }

    private function sendActivationCode()
    {
        $this->say(self::EMAILING_CODE);

        $user = $this->getNotifiable();

        $user->notify(new ActivateChat());

        $this->say(self::DONE);
    }

    /**
     *
     */
    private function wrongActivationCode()
    {
        $this->say(self::NOT_THE_CODE);

        $this->ask($this->getWrongActivationCodeQuestion(), function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() !== self::ANSWER_FORGET) {
                    return $this->askForActivationCode(
                        $answer->getValue() == self::ANSWER_SEND,
                        $answer->getValue() == self::ANSWER_WAIT
                            ? self::ILL_TRY_AGAIN
                            : "Ok, just to be sure you are getting it right, the code was {$this->getChat()->activation_code}, but I'm sending a new one..."
                    );
                }
            } else {
                return $this->say(self::DID_NOT_UNDESTAND);
            }

            $this->say(self::SORRY_COULDNT_HELP);

            return false;
        });
    }

    public function run()
    {
        $this->initialize();

        if ($this->knowsUser()) {
            return $this->greetsTheUser();
        }

        $this->askForFirstName();
    }
}
