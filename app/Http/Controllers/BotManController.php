<?php

namespace App\Http\Controllers;

use App\Data\Repositories\Chats;
use Mpociot\BotMan\BotMan;
use App\Conversations\AuthenticateUser;
use App\Conversations\ExampleConversation;

class BotManController extends Controller
{
    private function getChat($bot)
    {
        return app(Chats::class)->findChatByMessage($bot->getMessage());
    }

    /**
	 * Place your BotMan logic here.
	 */
    public function handle()
    {
    	$botman = app('botman');

        $botman->verifyServices(env('TOKEN_VERIFY'));

        info(request()->all());

        $botman->listen();
    }

    private function parseReply($reply)
    {
        return explode('||', $reply);
    }

    private function reply($bot)
    {
        $extras = $bot->getMessage()->getExtras();

        $apiReply = collect($this->parseReply($extras['apiReply']));

        info($apiReply);

        $question = $this->storeQuestion($bot, $bot->getMessage()->getMessage());

        $apiReply->each(function($reply) use ($bot, $question) {
            $this->storeAnswer($bot, $question, $reply);

            $bot->reply($reply, [
                'parse_mode' => 'Markdown'
            ]);
        });
    }

    /**
     * Loaded through routes/botman.php
     * @param  BotMan $bot
     */
    public function startConversation(BotMan $bot)
    {
        $bot->startConversation(new ExampleConversation());
    }

    private function storeAnswer($bot, $question, $message)
    {
        return app(Chats::class)
                ->createMessage(2, $this->getChat($bot), $message, $question->id);
    }

    private function storeQuestion($bot, $message)
    {
        return app(Chats::class)
                ->createMessage(1, $this->getChat($bot), $message);
    }

    public function talk(BotMan $bot)
    {
        if ($this->userIsAuthenticated($bot)) {
            return $this->reply($bot);
        }

        $bot->startConversation(new AuthenticateUser($bot));
    }

    private function userIsAuthenticated($bot)
    {
        if ($chat = $this->getChat($bot)) {
            return $chat->is_activated;
        }

        return false;
    }
}
