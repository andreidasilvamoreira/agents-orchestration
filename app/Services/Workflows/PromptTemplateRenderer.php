<?php

namespace App\Services\Workflows;

class PromptTemplateRenderer
{
    public function render(?string $template, array $context): ?string
    {
        if ($template === null) {
            return null;
        }

        return preg_replace_callback('/{{\s*([\w\.]+)\s*}}/', function (array $matches) use ($context) {
            $value = data_get($context, $matches[1]);

            if (is_array($value)) {
                return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }

            return (string) ($value ?? '');
        }, $template);
    }
}
