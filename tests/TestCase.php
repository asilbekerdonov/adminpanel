<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Тесты не требуют собранных Vite-ассетов (npm run build).
        $this->withoutVite();
    }
}
