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
         // Baseline oficial da Platform (eventos SaaS apenas).
         $this->call(WhatsAppOfficialTemplatesSeeder::class);
         // Baseline operacional padrao do Tenant (dominio clinico).
         $this->call(TenantDefaultNotificationTemplatesSeeder::class);
         //$this->call(TenantsSeeder::class);
    }
}
