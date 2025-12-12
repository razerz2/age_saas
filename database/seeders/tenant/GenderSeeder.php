<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant\Gender;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genders = [
            ['name' => 'Masculino', 'abbreviation' => 'M', 'order' => 1, 'is_active' => true],
            ['name' => 'Feminino', 'abbreviation' => 'F', 'order' => 2, 'is_active' => true],
        ];

        foreach ($genders as $gender) {
            Gender::firstOrCreate(
                ['abbreviation' => $gender['abbreviation']],
                $gender
            );
        }
    }
}
