<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CSVParserService;
use App\Models\Import;

class CSVParserServiceTest extends TestCase
{
    protected function makeStoragePath(string $relative): string
    {
        $fullDir = storage_path('app/' . dirname($relative));
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0777, true);
        }
        return storage_path('app/' . ltrim($relative, '/'));
    }

    public function test_parse_returns_empty_when_no_file_path()
    {
        $import = new Import();
        $import->file_path = null;

        $service = new CSVParserService($import);

        $this->assertSame([], $service->parse());
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
        $import->file_path = $relative;
        $import->settings = [
            CSVParserService::START_ROW_SETTING_NAME => 3, // skip first two rows, parse only 3rd (rowIndex 3)
            CSVParserService::NAME_COLUMN_POSITION_SETTING_NAME => 0,
            CSVParserService::SKU_COLUMN_POSITION_SETTING_NAME => 1,
            CSVParserService::PRICE_COLUMN_POSITION_SETTING_NAME => 2,
            CSVParserService::CATEGORY_COLUMN_POSITION_SETTIG_NAME => 3,
            CSVParserService::DESCRIPTION_COLUMN_POSITION_SETTING_NAME => 4,
            CSVParserService::IMAGES_COLUMN_POSITION_SETTING_NAME => 5,
        ];

        $service = new CSVParserService($import);

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

    public function test_parse_handles_missing_columns_and_empty_images()
    {
        $relative = 'tests/csvparser/missing_columns.csv';
        $fullPath = $this->makeStoragePath($relative);

        $csv = [
            ['OnlyName', '', '5.00', '', '', ''] // sku empty, category/description empty, images empty
        ];

        $fh = fopen($fullPath, 'w');
        foreach ($csv as $row) {
            fputcsv($fh, $row);
        }
        fclose($fh);

        $import = new Import();
        $import->file_path = $relative;
        // provide only some column positions (others are intentionally missing / null)
        $import->settings = [
            CSVParserService::NAME_COLUMN_POSITION_SETTING_NAME => 0,
            CSVParserService::SKU_COLUMN_POSITION_SETTING_NAME => 1,
            CSVParserService::PRICE_COLUMN_POSITION_SETTING_NAME => 2,
            CSVParserService::IMAGES_COLUMN_POSITION_SETTING_NAME => 5,
        ];

        $service = new CSVParserService($import);

        $products = $service->parse();

        $this->assertCount(1, $products);

        $expected = [
            'name' => 'OnlyName',
            'sku' => '', // present in CSV but empty
            'price' => '5.00',
            'category' => null, // not provided in settings
            'description' => null, // not provided in settings
            'images' => [], // empty images cell -> empty array
        ];

        $this->assertSame($expected, $products[0]);

        // cleanup
        @unlink($fullPath);
    }
}
