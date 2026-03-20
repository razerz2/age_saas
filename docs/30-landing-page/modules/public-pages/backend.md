# Backend

- `app/Models/Platform/Plan.php`
  - scope `publiclyAvailable()`: centraliza elegibilidade de plano para uso publico.
- `app/Http/Controllers/Landing/LandingController.php`
  - `index`, `plans` e `getPlan` usam apenas `Plan::publiclyAvailable()`.
