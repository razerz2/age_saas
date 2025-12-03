-- Migration para adicionar suporte ao Apple Calendar
-- Execute este script no banco de dados do tenant

-- 1. Adicionar campo apple_event_id na tabela appointments (se não existir)
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'appointments' 
        AND column_name = 'apple_event_id'
    ) THEN
        ALTER TABLE appointments ADD COLUMN apple_event_id VARCHAR(255) NULL;
    END IF;
END $$;

-- 2. Criar tabela apple_calendar_tokens (se não existir)
CREATE TABLE IF NOT EXISTS apple_calendar_tokens (
    id UUID PRIMARY KEY,
    doctor_id UUID NOT NULL UNIQUE,
    username VARCHAR(255) NOT NULL,
    password TEXT NOT NULL,
    server_url VARCHAR(255) NOT NULL DEFAULT 'https://caldav.icloud.com',
    calendar_url VARCHAR(255) NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    CONSTRAINT apple_calendar_tokens_doctor_id_foreign 
        FOREIGN KEY (doctor_id) 
        REFERENCES doctors(id) 
        ON DELETE CASCADE
);

-- 3. Registrar as migrations na tabela migrations
INSERT INTO migrations (migration, batch) 
VALUES 
    ('2025_12_03_084550_add_apple_calendar_fields_to_appointments_table', 
     (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations)),
    ('2025_12_03_084556_create_apple_calendar_tokens_table', 
     (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations))
ON CONFLICT (migration) DO NOTHING;

