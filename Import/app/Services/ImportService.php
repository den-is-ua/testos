<?php

namespace App\Services;

use App\Models\Import;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;



class ImportService  
{
    public static function store(UploadedFile $file)
    {
        $path = $file->store('imports');
        $disk = config('filesystems.default');
        $content = Storage::disk($disk)->get($path);
        $hash = hash('sha256', $content);

        return Import::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => substr($file->getClientOriginalExtension() ?? '', 0, 4),
            'hash_content' => $hash
        ]);
    }

    public static function setupSettingsByGemini(Import $import)
    {
        
    }

    public static function defineAndParse()
    {

    }

    public static function parseCSV()
    {

    }

    public static function parseXML()
    {
        
    }
}
