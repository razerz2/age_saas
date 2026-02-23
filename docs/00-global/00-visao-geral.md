# 🌐 Visão Geral Global

Este documento apresenta uma visão geral do produto de agendamento clínico em modelo SaaS e das principais áreas funcionais.

## Produto

- Sistema de agendamento médico multiárea baseado em Laravel.
- Suporte a múltiplas clínicas (tenants) com isolamento de dados.
- Integrações com serviços externos (pagamentos, calendários, etc.).

## Áreas principais

- **Platform**: área administrativa central da plataforma.
- **Tenant**: área de trabalho de cada clínica (agendamentos, pacientes, médicos, etc.).
- **Landing Page**: site público de apresentação, planos e pré‑cadastro.
- **Portal do Paciente**: acesso direto do paciente para ver e gerenciar seus agendamentos.

Para detalhes técnicos, consulte também:

- `ARQUITETURA.md` (arquitetura detalhada, rotas e controllers).
- `PLATFORM.md` (documentação atual da área Platform).
- `TENANT.md` (documentação atual da área Tenant).
