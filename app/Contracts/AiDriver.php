<?php

namespace App\Contracts;

use App\Data\AiRequestData;
use App\Data\AiResponseData;

interface AiDriver
{
    public function generate(AiRequestData $request): AiResponseData;

    public function availableModels(): array;
}
