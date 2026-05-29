# Workflow Agents

Projeto Laravel para orquestrar workflows com agentes usando uma IA própria, sem depender de token de API por padrão.

## O que já vem pronto

- Cadastro de workflows em JSON.
- Execução de workflows com passos sequenciais.
- Persistência de execuções e histórico de cada passo.
- Driver local para `Ollama`.
- Driver `OpenAI compatible` para LM Studio, Open WebUI proxy, vLLM ou outro endpoint local.
- Exemplo de workflow seedado para qualificação de leads.

## Estrutura principal

- `app/Models/Workflow.php`: definição do workflow.
- `app/Models/WorkflowRun.php`: execução completa.
- `app/Models/WorkflowStepRun.php`: histórico detalhado de cada agente/passo.
- `app/Services/Workflows/WorkflowRunner.php`: motor que interpreta os passos.
- `app/Services/Ai/*`: integração com a IA local.
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
