# Troubleshooting

## Modulo nao aparece no menu

Verifique se o usuario possui:

- `whatsapp_unofficial_templates` (modulo base) ou
- permissoes granulares correspondentes.

## Erro de chave duplicada

A `key` deve ser unica em `whatsapp_unofficial_templates`.

## Variaveis invalidas

O campo `variables` deve ser JSON valido quando informado.

## Baseline nao carregado

Execute os seeders da Platform:

- `php artisan db:seed --class=Database\\Seeders\\WhatsAppUnofficialTemplatesSeeder`

## Fallback Platform nao ocorre

O fallback para este catalogo e opt-in. Use escopo `tenant_then_platform` no resolver:

- `App\\Services\\Tenant\\WhatsAppUnofficialTemplateResolutionService`

## Teste manual bloqueado por provider

O teste manual exige provider nao oficial ativo e apto:

- WAHA com `base_url`, `api_key` e `session`
- ou Z-API com `api_url`, `token` e `instance_id`

Se o provider ativo for `whatsapp_business`, o envio manual deste modulo e bloqueado.

## Provider nao oficial indisponivel (WAHA/Z-API offline)

Sintoma:

- envio manual/preview falha com timeout ou erro de conexao ao provider.

Causa:

- WAHA ou Z-API indisponivel (servico offline, base_url/api_url incorreta, credenciais invalidas, sessao expirada).

Acao:

- validar conectividade do host/URL configurado;
- validar credenciais e estado de sessao/instancia;
- revisar logs tecnicos do adapter (WAHA/Z-API) para erro resumido.

## Preview mostra variaveis ausentes

Preencha todas as variaveis obrigatorias detectadas no template (placeholders + variaveis declaradas) antes do envio.
Use o botao de dados ficticios para auto-preenchimento inicial.
