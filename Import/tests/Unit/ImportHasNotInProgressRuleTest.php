<?php

namespace Tests\Unit;

use App\Models\Import;
use App\Rules\ImportHasNotInProgress;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ImportHasNotInProgressRuleTest extends TestCase
{
    /**
     * @test
     */
    public function allows_upload_when_allow_upload_duplicated_file_is_true(): void
    {
        // prepare CSV content
        $csv = "name,sku\nProduct A,SKU1\n";
        $path = Str::random() . '.csv';
        Storage::put($path, $csv);

        $file = new UploadedFile(
            Storage::path($path),
            'test.csv',
            'text/csv',
            null
        );

        // create an existing import record that is in progress (completed_at = null)
        $hash = hash_file('sha256', Storage::path($path));
        Import::create([
            'file_extension' => 'csv',
            'file_name' => 'test.csv',
            'file_path' => $path,
            'hash_content' => $hash,
            'settings' => [],
        ]);

        // enable the config flag that allows duplicated uploads
        config(['app.allow_upload_duplicated_file' => true]);

        $rule = new ImportHasNotInProgress;

        $this->assertTrue($rule->passes('file', $file));

        // cleanup
        Storage::delete($path);
    }
}
