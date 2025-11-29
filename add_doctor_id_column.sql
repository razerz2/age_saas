-- ============================================================
-- Script SQL para adicionar a coluna doctor_id na tabela appointment_types
-- Execute este script no banco de dados do tenant (PostgreSQL)
-- ============================================================

-- Passo 1: Verificar se a coluna já existe
SELECT column_name 
FROM information_schema.columns 
WHERE table_name = 'appointment_types' 
AND column_name = 'doctor_id';

-- Se não retornar nenhuma linha, execute os comandos abaixo:

-- Passo 2: Adicionar a coluna doctor_id
ALTER TABLE appointment_types 
ADD COLUMN IF NOT EXISTS doctor_id UUID;

-- Passo 3: Adicionar a foreign key (se ainda não existir)
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint 
        WHERE conname = 'appointment_types_doctor_id_foreign'
    ) THEN
        ALTER TABLE appointment_types 
        ADD CONSTRAINT appointment_types_doctor_id_foreign 
        FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE;
    END IF;
END $$;

-- Passo 4: Criar índice para melhor performance (se ainda não existir)
CREATE INDEX IF NOT EXISTS appointment_types_doctor_id_index 
ON appointment_types(doctor_id);

-- Verificar se foi criado com sucesso
SELECT column_name, data_type, is_nullable
FROM information_schema.columns 
WHERE table_name = 'appointment_types' 
AND column_name = 'doctor_id';

