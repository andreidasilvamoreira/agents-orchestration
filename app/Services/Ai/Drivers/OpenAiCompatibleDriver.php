<?php

namespace App\Services\Ai\Drivers;

use App\Contracts\AiDriver;
use App\Data\AiRequestData;
use App\Data\AiResponseData;
use Illuminate\Http\Client\Factory as HttpFactory;

class OpenAiCompatibleDriver implements AiDriver
{
    public function __construct(
        protected HttpFactory $http,
        protected array $config,
    ) {
    }

    public function generate(AiRequestData $request): AiResponseData
    {
        $response = $this->client()->post('/v1/chat/completions', [
            'model' => $request->model ?: $this->config['model'],
            'temperature' => $request->temperature ?? $this->config['temperature'],
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
        ])->throw();

        $payload = $response->json();

        return new AiResponseData(
            content: data_get($payload, 'choices.0.message.content', ''),
            raw: $payload,
            model: data_get($payload, 'model', $request->model ?: $this->config['model']),
        );
    }

    public function availableModels(): array
    {
        $response = $this->client()
            ->get('/v1/models')
            ->throw();

        return collect($response->json('data', []))
            ->pluck('id')
            ->filter()
            ->values()
            ->all();
    }

    protected function client()
    {
        $client = $this->http->baseUrl($this->config['base_url'])
            ->timeout($this->config['timeout'])
            ->acceptJson();

        if ($this->config['api_key']) {
            $client = $client->withToken($this->config['api_key']);
        }

        return $client;
    }
}
