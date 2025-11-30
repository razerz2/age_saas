# Instruções para Adicionar a Coluna doctor_id

## Problema
A migration não pode ser executada via `php artisan migrate` porque a conexão `tenant` é configurada dinamicamente apenas quando há um tenant ativo no sistema.

## Solução: Executar SQL Diretamente

### Opção 1: Via DBeaver ou Cliente SQL

1. Conecte-se ao banco de dados do tenant (ex: `db_aguas_guariroba`)
2. Abra o arquivo `add_doctor_id_column.sql`
3. Execute o script completo

### Opção 2: Via psql (Linha de Comando)

```bash
psql -h localhost -U seu_usuario -d db_aguas_guariroba -f add_doctor_id_column.sql
```

### Opção 3: Executar Comandos Individuais

Se preferir executar passo a passo:

```sql
-- 1. Adicionar coluna
ALTER TABLE appointment_types 
ADD COLUMN IF NOT EXISTS doctor_id UUID;

-- 2. Adicionar foreign key
ALTER TABLE appointment_types 
ADD CONSTRAINT appointment_types_doctor_id_foreign 
FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE;

-- 3. Criar índice
CREATE INDEX IF NOT EXISTS appointment_types_doctor_id_index 
ON appointment_types(doctor_id);
```

## Verificação

Após executar, verifique se a coluna foi criada:

```sql
SELECT column_name, data_type, is_nullable
FROM information_schema.columns 
WHERE table_name = 'appointment_types' 
AND column_name = 'doctor_id';
```

Deve retornar uma linha com:
- column_name: `doctor_id`
- data_type: `uuid`
- is_nullable: `YES`

## Próximos Passos

Após adicionar a coluna, você precisará:

1. **Vincular tipos de consulta existentes aos médicos:**
   ```sql
   -- Exemplo: vincular todos os tipos existentes ao primeiro médico
   UPDATE appointment_types 
   SET doctor_id = (SELECT id FROM doctors LIMIT 1)
   WHERE doctor_id IS NULL;
   ```

2. **Ou criar novos tipos de consulta vinculados a médicos específicos** através da interface do sistema.

