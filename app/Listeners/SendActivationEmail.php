<?php

namespace App\Listeners;

use App\Data\Repositories\Users;
use App\Events\UserRegistered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendActivationEmail
{
    /**
     * @var Users
     */
    private $usersRepository;

    /**
     * Create the event listener.
     *
     * @param Users $usersRepository
     */
    public function __construct(Users $usersRepository)
    {
        $this->usersRepository = $usersRepository;
    }

    /**
     * Handle the event.
     *
     * @param  UserRegistered  $event
     * @return void
     */
    public function handle(UserRegistered $event)
    {
        $this->usersRepository->sendActivationEmail($event->user);
    }
}
