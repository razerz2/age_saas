-- ============================================================
-- Script SQL SIMPLIFICADO para adicionar doctor_id
-- Execute este script no banco de dados do tenant
-- ============================================================

-- 1. Adicionar a coluna (se não existir)
ALTER TABLE appointment_types 
ADD COLUMN IF NOT EXISTS doctor_id UUID;

-- 2. Remover constraint antiga se existir (para evitar erro)
ALTER TABLE appointment_types 
DROP CONSTRAINT IF EXISTS appointment_types_doctor_id_foreign;

-- 3. Adicionar a foreign key
ALTER TABLE appointment_types 
ADD CONSTRAINT appointment_types_doctor_id_foreign 
FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE;

-- 4. Criar índice (se não existir)
CREATE INDEX IF NOT EXISTS appointment_types_doctor_id_index 
ON appointment_types(doctor_id);

-- 5. Verificar se foi criado
SELECT 
    column_name, 
    data_type, 
    is_nullable,
    column_default
FROM information_schema.columns 
WHERE table_name = 'appointment_types' 
AND column_name = 'doctor_id';

