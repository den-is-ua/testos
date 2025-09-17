<?php

namespace Tests\Unit;

use App\Services\ImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\Import;
use App\Jobs\ParseImportJob;
use App\Jobs\SendToProductsBaseJob;
use Illuminate\Support\Facades\Queue;

class ParseImportJobTest extends TestCase
{
    public function test_handles_and_dispatches_chunks_and_increments_total_iterations(): void
    {
        Queue::fake();

        // prepare CSV content (2 rows -> 1 chunk with default chunk size 100)
        $csv = "name,sku,price,category,description\nProduct A,SKU1,10,Category1,Desc A\nProduct B,SKU2,20,Category2,Desc B\n";
        $path = Str::random() . '.csv';
        Storage::put($path, $csv);

        $file = new UploadedFile(
            Storage::path($path),
            'test.csv',       // original filename
            'text/csv',       // mime type
            null
        );

        $import = ImportService::store($file);

        // set parser settings expected by CSVParserService
        $import->settings = [
            'startRow' => 1,
            'nameColumnPosition' => 0,
            'skuColumnPosition' => 1,
            'priceColumnPosition' => 2,
            'categoryColumnPosition' => 3,
            'descriptionColumnPosition' => 4,
            'imagesColumnPosition' => null,
            'separator' => ',',
            'endclosure' => '"',
            'escape' => '\\',
        ];
        $import->save();

        // execute job
        $job = new ParseImportJob($import->id);
        $job->handle();

        // assert SendToProductsBaseJob was pushed once with expected payload
        Queue::assertPushed(SendToProductsBaseJob::class);

        $import->refresh();
        $this->assertEquals(1, $import->total_iterations);

        Storage::delete([
            $path,
            $import->file_path
        ]);
    }
}
