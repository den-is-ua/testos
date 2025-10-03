<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AISetupperSettingsContract;
use App\Models\Import;
use Gemini\Data\Blob;
use Gemini\Data\GenerationConfig;
use Gemini\Enums\MimeType;
use Gemini\Enums\ResponseMimeType;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Storage;

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
        $separator = CSVParserService::SEPARATOR_SETTING_NAME;
        $endclosure = CSVParserService::ENDCLOUSURE_SETTING_NAME;
        $escape = CSVParserService::ESCAPE_SETTING_NAME;

        $prompt = strtr(<<<'PROMPT'
            You are configuring a CSV parser.

            You will receive a CSV file as an attached file part (MIME text/csv).
            Analyze it and return ONLY a minified JSON object with EXACTLY these keys:

            - "{CONST_START_ROW}"
            - "{CONST_NAME_COL}"
            - "{CONST_SKU_COL}"
            - "{CONST_PRICE_COL}"
            - "{CONST_CATEGORY_COL}"
            - "{CONST_DESCRIPTION_COL}"
            - "{CONST_IMAGES_COL}"
            - "{CONST_SEPARATOR}"
            - "{CONST_ENCLOSURE}"
            - "{CONST_ESCAPE}"

            Rules:
            1) Column positions are 1-based (first column is 1).
            2) Detect if the first row is a header. If it looks like a header (non-numeric tokens, common header names), set "{CONST_START_ROW}" = 2, else 1.
            3) Map columns by robust header matching (case-insensitive, trim):
            - Name: ["name","product name","title","назва","наименование"]
            - SKU:  ["sku","article","code","код","артикул","product code","ean","upc","id"]
            - Price:["price","cost","amount","ціна","стоимость"]
            - Category:["category","cat","категория","категорія"]
            - Description:["description","desc","опис","описание"]
            - Images:["image","images","image url","img","photo","photos","picture","pictures"]
            4) If SKU is missing, use an ID-like column as SKU (preferred order: "sku" > "product_id" > "id" > "code" > "article" > "ean" > "upc").
            If nothing found, choose the column with mostly unique non-empty values and treat it as SKU.
            5) If there is no header, infer columns by content heuristics:
            - price: numeric values with decimals, currency-like patterns
            - images: many cells containing URLs or file paths (http(s)://, .jpg/.png/.webp)
            6) Detect the CSV separator by frequency and parseability among [",",";","|","\t"]. Use the one that yields the most sane columns; prefer "," when ambiguous.
            7) Detect the enclosure (usually `"`). If unclear, use `"`; escape is `\` by default.
            8) Always include ALL keys in the JSON. Use null only where the column truly does not exist.
            9) Output MUST be ONLY a single minified JSON object. No markdown, no backticks, no commentary.
            10) Start row position begin from 1

            Return exactly the JSON object.
            PROMPT, [
            '{CONST_START_ROW}' => $startRow,
            '{CONST_NAME_COL}' => $nameColumnPosition,
            '{CONST_SKU_COL}' => $skuColumnPosition,
            '{CONST_PRICE_COL}' => $priceColumnPosition,
            '{CONST_CATEGORY_COL}' => $categoryColumnPosition,
            '{CONST_DESCRIPTION_COL}' => $descriptionColumnPosition,
            '{CONST_IMAGES_COL}' => $imagesColumnPosition,
            '{CONST_SEPARATOR}' => $separator,
            '{CONST_ENCLOSURE}' => $endclosure,
            '{CONST_ESCAPE}' => $escape,
        ]);

        $result = Gemini::generativeModel(model: 'gemini-2.0-flash')
            ->withGenerationConfig(
                generationConfig: new GenerationConfig(responseMimeType: ResponseMimeType::APPLICATION_JSON)
            )
            ->generateContent([
                $prompt,
                new Blob(
                    data: base64_encode(Storage::get($import->file_path)),
                    mimeType: MimeType::TEXT_CSV
                ),
            ]);

        $CSVParserService = new CSVParserService($import);
        $CSVParserService->setupSettings(...(array) $result->json());
    }
}
