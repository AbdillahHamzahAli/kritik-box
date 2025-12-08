<?php

namespace App\Models;

class BusinessModel
{
    public function __construct(
        public ?int $id = null,
        public int $user_id,
        public string $name,
        public string $location,
        public string $address = "",
        public string $qrcode = "",
    ) {}
}
