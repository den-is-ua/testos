<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Import;

class ImportHashNotInProgress implements Rule
{
    public function passes($attribute, $value): bool
    {
        $realPath = $value->getRealPath();
        $hash = hash_file('sha256', $realPath);

        return !Import::where('hash_content', $hash)->whereNull('completed_at')->exists();
    }

    public function message(): string
    {
        return 'An import with the same file content is already in progress.';
    }
}
