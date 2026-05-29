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
];
