<?php

namespace App\Services;

use App\Contracts\AISetupperSettingsContract;
use App\Contracts\ParserContract;
use App\Models\Import;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;



class ImportService  
{
    private ParserContract $parser;
    private AISetupperSettingsContract $AISetupperSettings;


    public function __construct(private Import $import)
    {
        $this->parser = $this->parserMap();
        $this->AISetupperSettings = $this->AISetupperSettingsMap();
    }

    public static function store(UploadedFile $file)
    {
        $path = $file->store();
        $content = Storage::get($path);
        $hash = hash('sha256', $content);

        return Import::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => substr($file->getClientOriginalExtension() ?? '', 0, 4),
            'hash_content' => $hash
        ]);
    }

    public function setupSettingsByGemini()
    {
        $this->AISetupperSettings->setupSettings($this->import);
    }

    public function parse()
    {
        $this->parser->parse();
    }

    private function parserMap(): ParserContract
    {
        return [
            'csv' => new CSVParserService($this->import)
        ][$this->import->file_extension];
    }

    private function AISetupperSettingsMap(): AISetupperSettingsContract
    {
        return [
            'csv' => new CSVAISetupperSettingsService()
        ][$this->import->file_extension];
    }
}
