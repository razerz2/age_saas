# Permissões e Módulos (Índice Global)

Este documento descreve, de forma resumida, como o sistema organiza permissões por área e onde ficam os detalhes.

## Visão geral

- Uso de **guards** diferentes por área (ex.: `web`, `tenant`, `network`, `patient`).
- Uso de campos `modules` (JSON) e/ou `role` em models de usuário para controle de acesso.

## Onde estão os detalhes

- `PLATFORM.md` → descreve os módulos da área Platform e o campo `modules` do usuário administrativo.
- `TENANT.md` → descreve roles (`admin`, `doctor`, `user`) e módulos (`appointments`, `patients`, `finance`, etc.).

> Este arquivo é um ponto central para, no futuro, consolidar a tabela de chaves canônicas e regras por plano/perfil.
