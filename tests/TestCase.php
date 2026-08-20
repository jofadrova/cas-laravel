<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $config = $app->make('config');

        if ($config->get('database.default') !== 'sqlite'
            || $config->get('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException(
                'Pruebas bloqueadas: la conexión debe ser SQLite en memoria. '
                .'Nunca ejecute RefreshDatabase contra MySQL.'
            );
        }

        return $app;
    }
}
