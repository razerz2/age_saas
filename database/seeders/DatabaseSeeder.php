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
         // Base oficial brasileira (IBGE), sem dependencia dos seeders manuais legados.
         $this->call(OfficialIbgeLocationsSeeder::class);
         $this->call(SubscriptionFeaturesSeeder::class);
         // Baseline oficial da Platform (eventos SaaS apenas).
         $this->call(WhatsAppOfficialTemplatesSeeder::class);
         // Baseline oficial tenant (eventos clinicos para mapeamento tenant-aware).
         $this->call(WhatsAppOfficialTenantTemplatesSeeder::class);
         // Baseline interno da Platform para WhatsApp nao oficial.
         $this->call(WhatsAppUnofficialTemplatesSeeder::class);
         // Baseline operacional padrao do Tenant (dominio clinico).
         $this->call(TenantDefaultNotificationTemplatesSeeder::class);
         // Baseline de Email Templates (Platform/Tenant) derivado de templates WhatsApp nao oficial.
         $this->call(NotificationTemplatesSeeder::class);
         //$this->call(TenantsSeeder::class);
    }
}
