<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Import extends Model
{
    use HasFactory;

    protected $table = 'imports';

    protected $fillable = [
        'file_name',
        'file_path',
        'file_extension',
        'hash_content',
        'settings',
        'progress',
        'import_completed_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'progress' => 'integer',
        'import_completed_at' => 'datetime',
    ];
}
