<?php

namespace App\Providers;

use App\Contracts\AiDriver;
use App\Exceptions\WorkflowException;
use App\Services\Ai\Drivers\OllamaDriver;
use App\Services\Ai\Drivers\OpenAiCompatibleDriver;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiDriver::class, function ($app) {
            $driver = config('agent_ai.driver');
            $http = $app->make(HttpFactory::class);

            return match ($driver) {
                'ollama' => new OllamaDriver($http, config('agent_ai.ollama')),
                'openai_compatible' => new OpenAiCompatibleDriver($http, config('agent_ai.openai_compatible')),
                default => throw new WorkflowException("Driver de IA [{$driver}] não suportado."),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
