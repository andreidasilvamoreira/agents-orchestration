<?php

namespace App\Services\Ai;

use App\Contracts\AiDriver;
use App\Data\AiRequestData;
use App\Data\AiResponseData;
use App\Exceptions\WorkflowException;

class AiManager
{
    public function __construct(
        protected AiDriver $driver,
    ) {
    }

    public function ask(AiRequestData $request): AiResponseData
    {
        $response = $this->driver->generate($request);

        if (blank($response->content)) {
            throw new WorkflowException('A IA local respondeu sem conteúdo.');
        }

        return $response;
    }
}
