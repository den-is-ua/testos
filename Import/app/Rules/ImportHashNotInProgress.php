<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Import;

class ImportHashNotInProgress implements Rule
{
    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        try {
            $realPath = method_exists($value, 'getRealPath') ? $value->getRealPath() : null;
            $hash = ($realPath && file_exists($realPath)) ? hash_file('sha256', $realPath) : null;
        } catch (\Throwable $e) {
            // If we can't read the file, skip the duplicate check (allow validation to proceed)
            $hash = null;
        }

        if (! $hash) {
            return true;
        }

        return ! Import::where('hash_content', $hash)->whereNull('import_completed_at')->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'An import with the same file content is already in progress.';
    }
}
