<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ParserContract;
use App\Models\Import;



class CSVParserService implements ParserContract
{
    const START_ROW_SETTING_NAME = 'start_row';
    const NAME_COLUMN_POSITION_SETTING_NAME = 'name_column_position';
    const SKU_COLUMN_POSITION_SETTING_NAME = 'sku_column_position';
    const PRICE_COLUMN_POSITION_SETTING_NAME = 'price_column_position';
    const CATEGORY_COLUMN_POSITION_SETTIG_NAME = 'category_column_position';
    const DESCRIPTION_COLUMN_POSITION_SETTING_NAME = 'description_column_position';
    const IMAGES_COLUMN_POSITION_SETTING_NAME = 'images_column_position';


    public function __construct(private Import $import)
    {
        
    }

    public function setupSettings(
        int $startRow, 
        int $nameColumnPosition, 
        int $skuColumnPosition,
        int $priceColumnPosition,
        ?int $categoryColumnPosition,
        ?int $descriptionColumnPosition,
        ?int $imageColumnPosition    
    ) {
        $this->import->settings = [
            self::START_ROW_SETTING_NAME                    => $startRow,
            self::NAME_COLUMN_POSITION_SETTING_NAME         => $nameColumnPosition,
            self::SKU_COLUMN_POSITION_SETTING_NAME          => $skuColumnPosition,
            self::PRICE_COLUMN_POSITION_SETTING_NAME        => $priceColumnPosition,
            self::CATEGORY_COLUMN_POSITION_SETTIG_NAME      => $categoryColumnPosition,
            self::DESCRIPTION_COLUMN_POSITION_SETTING_NAME  => $descriptionColumnPosition,
            self::IMAGES_COLUMN_POSITION_SETTING_NAME       => $imageColumnPosition
        ];
        $this->import->save();
    }

    public function parse(): array
    {
        $settings = $this->import->settings ?? [];

        $startRow = isset($settings[self::START_ROW_SETTING_NAME]) ? (int)$settings[self::START_ROW_SETTING_NAME] : 1;

        $namePos = array_key_exists(self::NAME_COLUMN_POSITION_SETTING_NAME, $settings) ? $settings[self::NAME_COLUMN_POSITION_SETTING_NAME] : null;
        $skuPos = array_key_exists(self::SKU_COLUMN_POSITION_SETTING_NAME, $settings) ? $settings[self::SKU_COLUMN_POSITION_SETTING_NAME] : null;
        $pricePos = array_key_exists(self::PRICE_COLUMN_POSITION_SETTING_NAME, $settings) ? $settings[self::PRICE_COLUMN_POSITION_SETTING_NAME] : null;
        $categoryPos = array_key_exists(self::CATEGORY_COLUMN_POSITION_SETTIG_NAME, $settings) ? $settings[self::CATEGORY_COLUMN_POSITION_SETTIG_NAME] : null;
        $descriptionPos = array_key_exists(self::DESCRIPTION_COLUMN_POSITION_SETTING_NAME, $settings) ? $settings[self::DESCRIPTION_COLUMN_POSITION_SETTING_NAME] : null;
        $imagesPos = array_key_exists(self::IMAGES_COLUMN_POSITION_SETTING_NAME, $settings) ? $settings[self::IMAGES_COLUMN_POSITION_SETTING_NAME] : null;

        $filePath = $this->import->file_path ?? null;
        if (!$filePath) {
            return [];
        }

        $fullPath = storage_path('app/' . ltrim($filePath, '/'));

        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            return [];
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            return [];
        }

        $products = [];
        $rowIndex = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowIndex++;

            if ($rowIndex < $startRow) {
                continue;
            }

            $getValue = function ($pos) use ($row) {
                if ($pos === null || $pos === '') {
                    return null;
                }
                $pos = (int)$pos;
                return array_key_exists($pos, $row) ? $row[$pos] : null;
            };

            $rawImages = $getValue($imagesPos);

            $images = [];
            if ($rawImages !== null && $rawImages !== '') {
                // support comma or semicolon separated image lists
                $parts = preg_split('/[,;]+/', (string)$rawImages);
                $images = array_values(array_filter(array_map('trim', $parts), fn($v) => $v !== ''));
            }

            $products[] = [
                'name' => $getValue($namePos),
                'sku' => $getValue($skuPos),
                'price' => $getValue($pricePos),
                'category' => $getValue($categoryPos),
                'description' => $getValue($descriptionPos),
                'images' => $images,
            ];
        }

        fclose($handle);

        return $products;
    }
}
