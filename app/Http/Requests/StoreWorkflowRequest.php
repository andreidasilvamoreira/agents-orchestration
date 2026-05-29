<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkflowRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('definition'))) {
            $decoded = json_decode($this->input('definition'), true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge([
                    'definition' => $decoded,
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
        $workflow = $this->route('workflow');

        return [
            'agent_team_id' => ['nullable', 'exists:agent_teams,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('workflows', 'slug')->ignore($workflow?->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'definition' => ['required', 'array'],
            'definition.steps' => ['required', 'array', 'min:1'],
            'definition.steps.*.key' => ['nullable', 'string', 'max:255'],
            'definition.steps.*.agent_id' => ['required_without:definition.steps.*.agent_slug', 'nullable', 'exists:agents,id'],
            'definition.steps.*.agent_slug' => ['required_without:definition.steps.*.agent_id', 'nullable', 'string', 'max:255'],
            'definition.steps.*.prompt' => ['required', 'string'],
            'definition.steps.*.system_prompt' => ['nullable', 'string'],
            'definition.steps.*.save_as' => ['nullable', 'string', 'max:255'],
            'definition.steps.*.output_format' => ['nullable', Rule::in(['text', 'json'])],
        ];
    }

    public function messages(): array
    {
        return [
            'definition.array' => 'A definição do workflow precisa ser um JSON válido.',
            'input.array' => 'O input da execução precisa ser um JSON válido.',
        ];
    }
}
