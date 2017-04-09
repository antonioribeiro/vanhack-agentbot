<?php

namespace App\Data\Repositories;

class Clients
{
    public function findByEmail($email)
    {
        return ends_with($email, '@gmail.com');
    }
}
