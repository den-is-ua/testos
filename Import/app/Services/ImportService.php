<?php

namespace App\Services;

use App\Models\Import;
use Illuminate\Http\UploadedFile;



class ImportService  
{
    public static function store(UploadedFile $file)
    {
        $path = $file->store('imports');
        $fullPath = storage_path('app/' . $path);
        $hash = file_exists($fullPath) ? hash_file('sha256', $fullPath) : null;

        return Import::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_extension' => substr($file->getClientOriginalExtension() ?? '', 0, 4),
            'hash_content' => $hash
        ]);
    }
}
