<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\File;
use Illuminate\Foundation\Http\FormRequest;

class VerificationRequest extends FormRequest
{
    public const MAX_UPLOAD_SIZE = 2;
    public const KB_MB_CONVERSION = 1024;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file'    => [
                'required',
                File::types(['json'])
                    ->max(self::MAX_UPLOAD_SIZE * self::KB_MB_CONVERSION)
            ]
        ];
    }
}
