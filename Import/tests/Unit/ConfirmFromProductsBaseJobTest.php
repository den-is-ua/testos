<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Import;
use App\Jobs\ConfirmFromProductsBaseJob;

class ConfirmFromProductsBaseJobTest extends TestCase
{
    public function test_sets_completed_at_when_confirmed_equals_total_after_increment(): void
    {
        // create import with total_iterations = 1 and confirmed_iterations = 0
        $import = new Import();
        $import->file_name = 'test.csv';
        $import->file_path = 'test.csv';
        $import->file_extension = 'csv';
        $import->hash_content = 'hash123';
        $import->total_iterations = 1;
        $import->confirmed_iterations = 0;
        $import->save();

        // run job
        $job = new ConfirmFromProductsBaseJob($import->id);
        $job->handle();

        $import->refresh();

        // confirmed_iterations must be incremented and equal total_iterations
        $this->assertEquals(1, $import->confirmed_iterations);
        $this->assertEquals($import->total_iterations, $import->confirmed_iterations);

        // completed_at must be set
        $this->assertNotNull($import->completed_at);
    }

    public function test_does_not_set_completed_at_when_confirmed_less_than_total_after_increment(): void
    {
        // create import with total_iterations = 3 and confirmed_iterations = 1
        $import = new Import();
        $import->file_name = 'test2.csv';
        $import->file_path = 'test2.csv';
        $import->file_extension = 'csv';
        $import->hash_content = 'hash456';
        $import->total_iterations = 3;
        $import->confirmed_iterations = 1;
        $import->save();

        // run job
        $job = new ConfirmFromProductsBaseJob($import->id);
        $job->handle();

        $import->refresh();

        // confirmed_iterations must be incremented but still less than total_iterations
        $this->assertEquals(2, $import->confirmed_iterations);
        $this->assertTrue($import->confirmed_iterations < $import->total_iterations);

        // completed_at must remain null
        $this->assertNull($import->completed_at);
    }
}
