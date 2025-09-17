<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ParserContract;
use App\Models\Import;
use App\OV\ProductOV;
use Exception;
use Illuminate\Support\Facades\Storage;



class CSVParserService implements ParserContract
{
    const START_ROW_SETTING_NAME = 'startRow';
    const NAME_COLUMN_POSITION_SETTING_NAME = 'nameColumnPosition';
    const SKU_COLUMN_POSITION_SETTING_NAME = 'skuColumnPosition';
    const PRICE_COLUMN_POSITION_SETTING_NAME = 'priceColumnPosition';
    const CATEGORY_COLUMN_POSITION_SETTIG_NAME = 'categoryColumnPosition';
    const DESCRIPTION_COLUMN_POSITION_SETTING_NAME = 'descriptionColumnPosition';
    const IMAGES_COLUMN_POSITION_SETTING_NAME = 'imagesColumnPosition';
    const SEPARATOR_SETTING_NAME = 'separator';
    const ENDCLOUSURE_SETTING_NAME = 'endclosure';
    const ESCAPE_SETTING_NAME = 'escape';


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
        ?int $imagesColumnPosition,
        string $separator = ',',
        string $endclosure = '"',
        string $escape = '\\', 
    ) {
        $this->import->settings = [
            self::START_ROW_SETTING_NAME                    => $startRow,
            self::NAME_COLUMN_POSITION_SETTING_NAME         => $nameColumnPosition,
            self::SKU_COLUMN_POSITION_SETTING_NAME          => $skuColumnPosition,
            self::PRICE_COLUMN_POSITION_SETTING_NAME        => $priceColumnPosition,
            self::CATEGORY_COLUMN_POSITION_SETTIG_NAME      => $categoryColumnPosition,
            self::DESCRIPTION_COLUMN_POSITION_SETTING_NAME  => $descriptionColumnPosition,
            self::IMAGES_COLUMN_POSITION_SETTING_NAME       => $imagesColumnPosition,
            self::SEPARATOR_SETTING_NAME                    => $separator,
            self::ENDCLOUSURE_SETTING_NAME                  => $endclosure,
            self::ESCAPE_SETTING_NAME                       => $escape
        ];
        $this->import->save();
    }

    public function parse(): iterable
    {
        if (empty($this->import->settings)) {
            throw new Exception('Need to setup settings before parsing!');
        }

        $settings = $this->import->settings;

        $startRow = $settings[self::START_ROW_SETTING_NAME] ?? 1;

        $namePos = $settings[self::NAME_COLUMN_POSITION_SETTING_NAME];
        $skuPos = $settings[self::SKU_COLUMN_POSITION_SETTING_NAME];
        $pricePos = $settings[self::PRICE_COLUMN_POSITION_SETTING_NAME];
        $categoryPos = $settings[self::CATEGORY_COLUMN_POSITION_SETTIG_NAME] ?? null;
        $descriptionPos = $settings[self::DESCRIPTION_COLUMN_POSITION_SETTING_NAME] ?? null;
        $imagesPos = $settings[self::IMAGES_COLUMN_POSITION_SETTING_NAME] ?? null;

        $fullPath = Storage::path($this->import->file_path);

        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            throw new Exception('Import file doesnt exists!');
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            throw new Exception('Import file cant be read!');
        }

        $buffer = [];
        $rowIndex = 0;
        $separator = $settings[self::SEPARATOR_SETTING_NAME];
        $endclousure = $settings[self::ENDCLOUSURE_SETTING_NAME];
        $escape = $settings[self::ESCAPE_SETTING_NAME];
        $chunkSize = 100;

        try {
            while (($row = fgetcsv($handle, null, $separator, $endclousure, $escape )) !== false) {
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

                $buffer[] = new ProductOV(
                    $getValue($namePos),
                    $getValue($skuPos),
                    (float)$getValue($pricePos),
                    $getValue($categoryPos),
                    $getValue($descriptionPos),
                    $images,
                );

                if (count($buffer) >= $chunkSize) {
                    yield $buffer;
                    $buffer = [];
                }
            }

            if (!empty($buffer)) {
                yield $buffer;
            }
        } finally {
            fclose($handle);
        }
    }
}
