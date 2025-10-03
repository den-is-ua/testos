<?php

namespace App\Http\Requests;

use App\Rules\ImportHasNotInProgress;
use Illuminate\Foundation\Http\FormRequest;

class StoreImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'bail',
                'required',
                'file',
                'max:10240',
                new ImportHasNotInProgress,
            ],
        ];
    }
}
