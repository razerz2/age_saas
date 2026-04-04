# Encoding Policy

Todos os arquivos textuais do projeto devem seguir estas regras:

- Encoding: UTF-8 sem BOM.
- Quebra de linha: LF (`\n`).
- Nunca salvar em ANSI, Windows-1252 ou Latin1.

Sinais comuns de encoding quebrado (mojibake):

- `Ã`
- `Â`
- `�`
- `ÃƒÂ`
- `â€™`
- `â€œ`
- `â€`

Se qualquer um desses sinais aparecer em arquivo de código, view, config ou doc, corrija antes do commit.

Validação automática:

```bash
composer check:encoding
```
