<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ImportHashNotInProgress;

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
                new ImportHashNotInProgress(),
            ],
        ];
    }
}
