<?php

namespace App\Models;
use DateTimeImmutable;

class FeedbackModel
{
    public function __construct(
        public ?int $id = null,
        public int $business_id,
        public float $rating,
        public string $text,
        public ?DateTimeImmutable $created_at = null,
    ) {}
}
