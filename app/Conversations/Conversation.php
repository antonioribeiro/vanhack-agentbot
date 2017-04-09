<?php

namespace App\Conversations;

use Mpociot\BotMan\BotMan;
use Mpociot\BotMan\Conversation as BotManConversation;

abstract class Conversation extends BotManConversation
{
    protected $currentBot;

    public function __construct(BotMan $currentBot)
    {
        $this->currentBot = $currentBot;
    }

    protected function translate($string)
    {
        if (is_string($string)) {
            preg_match_all('/\{\%(\w+)\}/', $string, $matches);

            foreach ($matches[1] as $key => $value) {
                $string = str_replace($matches[0][$key], $this->{$value}, $string);
            }
        }

        return $string;
    }

    /**
     * @param string|Question $message
     * @param array $additionalParameters
     * @return $this
     */
    public function say($message, $additionalParameters = [])
    {
        return parent::say($this->translate($message), $additionalParameters);
    }

    /**
     * @param string|Question $question
     * @param array|Closure $next
     * @param array $additionalParameters
     * @return $this
     */
    public function ask($question, $next, $additionalParameters = [])
    {
        return parent::ask($this->translate($question), $next, $additionalParameters);
    }
}
