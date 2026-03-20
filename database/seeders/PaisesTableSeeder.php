<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaisesTableSeeder extends Seeder
{
    private const BRAZIL_COUNTRY_ID = 31;

    /**
     * Mantido apenas por compatibilidade com FKs legadas (`pais_id`/`country_id`).
     * O sistema e exclusivamente brasileiro, portanto garantimos somente o registro do Brasil.
     */
    public function run(): void
    {
        DB::table('paises')->updateOrInsert(
            ['id_pais' => self::BRAZIL_COUNTRY_ID],
            [
                'nome' => 'Brasil',
                'sigla2' => 'BR',
                'sigla3' => 'BRA',
                'codigo' => '076',
            ]
        );

        // Nao removemos outros registros para evitar efeito colateral em bases antigas.
        DB::statement("SELECT setval(pg_get_serial_sequence('paises', 'id_pais'), GREATEST((SELECT COALESCE(MAX(id_pais), 1) FROM paises), 247), true)");
    }
}
