# Overview

O dominio de localidades e exclusivo do Brasil.

## Mudanca principal
- A base manual legada foi substituida por base oficial do IBGE.
- A sincronizacao e incremental para preservar IDs internos e relacoes existentes.

## Decisoes arquiteturais
- `estados.ibge_id` e `cidades.ibge_id` armazenam identificadores oficiais.
- O match com ViaCEP usa `ibge` do municipio, nunca apenas nome textual.
- ViaCEP nao e fonte mestra de localidade, somente helper de input.
- Nao ha fluxo funcional de gestao de paises na UI administrativa.
- Colunas/tabela de pais permanecem apenas por compatibilidade com FKs legadas.
