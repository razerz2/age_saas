# Database

Tabela usada por este modulo (catalogo global oficial):

- `whatsapp_official_templates`

Campos principais relevantes para o baseline tenant oficial:

- `key` (ex.: `appointment.confirmed`)
- `meta_template_name` (nome do template na Meta; pode divergir do local e ser corrigido por sync)
- `provider` (fixo: `whatsapp_business`)
- `category` (ex.: `UTILITY`, `SECURITY`)
- `language` (padrao: `pt_BR`)
- `body_text` (texto local para submissao/registro)
- `variables` (map local de placeholders -> variavel semantica)
- `sample_variables` (map local de placeholder -> exemplo exigido pela Meta)
- `status` (`draft`, `pending`, `approved`, `rejected`, `archived`)
- `version` (int)
- `meta_template_id` (id remoto, quando existir)
- `meta_response` (snapshot remoto do template, usado para schema efetivo)
- `last_synced_at` (timestamp do ultimo sync)

Observacao:

- este modulo nao possui tabela propria: ele opera em um subconjunto (keys tenant) do catalogo global `whatsapp_official_templates`;
- a separacao entre Platform vs Tenant e feita por dominio/keys e por navegacao, nao por uma segunda fonte de verdade no banco.
