<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\CSVParserService;
use App\Models\Import;

class CSVParserServiceTest extends TestCase
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

    public function test_parse_reads_csv_and_splits_images_and_respects_start_row()
    {
        $relative = 'tests/csvparser/start_row_images.csv';
        $fullPath = $this->makeStoragePath($relative);

        $csv = [
            ['name_header', 'sku_header', 'price_header', 'category_header', 'description_header', 'images_header'],
            ['Product One', 'P1', '10.00', 'Cat A', 'Desc one', 'http://img1.jpg, http://img2.jpg'],
            ['Product Two', 'P2', '20.00', 'Cat B', 'Desc two', 'http://img3.jpg;http://img4.jpg']
        ];

        $fh = fopen($fullPath, 'w');
        foreach ($csv as $row) {
            fputcsv($fh, $row);
        }
        fclose($fh);

        $import = new Import();
        $import->file_name = 'Import.csv';
        $import->file_extension = 'csv';
        $import->hash_content = '1111';
        $import->file_path = $relative;

        $service = new CSVParserService($import);
        $service->setupSettings(3, 0, 1, 2, 3, 4, 5);

        $products = $service->parse();

        // Only the third row should be parsed due to start_row = 3
        $this->assertCount(1, $products);

        $expected = [
            'name' => 'Product Two',
            'sku' => 'P2',
            'price' => '20.00',
            'category' => 'Cat B',
            'description' => 'Desc two',
            'images' => ['http://img3.jpg', 'http://img4.jpg'],
        ];

        $this->assertSame($expected, $products[0]);

        // cleanup
        @unlink($fullPath);
    }
}
