<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AISetupperSettingsContract;
use App\Models\Import;
use Gemini\Data\UploadedFile;
use Gemini\Laravel\Facades\Gemini;



class CSVAISetupperSettingsService implements AISetupperSettingsContract
{
    public function setupSettings(Import $import)
    {
        $startRow = CSVParserService::START_ROW_SETTING_NAME;
        $nameColumnPosition = CSVParserService::NAME_COLUMN_POSITION_SETTING_NAME;
        $skuColumnPosition = CSVParserService::SKU_COLUMN_POSITION_SETTING_NAME;
        $priceColumnPosition = CSVParserService::PRICE_COLUMN_POSITION_SETTING_NAME;
        $categoryColumnPosition = CSVParserService::CATEGORY_COLUMN_POSITION_SETTIG_NAME;
        $descriptionColumnPosition = CSVParserService::DESCRIPTION_COLUMN_POSITION_SETTING_NAME;
        $imagesColumnPosition = CSVParserService::IMAGES_COLUMN_POSITION_SETTING_NAME;

        $prompt = <<<EOL
            Generate php array map for settings for parse csv file.
            If sku dont exists, use product id
            Need to get this array shape: 
            [
                $startRow                       => int,
                $nameColumnPosition             => int,        
                $skuColumnPosition              => int,
                $priceColumnPosition            => int,
                $categoryColumnPosition         => ?int,
                $descriptionColumnPosition      => ?int,
                $imagesColumnPosition           => ?int
            ]
        EOL>>

        $result = Gemini::generativeModel(model: 'gemini-2.0-flash')
            ->generateContent([
                'What is this video?',
                new UploadedFile(
                    fileUri: $import->file_path,
                    mimeType: $import->file_extension
                )
            ]);
    }
}
