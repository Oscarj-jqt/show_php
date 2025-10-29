<?php

namespace App\Model;

class User
{
    public function __construct(
        public int $id,
        public string $username,
        public string $name,
        public string $firstname,
        public string $lastname,
        public string $email,
        public string $password,
        public string $role,
    ) {}
}
