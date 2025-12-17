<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verifica o tipo real da coluna
        $columnInfo = DB::selectOne("
            SELECT udt_name, data_type
            FROM information_schema.columns 
            WHERE table_name = 'subscriptions' 
            AND column_name = 'status'
        ");

        if ($columnInfo) {
            // Se for um tipo enum customizado (USER-DEFINED)
            if ($columnInfo->data_type === 'USER-DEFINED' && $columnInfo->udt_name !== 'varchar') {
                $typeName = $columnInfo->udt_name;
                
                // Verifica se o valor já existe
                $exists = DB::selectOne("
                    SELECT 1 
                    FROM pg_enum e
                    JOIN pg_type t ON e.enumtypid = t.oid
                    WHERE t.typname = ?
                    AND e.enumlabel = 'recovery_pending'
                ", [$typeName]);

                if (!$exists) {
                    DB::statement("ALTER TYPE {$typeName} ADD VALUE 'recovery_pending'");
                }
            } else {
                // Se for varchar com constraint CHECK, precisamos:
                // 1. Encontrar e remover a constraint antiga
                // 2. Adicionar nova constraint com o novo valor
                
                // Encontra o nome da constraint
                $constraint = DB::selectOne("
                    SELECT constraint_name
                    FROM information_schema.table_constraints
                    WHERE table_name = 'subscriptions'
                    AND constraint_type = 'CHECK'
                    AND constraint_name LIKE '%status%'
                ");

                if ($constraint) {
                    // Remove a constraint antiga
                    DB::statement("ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS {$constraint->constraint_name}");
                }

                // Adiciona nova constraint com todos os valores incluindo recovery_pending
                DB::statement("
                    ALTER TABLE subscriptions 
                    ADD CONSTRAINT subscriptions_status_check 
                    CHECK (status IN ('pending', 'active', 'past_due', 'canceled', 'trialing', 'recovery_pending'))
                ");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove a constraint e recria sem recovery_pending
        DB::statement("ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_status_check");
        DB::statement("
            ALTER TABLE subscriptions 
            ADD CONSTRAINT subscriptions_status_check 
            CHECK (status IN ('pending', 'active', 'past_due', 'canceled', 'trialing'))
        ");
    }
};
