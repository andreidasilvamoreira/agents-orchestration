<?php

namespace App\Data;

class AiRequestData
{
    public function __construct(
        public readonly string $agent,
        public readonly string $prompt,
        public readonly ?string $systemPrompt = null,
        public readonly array $context = [],
        public readonly ?string $model = null,
        public readonly ?float $temperature = null,
    ) {
    }
}
