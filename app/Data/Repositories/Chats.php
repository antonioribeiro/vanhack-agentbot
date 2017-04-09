<?php

namespace App\Data\Repositories;

use Carbon\Carbon;
use App\Data\Entities\Chat;
use App\Data\Entities\ChatType;
use App\Data\Entities\ChatMessage;
use App\Data\Repositories\Users as UsersRepository;

class Chats
{
    /**
     * @var \App\Data\Repositories\Users
     */
    private $userRepository;

    public function __construct(UsersRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    private function createChatId($message)
    {
        return $message->getUser() . '||' . $message->getChannel();
    }

    private function findOrCreateChat($user, $currentBot)
    {
        $message = $currentBot->getMessage();

        return Chat::firstOrCreate(
            [
                'chat_id' => $this->createChatId($message),
            ],

            [
                'user' => $message->getUser(),

                'channel' => $message->getChannel(),

                'user_id' => $user->id,

                'chat_type_id' => $this->findChatTypeByName($currentBot->getDriver()->getName())->id,
            ]
        );
    }

    private function findChatTypeByName($getName)
    {
        return ChatType::firstOrNew([
                                        'name' => $getName,
                                        'slug' => $this->makeSlug($getName),
                                    ]);
    }

    private function makeSlug($name)
    {
        return str_slug(str_replace(' ', '', $name));
    }

    public function registerUserAndChat($firstName, $lastName, $username, $email, $currentBot)
    {
        $user = $this->userRepository->findOrCreateUser($firstName, $lastName, $username, $email);

        return $this->findOrCreateChat($user, $currentBot);
    }

    public function findChatByMessage($message)
    {
        return Chat::where('chat_id', $this->createChatId($message))->first();
    }

    public function activateChat(Chat $chat)
    {
        $chat->activated_at = Carbon::now();

        $chat->save();
    }

    public function makeActivationCodeForChat($chat)
    {
        $chat->activation_code = rand(100000, 999999);

        $chat->save();

        return $chat;
    }

    public function createMessage($type, $chat, $message, $question_id = null)
    {
        return ChatMessage::create([
            'text' => $message,
            'chat_id' => $chat->id,
            'message_id' => $question_id,
            'type' => $type,
        ]);
    }
}
