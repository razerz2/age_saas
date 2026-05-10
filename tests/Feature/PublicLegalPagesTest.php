<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicLegalPagesTest extends TestCase
{
    public function test_public_legal_pages_return_successful_responses(): void
    {
        $this->get('/politica-de-privacidade')->assertOk();
        $this->get('/termos-de-servico')->assertOk();
        $this->get('/termos-de-uso')->assertOk();
    }
}
