# ✅ Checklist de Go-Live - Módulo Financeiro

## 📋 Pré-requisitos

### Infraestrutura

- [ ] **Fila finance ativa**
  - [ ] Worker configurado: `php artisan queue:work --queue=finance --tries=3 --timeout=60`
  - [ ] Supervisor configurado para manter worker ativo
  - [ ] Monitoramento de fila implementado

- [ ] **SSL ativo**
  - [ ] Certificado SSL válido
  - [ ] HTTPS obrigatório para webhooks
  - [ ] URLs de webhook usando HTTPS

- [ ] **Rate limit aplicado**
  - [ ] Middleware `throttle.asaas.webhook` ativo
  - [ ] Limite: 60 req/min por IP
  - [ ] Testado e funcionando

- [ ] **Logs configurados**
  - [ ] Canal `finance` ativo
  - [ ] Rotação de logs configurada (30 dias)
  - [ ] Acesso a logs para troubleshooting

### Asaas

- [ ] **Conta produção ativa**
  - [ ] Ambiente configurado: `finance.asaas.environment = production`
  - [ ] API Key de produção configurada
  - [ ] API Key testada e validada

- [ ] **Webhook configurado**
  - [ ] URL de webhook: `https://seu-dominio.com/t/{tenant}/webhooks/asaas`
  - [ ] Webhook secret configurado em `finance.asaas.webhook_secret`
  - [ ] Secret validado e testado

- [ ] **Teste de pagamento real**
  - [ ] Pagamento de teste realizado
  - [ ] Webhook recebido e processado
  - [ ] Charge atualizada corretamente
  - [ ] Transação criada
  - [ ] Comissão criada (se habilitada)

### Sistema

- [ ] **Módulo habilitado**
  - [ ] `finance.enabled = true`
  - [ ] Menu financeiro visível
  - [ ] Rotas acessíveis

- [ ] **Configurações revisadas**
  - [ ] Modo de cobrança configurado (`billing_mode`)
  - [ ] Valores de cobrança definidos
  - [ ] Origem de cobrança configurada
  - [ ] Conta padrão selecionada
  - [ ] Categorias criadas
  - [ ] Comissões configuradas (se aplicável)

- [ ] **Notificações funcionando**
  - [ ] Email de pagamento testado
  - [ ] WhatsApp de pagamento testado (se habilitado)
  - [ ] Links de pagamento funcionando

- [ ] **Comandos testados**
  - [ ] `php artisan finance:reconcile` executado com sucesso
  - [ ] `php artisan finance:health-check` executado com sucesso
  - [ ] Resultados validados

## 🔒 Segurança

- [ ] **Webhook Secret**
  - [ ] Secret único por tenant
  - [ ] Secret armazenado de forma segura
  - [ ] Validação funcionando

- [ ] **IP Whitelist (opcional)**
  - [ ] Se habilitado, IPs do Asaas adicionados
  - [ ] Testado e funcionando

- [ ] **Rate Limiting**
  - [ ] Ativo e funcionando
  - [ ] Logs de bloqueios monitorados

- [ ] **Dados Sensíveis**
  - [ ] Tokens não logados completos
  - [ ] Masking funcionando em logs
  - [ ] Secrets criptografados

## 📊 Monitoramento

- [ ] **Health Checks**
  - [ ] Comando `finance:health-check` agendado
  - [ ] Alertas configurados para problemas críticos
  - [ ] Dashboard de saúde (opcional)

- [ ] **Logs**
  - [ ] Canal finance ativo
  - [ ] Logs estruturados funcionando
  - [ ] Rotação configurada

- [ ] **Métricas**
  - [ ] Taxa de sucesso de webhooks monitorada
  - [ ] Taxa de erro < 10%
  - [ ] Fila finance monitorada

## 🧪 Testes Finais

- [ ] **Fluxo completo testado**
  - [ ] Agendamento público → cobrança criada
  - [ ] Link de pagamento enviado
  - [ ] Pagamento realizado
  - [ ] Webhook recebido
  - [ ] Charge atualizada
  - [ ] Transação criada
  - [ ] Comissão criada (se aplicável)

- [ ] **Cenários de erro testados**
  - [ ] Webhook duplicado → ignorado
  - [ ] Secret inválido → rejeitado
  - [ ] IP não autorizado → rejeitado (se whitelist ativa)
  - [ ] Rate limit → bloqueado

- [ ] **Reconciliação manual testada**
  - [ ] Comando corrige inconsistências
  - [ ] Status sincronizado com Asaas

## 📝 Documentação

- [ ] **Documentação atualizada**
  - [ ] MODULO_FINANCEIRO.md atualizado
  - [ ] Checklist de go-live revisado
  - [ ] Guia de troubleshooting criado

- [ ] **Treinamento**
  - [ ] Equipe treinada no módulo
  - [ ] Processos documentados
  - [ ] Contatos de suporte definidos

## ✅ Assinaturas

- [ ] **Aprovação técnica**
  - [ ] Desenvolvedor: _________________ Data: __/__/____
  
- [ ] **Aprovação de negócio**
  - [ ] Product Owner: _________________ Data: __/__/____

- [ ] **Aprovação de infraestrutura**
  - [ ] DevOps: _________________ Data: __/__/____

## 🚀 Go-Live

- [ ] **Data de go-live definida**: __/__/____
- [ ] **Horário**: ____:____
- [ ] **Responsável**: _________________
- [ ] **Rollback plan definido**: _________________

---

## 📞 Contatos de Emergência

- **Desenvolvedor**: _________________
- **DevOps**: _________________
- **Asaas Suporte**: _________________

## 🔄 Rollback Plan

1. Desabilitar módulo: `finance.enabled = false`
2. Pausar workers da fila finance
3. Reverter código (se necessário)
4. Notificar stakeholders

---

**Última atualização**: __/__/____
**Versão**: 1.0

