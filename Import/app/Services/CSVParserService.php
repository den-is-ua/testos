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
        int $categoryColumnPosition,
        int $descriptionColumnPosition,
        int $imageColumnPosition    
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
        return [];
    }
}
