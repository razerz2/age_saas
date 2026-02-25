# Views

## Settings > Editor

Arquivos:

- `resources/views/tenant/settings/index.blade.php` (tab `editor`)
- `resources/views/tenant/settings/tabs/editor.blade.php` (UI do Editor)

O Editor é renderizado dentro da tela de Settings e carrega o template efetivo (default ou override) para o canal/key selecionado.

## Preview

O preview é renderizado na própria aba Editor (sem AJAX), abaixo do formulário, quando acionado via `POST settings/editor/preview`.

