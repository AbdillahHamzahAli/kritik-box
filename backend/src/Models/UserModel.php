<?php

namespace App\Models;

class UserModel
{
    public function __construct(
        public ?int $id = null,
        public string $name,
        public string $username,
        public string $email,
        public string $password,
        public ?string $membership_expires_at = null,
        public ?string $created_at = null,
    ) {}
}
