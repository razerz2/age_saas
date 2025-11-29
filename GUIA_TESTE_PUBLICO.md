# 🧪 Guia de Teste - Área Pública de Agendamento

## 📋 Pré-requisitos

Antes de testar, certifique-se de que:

1. ✅ O servidor Laravel está rodando
2. ✅ O banco de dados está configurado
3. ✅ Existe pelo menos um Tenant cadastrado no sistema
4. ✅ O Tenant tem um banco de dados configurado

---

## 🔗 URLs das Páginas Públicas

As páginas públicas seguem o padrão: `/t/{subdomain}/agendamento/{acao}`

### 1️⃣ **Identificação do Paciente**
```
GET  /t/{tenant}/agendamento/identificar
POST /t/{tenant}/agendamento/identificar
```

### 2️⃣ **Cadastro de Paciente**
```
GET  /t/{tenant}/agendamento/cadastro
POST /t/{tenant}/agendamento/cadastro
```

### 3️⃣ **Criar Agendamento**
```
GET  /t/{tenant}/agendamento/criar
POST /t/{tenant}/agendamento/criar
```

### 4️⃣ **Página de Sucesso**
```
GET  /t/{tenant}/agendamento/sucesso
```

---

## 🚀 Como Acessar

### Opção 1: Usando Tenant de Teste Existente

Se você já tem tenants cadastrados, verifique o `subdomain` no banco:

```sql
SELECT subdomain, trade_name, legal_name FROM tenants;
```

Exemplo: Se o subdomain for `odontovida`, acesse:
```
http://localhost/t/odontovida/agendamento/identificar
```

### Opção 2: Criar um Tenant de Teste

1. **Via Seeder** (se estiver disponível):
```bash
php artisan db:seed --class=TenantsSeeder
```

2. **Via Interface Admin**:
   - Acesse a área da plataforma
   - Vá em Tenants
   - Crie um novo tenant

3. **Via Tinker**:
```bash
php artisan tinker
```

```php
$tenant = \App\Models\Platform\Tenant::create([
    'legal_name' => 'Clínica Teste',
    'trade_name' => 'Clínica Teste',
    'subdomain' => 'teste',
    'document' => '12345678900',
    'email' => 'teste@clinica.com',
    'status' => 'active',
    'db_host' => '127.0.0.1',
    'db_port' => '5432',
    'db_name' => 'clinica_teste_db',
    'db_username' => 'postgres',
    'db_password' => 'senha',
]);
```

---

## 📝 Fluxo de Teste Completo

### **Passo 1: Acessar Identificação**

```
http://localhost/t/{tenant}/agendamento/identificar
```

Ou no navegador:
```
http://seu-dominio.local/t/teste/agendamento/identificar
```

**O que esperar:**
- ✅ Formulário com campo para CPF ou E-mail
- ✅ Botão "Continuar"
- ✅ Máscara automática no campo CPF

---

### **Passo 2: Testar Paciente Não Cadastrado**

1. Digite um CPF ou e-mail que **NÃO existe** no sistema
2. Clique em "Continuar"
3. **Resultado esperado:**
   - ✅ Mensagem: "Você ainda não possui cadastro na clínica."
   - ✅ Botão "Criar Cadastro" aparece

---

### **Passo 3: Criar Cadastro**

1. Clique no botão "Criar Cadastro"
2. Ou acesse diretamente:
   ```
   http://localhost/t/{tenant}/agendamento/cadastro
   ```

3. Preencha o formulário:
   - Nome Completo: *obrigatório*
   - CPF: *obrigatório*
   - Data de Nascimento: *opcional*
   - E-mail: *opcional*
   - Telefone: *opcional*

4. Clique em "Cadastrar"

**Resultado esperado:**
- ✅ Redirecionamento para identificação
- ✅ Mensagem: "Cadastro realizado com sucesso! Agora você já pode realizar seu agendamento."

---

### **Passo 4: Identificar Paciente Cadastrado**

1. Volte para a página de identificação
2. Digite o CPF ou e-mail que você acabou de cadastrar
3. Clique em "Continuar"

**Resultado esperado:**
- ✅ Redirecionamento para o formulário de agendamento
- ✅ Paciente identificado (salvo na sessão)

---

### **Passo 5: Criar Agendamento**

Acesse:
```
http://localhost/t/{tenant}/agendamento/criar
```

