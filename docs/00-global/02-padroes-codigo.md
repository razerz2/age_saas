# Padrões de Código (Laravel / Backend)

Este documento consolida convenções gerais de código usadas em todo o projeto.

## Convenções gerais

- Framework principal: Laravel.
- Separação de responsabilidades entre Controllers, Services, Form Requests, Jobs e Observers.
- Uso de Form Requests para validação sempre que possível.

## Organização de código

- Controllers focados em orquestração e fluxo HTTP.
- Regras de negócio relevantes migradas para Services ou métodos dedicados.
- Jobs usados para tarefas assíncronas (envio de notificações, integração externa, etc.).

## Relação com documentação legada

- `ARQUITETURA.md` detalha controllers, models e estrutura de pastas.
- Documentos específicos em `/docs` (ex.: financeiro, integrações) podem especificar regras adicionais.

> Este arquivo é um índice de padrões. Detalhes específicos de módulos devem ficar na documentação de módulo correspondente.
