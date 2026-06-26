<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // O driver de cache `array` persiste no processo entre testes; limpar
        // evita que dados em cache (ex.: taxas activas, stats) vazem de um
        // teste para o seguinte.
        Cache::flush();
    }
}
