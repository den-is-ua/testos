<?php

namespace Tests\Unit;

use App\Http\Resources\ImportResource;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class ImportResourceTest extends TestCase
{
    /**
     * @test
     */
    public function test_progress_is_zero_in_default()
    {
        $import = (object) [
            'id' => 2,
            'file_name' => 'file.csv',
            'confirmed_iterations' => 0,
            'total_iterations' => 0,
            'completed_at' => null,
        ];

        $resource = new ImportResource($import);
        $array = $resource->toArray(Request::create('/'));

        $this->assertSame(0, $array['progress']);
    }

    /**
     * @test
     */
    public function test_progress_1_percent()
    {
        $import = (object) [
            'id' => 3,
            'file_name' => 'file.csv',
            'confirmed_iterations' => 1,
            'total_iterations' => 100,
            'completed_at' => null,
        ];

        $resource = new ImportResource($import);
        $array = $resource->toArray(Request::create('/'));

        $this->assertSame(1, $array['progress']);
    }

    /**
     * @test
     */
    public function test_progress_50_percent()
    {
        $import = (object) [
            'id' => 3,
            'file_name' => 'file.csv',
            'confirmed_iterations' => 50,
            'total_iterations' => 100,
            'completed_at' => null,
        ];

        $resource = new ImportResource($import);
        $array = $resource->toArray(Request::create('/'));

        $this->assertSame(50, $array['progress']);
    }

    /**
     * @test
     */
    public function test_progress_completed()
    {
        $import = (object) [
            'id' => 4,
            'file_name' => 'done.csv',
            'confirmed_iterations' => 1,
            'total_iterations' => 1,
            'completed_at' => '2025-01-01 00:00:00',
        ];

        $resource = new ImportResource($import);
        $array = $resource->toArray(Request::create('/'));

        $this->assertSame(100, $array['progress']);
        $this->assertTrue($array['completed']);
    }
}
