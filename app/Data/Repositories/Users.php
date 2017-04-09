<?php

namespace App\Data\Repositories;

use Hash;
use Ramsey\Uuid\Uuid;
use App\Data\Entities\User;
use App\Events\UserRegistered;

class Users
{
    public function findOrCreateUser($first_name, $last_name, $username, $email)
    {
        if ($user = $this->findByEmail($email)) {
            return $user;
        }

        $user = User::create([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make(str_random(32)),
            'activation_key' => (string) Uuid::uuid4(),
        ]);

        event(new UserRegistered($user));

        return $user;
    }

    private function findByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function sendActivationEmail($user)
    {
        // $user->notify(new ActivateAccount($user->activationUrl));
    }

    public function getNewModel()
    {
        return new User;
    }
}
