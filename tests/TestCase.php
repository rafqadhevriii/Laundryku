<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // Dijalankan sebelum trait RefreshDatabase menyiapkan skema pengujian.
        if (! $app->environment('testing')
            || $app->make('config')->get('database.default') !== 'mysql'
            || $app->make('db')->connection()->getDatabaseName() !== 'laundryku_testing') {
            throw new \RuntimeException('Tes hanya boleh menggunakan MySQL laundryku_testing.');
        }

        return $app;
    }
}
