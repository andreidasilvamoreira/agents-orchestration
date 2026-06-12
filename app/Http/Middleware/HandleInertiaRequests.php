<?php

namespace App\Http\Middleware;

use App\Contracts\AiDriver;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Throwable;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $connected = true;

        try {
            app(AiDriver::class)->availableModels();
        } catch (Throwable) {
            $connected = false;
        }

        return [
            ...parent::share($request),
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
            'ai' => [
                'driver' => config('agent_ai.driver'),
                'model' => $this->currentModelName(),
                'is_connected' => $connected,
                'status_label' => $connected ? 'Conectada e funcionando' : 'Offline',
            ],
        ];
    }

    protected function currentModelName(): string
    {
        return match (config('agent_ai.driver')) {
            'ollama' => (string) config('agent_ai.ollama.model'),
            'openai_compatible' => (string) config('agent_ai.openai_compatible.model'),
            'codex_cli' => (string) config('agent_ai.codex_cli.model'),
            default => 'modelo-nao-configurado',
        };
    }
}
