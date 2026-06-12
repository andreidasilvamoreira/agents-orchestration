<?php

return [
    'driver' => env('AGENT_AI_DRIVER', 'ollama'),

    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.1'),
        'timeout' => (int) env('OLLAMA_TIMEOUT', 120),
        'options' => [
            'temperature' => (float) env('OLLAMA_TEMPERATURE', 0.2),
        ],
    ],

    'openai_compatible' => [
        'base_url' => env('OPENAI_COMPATIBLE_BASE_URL', 'http://127.0.0.1:1234'),
        'model' => env('OPENAI_COMPATIBLE_MODEL', 'local-model'),
        'api_key' => env('OPENAI_COMPATIBLE_API_KEY'),
        'timeout' => (int) env('OPENAI_COMPATIBLE_TIMEOUT', 120),
        'temperature' => (float) env('OPENAI_COMPATIBLE_TEMPERATURE', 0.2),
    ],

    'codex_cli' => [
        'binary' => env('CODEX_CLI_BINARY', 'codex'),
        'model' => env('CODEX_CLI_MODEL', 'gpt-5.4'),
        'timeout' => (int) env('CODEX_CLI_TIMEOUT', 600),
        'working_directory' => env('CODEX_CLI_WORKDIR', base_path()),
        'profile' => env('CODEX_CLI_PROFILE'),
        'sandbox' => env('CODEX_CLI_SANDBOX', 'workspace-write'),
        'bypass_approvals_and_sandbox' => filter_var(env('CODEX_CLI_BYPASS_SANDBOX', false), FILTER_VALIDATE_BOOLEAN),
        'available_models' => array_values(array_filter(array_map(
            static fn (string $model) => trim($model),
            explode(',', (string) env('CODEX_CLI_AVAILABLE_MODELS', 'gpt-5.4'))
        ))),
    ],
];
