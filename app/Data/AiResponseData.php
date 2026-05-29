<?php

namespace App\Data;

class AiResponseData
{
    public function __construct(
        public readonly string $content,
        public readonly array $raw = [],
        public readonly ?string $model = null,
    ) {
    }
}
