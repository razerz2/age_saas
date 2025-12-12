<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    { 
         $this->call(UsersSeeder::class);
         $this->call(MedicalSpecialtiesCatalogSeeder::class);
         $this->call(PaisesTableSeeder::class);
         $this->call(EstadosTableSeeder::class);
         $this->call(CidadesTableSeeder::class);
         $this->call(SubscriptionFeaturesSeeder::class);
         //$this->call(TenantsSeeder::class);
    }
}
