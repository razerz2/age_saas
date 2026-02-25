# Permissions

## Acesso ao Editor

O Editor fica dentro de Settings do tenant:

- URL base: `GET /workspace/{slug}/settings?tab=editor`

Regras práticas:

- Apenas usuários autenticados do tenant com acesso ao módulo/tela de Settings conseguem visualizar e editar templates.
- Preview também exige o mesmo acesso (é um `POST` na área autenticada).

Observação:

- As rotas públicas (`/customer/{slug}/...`) não expõem o Editor; apenas consomem links gerados para confirmação/cancelamento/oferta.

