<?php

namespace App\Rules;

use App\Models\Import;
use Illuminate\Contracts\Validation\Rule;

class ImportHasNotInProgress implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (empty($value)) {
            return true;
        }

        if (config('app.allow_upload_duplicated_file')) {
            return true;
        }

        $realPath = $value->getRealPath();
        $hash = hash_file('sha256', $realPath);

        return ! Import::where('hash_content', $hash)->whereNull('completed_at')->exists();
    }

    public function message(): string
    {
        return 'An import with the same file content is already in progress.';
    }
}
