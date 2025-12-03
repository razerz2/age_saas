<?php

namespace App\Services\DTO;

class RenderedMessageDTO
{
    public function __construct(
        public string $subject,
        public string $body
    ) {
    }
}

