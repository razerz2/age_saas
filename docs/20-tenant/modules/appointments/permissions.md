# Permissions

## Resumo

Não houve introdução de nova permissão/role específica para hold ou waitlist.

## Acesso interno

As rotas internas de appointments continuam dentro do grupo autenticado:

- Prefixo: `/workspace/{slug}`
- Middlewares do grupo: `web`, `persist.tenant`, `tenant.from.guard`, `ensure.guard`, `tenant.auth`

Rotas novas de appointments (confirm/cancel/waitlist) herdam esse mesmo contexto.

## Acesso de settings

A configuração de agendamentos continua sob módulo de settings:

- Rota: `POST /workspace/{slug}/settings/appointments`
- Envolvida pelo grupo com middleware `module.access:settings`

## Acesso público

Rotas públicas de agendamento/waitlist usam:

- Prefixo: `/customer/{slug}`
- Middleware: `tenant-web`

Validações adicionais de contexto público:

- Identificação de paciente via sessão para store público/waitlist público.
- `show` público de appointment valida ownership pelo paciente em sessão.

## Privacidade

No endpoint público de slots (`/customer/{slug}/agendamento/api/doctors/{doctorId}/available-slots`):

- Não retorna `appointment_id`.
- Não retorna dados de paciente/agendamento existente.

## Recorrência

Sem mudanças de permissão no módulo de recorrência.

