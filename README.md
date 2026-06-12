# Agent Orchestration

Projeto Laravel para orquestrar workflows com agentes usando uma IA própria, sem depender de token de API por padrão.

## O que já vem pronto

- Cadastro de workflows em JSON.
- Execução de workflows com passos sequenciais.
- Persistência de execuções e histórico de cada passo.
- Driver local para `Ollama`.
- Driver `OpenAI compatible` para LM Studio, Open WebUI proxy, vLLM ou outro endpoint local.
- Driver `Codex CLI` para executar cada agente via `codex exec`.
- Exemplo de workflow seedado para qualificação de leads.

## Estrutura principal

- `app/Models/Workflow.php`: definição do workflow.
- `app/Models/WorkflowRun.php`: execução completa.
- `app/Models/WorkflowStepRun.php`: histórico detalhado de cada agente/passo.
- `app/Services/Workflows/WorkflowRunner.php`: motor que interpreta os passos.
- `app/Services/Ai/*`: integração com a IA local.
- `app/Services/Ai/Drivers/CodexCliDriver.php`: integração com `codex exec`.
- `routes/api.php`: endpoints da API.

## Formato do workflow

```json
{
  "name": "Lead Qualifier",
  "slug": "lead-qualifier",
  "description": "Qualifica leads com IA local.",
  "definition": {
    "steps": [
      {
        "key": "summary",
        "agent": "research-agent",
        "system_prompt": "Você é um analista comercial objetivo.",
        "prompt": "Resuma o lead abaixo: {{lead}}",
        "save_as": "lead_summary",
        "output_format": "text"
      },
      {
        "key": "classification",
        "agent": "decision-agent",
        "system_prompt": "Responda apenas com JSON válido.",
        "prompt": "{\"summary\":\"{{lead_summary}}\",\"task\":\"Classifique o lead.\"}",
        "save_as": "lead_score",
        "output_format": "json"
      }
    ]
  }
}
```

`{{...}}` usa valores do contexto acumulado. O `input` inicial entra no contexto e cada passo pode salvar saídas com `save_as`.

## Configuração da IA

Padrão:

```env
AGENT_AI_DRIVER=ollama
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=llama3.1
```

Se quiser usar um servidor local compatível com OpenAI:

```env
AGENT_AI_DRIVER=openai_compatible
OPENAI_COMPATIBLE_BASE_URL=http://127.0.0.1:1234
OPENAI_COMPATIBLE_MODEL=local-model
OPENAI_COMPATIBLE_API_KEY=
```

O token é opcional. Se o endpoint local não exigir autenticação, deixe vazio.

Se quiser usar o `codex` CLI como backend dos agentes:

```env
AGENT_AI_DRIVER=codex_cli
CODEX_CLI_BINARY=codex
CODEX_CLI_MODEL=gpt-5.4
CODEX_CLI_TIMEOUT=600
CODEX_CLI_SANDBOX=workspace-write
CODEX_CLI_AVAILABLE_MODELS=gpt-5.4
```

Pré-requisitos:

1. Instale o `codex` CLI na máquina do app.
2. Rode `codex login` no mesmo usuário que executa o PHP/Laravel.
3. Garanta que `CODEX_CLI_WORKDIR` aponte para um diretório que o Codex possa usar como workspace quando necessário.

Notas:

- Cada passo do workflow vira uma chamada `codex exec`.
- O `system_prompt` do agente e o prompt do passo são combinados em uma única instrução enviada ao CLI.
- A lista de modelos exibida na UI passa a vir de `CODEX_CLI_AVAILABLE_MODELS`; você também pode digitar um modelo manualmente.

## Subida rápida

1. Configure um banco no `.env`.
2. Rode `php artisan migrate`.
3. Rode `php artisan db:seed`.
4. Suba a aplicação com `php artisan serve`.
5. Se estiver usando fila assíncrona depois, troque `QUEUE_CONNECTION` e rode worker.

## Endpoints

- `GET /api/workflows`
- `POST /api/workflows`
- `GET /api/workflows/{workflow}`
- `POST /api/workflows/{workflow}/run`
- `GET /api/runs/{run}`

## Exemplo de execução

```bash
curl -X POST http://127.0.0.1:8000/api/workflows/1/run \
  -H 'Content-Type: application/json' \
  -d '{
    "dispatch": false,
    "input": {
      "lead": {
        "company": "Acme",
        "segment": "logistica",
        "employees": 120,
        "pain": "muito retrabalho no atendimento"
      }
    }
  }'
```

## Observação importante

O ambiente atual criou o projeto, mas o `php` CLI desta máquina está sem `pdo_sqlite`. Se você mantiver SQLite, habilite essa extensão. Se preferir MySQL/Postgres, ajuste o `.env` antes de migrar.
