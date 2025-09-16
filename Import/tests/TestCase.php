<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    
    protected function makeStoragePath(string $relative): string
    {
        $fullDir = storage_path('app/' . dirname($relative));
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0777, true);
        }
        return storage_path('app/' . ltrim($relative, '/'));
    }
}
