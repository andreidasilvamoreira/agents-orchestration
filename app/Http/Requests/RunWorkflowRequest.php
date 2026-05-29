<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunWorkflowRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('input'))) {
            $decoded = json_decode($this->input('input'), true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge([
                    'input' => $decoded,
                ]);
            }
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'input' => ['nullable', 'array'],
            'dispatch' => ['sometimes', 'boolean'],
        ];
    }
}
