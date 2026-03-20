# Backend

- `LandingController@storePreRegister`
  - delega para `PreRegisterController@store`.
- `PreRegisterController@store`
  - valida `plan_id` existente;
  - depois valida elegibilidade publica via `Plan::publiclyAvailable()->find($planId)`;
  - rejeita plano nao elegivel com mensagem amigavel.
- `Webhook/PreRegistrationWebhookController`
