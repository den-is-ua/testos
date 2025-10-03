<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Import;
use App\Services\CSVAISetupperSettingsService;
use App\Services\CSVParserService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CSVAISetupperSettingsServiceTest extends TestCase
{
    /**
     * @test
     */
    public function setup_settings_calls_gemini_with_expected_parameters()
    {
        $path = Str::random();

        $csv = [
            ['name_header', 'sku_header', 'price_header', 'category_header', 'description_header', 'images_header'],
            ['Product One', 'P1', '10.00', 'Cat A', 'Desc one', 'http://img1.jpg, http://img2.jpg'],
            ['Product Two', 'P2', '20.00', 'Cat B', 'Desc two', 'http://img3.jpg;http://img4.jpg'],
        ];

        $content = (function (array $rows, string $delimiter = ',', string $enclosure = '"', string $escape = '\\'): string {
            $h = fopen('php://temp', 'r+');
            foreach ($rows as $row) {
                fputcsv($h, $row, $delimiter, $enclosure, $escape);
            }
            rewind($h);
            $out = stream_get_contents($h);
            fclose($h);

            return $out === false ? '' : $out;
        })($csv);

        Storage::put($path, $content);

        $import = new Import;
        $import->file_path = $path;
        $import->file_name = 'Import.csv';
        $import->file_extension = 'csv';
        $import->hash_content = '1111';

        $ai = new CSVAISetupperSettingsService;
        $ai->setupSettings($import);

        $this->assertEquals([
            CSVParserService::START_ROW_SETTING_NAME => 2,
            CSVParserService::NAME_COLUMN_POSITION_SETTING_NAME => 1,
            CSVParserService::SKU_COLUMN_POSITION_SETTING_NAME => 2,
            CSVParserService::PRICE_COLUMN_POSITION_SETTING_NAME => 3,
            CSVParserService::CATEGORY_COLUMN_POSITION_SETTIG_NAME => 4,
            CSVParserService::DESCRIPTION_COLUMN_POSITION_SETTING_NAME => 5,
            CSVParserService::IMAGES_COLUMN_POSITION_SETTING_NAME => 6,
            CSVParserService::SEPARATOR_SETTING_NAME => ',',
            CSVParserService::ENDCLOUSURE_SETTING_NAME => '"',
            CSVParserService::ESCAPE_SETTING_NAME => '\\',
        ], $import->settings);

        Storage::delete($path);
    }
}
