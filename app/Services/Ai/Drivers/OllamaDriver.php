<?php

namespace App\Services\Ai\Drivers;

use App\Contracts\AiDriver;
use App\Data\AiRequestData;
use App\Data\AiResponseData;
use Illuminate\Http\Client\Factory as HttpFactory;

class OllamaDriver implements AiDriver
{
    public function __construct(
        protected HttpFactory $http,
        protected array $config,
    ) {
    }

    public function generate(AiRequestData $request): AiResponseData
    {
        $response = $this->client()
            ->post('/api/chat', [
                'model' => $request->model ?: $this->config['model'],
                'stream' => false,
                'messages' => array_values(array_filter([
                    $request->systemPrompt ? [
                        'role' => 'system',
                        'content' => $request->systemPrompt,
                    ] : null,
                    [
                        'role' => 'user',
                        'content' => $request->prompt,
                    ],
                ])),
                'options' => [
                    ...$this->config['options'],
                    'temperature' => $request->temperature ?? $this->config['options']['temperature'] ?? null,
                ],
            ])
            ->throw();

        $payload = $response->json();

        return new AiResponseData(
            content: data_get($payload, 'message.content', ''),
            raw: $payload,
            model: data_get($payload, 'model', $request->model ?: $this->config['model']),
        );
    }

    public function availableModels(): array
    {
        $response = $this->client()
            ->get('/api/tags')
            ->throw();

        return collect($response->json('models', []))
            ->pluck('name')
            ->filter()
            ->values()
            ->all();
    }

    protected function client()
    {
        return $this->http->baseUrl($this->config['base_url'])
            ->timeout($this->config['timeout'])
            ->acceptJson();
    }
}