**O que esperar:**
- ✅ Formulário completo de agendamento
- ✅ Seleção de médico
- ✅ Seleção de calendário (carrega após escolher médico)
- ✅ Tipo de consulta (carrega após escolher médico)
- ✅ Especialidade (carrega após escolher médico)
- ✅ Seleção de data
- ✅ Horários disponíveis (carrega após escolher data)

**Fluxo de preenchimento:**
1. Selecione um médico
2. Selecione o calendário
3. Selecione tipo de consulta (opcional)
4. Selecione especialidade (opcional)
5. Selecione uma data
6. Selecione um horário disponível
7. Adicione observações (opcional)
8. Clique em "Confirmar Agendamento"

**Resultado esperado:**
- ✅ Redirecionamento para página de sucesso
- ✅ Mensagem de confirmação
- ✅ Agendamento criado no banco de dados

---

## 🧪 Cenários de Teste

### ✅ **Teste 1: Validação de Duplicidade**
1. Tente cadastrar um paciente com CPF já existente
2. **Esperado:** Mensagem de erro "Este CPF já está cadastrado na clínica."

### ✅ **Teste 2: Validação de E-mail Duplicado**
1. Tente cadastrar um paciente com e-mail já existente
2. **Esperado:** Mensagem de erro "Este e-mail já está cadastrado na clínica."

### ✅ **Teste 3: Campos Obrigatórios**
1. Tente cadastrar sem preencher nome ou CPF
2. **Esperado:** Mensagens de validação indicando campos obrigatórios

### ✅ **Teste 4: Acesso Sem Identificação**
1. Tente acessar `/t/{tenant}/agendamento/criar` diretamente
2. **Esperado:** Redirecionamento para identificação com mensagem de erro

### ✅ **Teste 5: Máscaras de Formatação**
1. Digite CPF sem formatação: `12345678900`
2. **Esperado:** Formatação automática: `123.456.789-00`

---

## 🔍 Verificações Importantes

### **No Banco de Dados do Tenant:**

```sql
-- Verificar pacientes cadastrados
SELECT * FROM patients;

-- Verificar agendamentos criados
SELECT * FROM appointments ORDER BY created_at DESC;

-- Verificar se o paciente foi criado corretamente
SELECT id, full_name, cpf, email, is_active, created_at 
FROM patients 
WHERE cpf = '12345678900';
```

---

## 🐛 Troubleshooting

### **Erro 404 - Tenant não encontrado**
- ✅ Verifique se o tenant existe no banco
- ✅ Verifique se o subdomain está correto
- ✅ Verifique se o middleware `tenant-web` está funcionando

### **Erro de Conexão com Banco**
- ✅ Verifique as configurações do tenant no banco
- ✅ Certifique-se de que o banco do tenant existe
- ✅ Verifique credenciais de acesso

### **Erro "Paciente não encontrado"**
- ✅ Certifique-se de que o paciente foi cadastrado no banco correto (do tenant)
- ✅ Verifique se o CPF/e-mail foi digitado corretamente
- ✅ Verifique se o paciente está ativo (`is_active = true`)

---

## 📱 Exemplos de URLs Completas

Substitua `{tenant}` pelo subdomain do seu tenant:

```
# Identificação
http://localhost/t/odontovida/agendamento/identificar

# Cadastro
http://localhost/t/odontovida/agendamento/cadastro

# Agendamento
http://localhost/t/odontovida/agendamento/criar

# Sucesso
http://localhost/t/odontovida/agendamento/sucesso
```

---

## ✅ Checklist de Teste

- [ ] Página de identificação carrega
- [ ] Formulário de identificação funciona
- [ ] Mensagem quando paciente não encontrado
- [ ] Botão "Criar Cadastro" aparece e funciona
- [ ] Formulário de cadastro carrega
- [ ] Validação de campos obrigatórios funciona
- [ ] Validação de duplicidade de CPF funciona
- [ ] Validação de duplicidade de e-mail funciona
- [ ] Máscaras de CPF e telefone funcionam
- [ ] Cadastro redireciona corretamente após sucesso
- [ ] Identificação funciona após cadastro
- [ ] Formulário de agendamento carrega
- [ ] Seleção de médico funciona
- [ ] Carregamento dinâmico de calendários funciona
- [ ] Carregamento dinâmico de tipos funciona
- [ ] Carregamento dinâmico de especialidades funciona
- [ ] Seleção de data funciona
- [ ] Carregamento de horários disponíveis funciona
- [ ] Criação de agendamento funciona
- [ ] Página de sucesso aparece após agendamento

---

**Bons testes! 🚀**

