# Backend

Componentes principais:

- controller: `app/Http/Controllers/Platform/EmailLayoutController.php`
- model: `app/Models/Platform/EmailLayout.php`

Aplicacao do layout:

- service: `app/Services/TemplateRenderer.php` (aplica `EmailLayout::getActiveLayout()` ao renderizar emails).
