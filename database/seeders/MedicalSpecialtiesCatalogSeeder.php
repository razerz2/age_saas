<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Platform\MedicalSpecialtyCatalog;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MedicalSpecialtiesCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
             // 🔹 Especialidades Médicas
            ['name' => 'Alergia e Imunologia', 'code' => '2251-05', 'type' => 'medical_specialty'],
            ['name' => 'Anestesiologia', 'code' => '2251-10', 'type' => 'medical_specialty'],
            ['name' => 'Angiologia', 'code' => '2251-15', 'type' => 'medical_specialty'],
            ['name' => 'Cancerologia', 'code' => '2251-20', 'type' => 'medical_specialty'],
            ['name' => 'Cardiologia', 'code' => '2251-25', 'type' => 'medical_specialty'],
            ['name' => 'Cirurgia Cardiovascular', 'code' => '2251-30', 'type' => 'medical_specialty'],
            ['name' => 'Cirurgia da Mão', 'code' => '2251-35', 'type' => 'medical_specialty'],
            ['name' => 'Cirurgia de Cabeça e Pescoço', 'code' => '2251-40', 'type' => 'medical_specialty'],
            ['name' => 'Cirurgia do Aparelho Digestivo', 'code' => '2251-45', 'type' => 'medical_specialty'],
            ['name' => 'Cirurgia Geral', 'code' => '2251-50', 'type' => 'medical_specialty'],
            ['name' => 'Cirurgia Pediátrica', 'code' => '2251-55', 'type' => 'medical_specialty'],
            ['name' => 'Cirurgia Plástica', 'code' => '2251-60', 'type' => 'medical_specialty'],
            ['name' => 'Cirurgia Torácica', 'code' => '2251-65', 'type' => 'medical_specialty'],
            ['name' => 'Cirurgia Vascular', 'code' => '2251-70', 'type' => 'medical_specialty'],
            ['name' => 'Clínica Médica', 'code' => '2251-75', 'type' => 'medical_specialty'],
            ['name' => 'Coloproctologia', 'code' => '2251-80', 'type' => 'medical_specialty'],
            ['name' => 'Dermatologia', 'code' => '2251-85', 'type' => 'medical_specialty'],
            ['name' => 'Endocrinologia e Metabologia', 'code' => '2251-90', 'type' => 'medical_specialty'], 
            ['name' => 'Endoscopia', 'code' => '2251-95', 'type' => 'medical_specialty'],
            ['name' => 'Gastroenterologia', 'code' => '2252-00', 'type' => 'medical_specialty'],
            ['name' => 'Genética Médica', 'code' => '2252-05', 'type' => 'medical_specialty'],
            ['name' => 'Geriatria', 'code' => '2252-10', 'type' => 'medical_specialty'],
            ['name' => 'Ginecologia e Obstetrícia', 'code' => '2252-15', 'type' => 'medical_specialty'],
            ['name' => 'Hematologia e Hemoterapia', 'code' => '2252-20', 'type' => 'medical_specialty'],
            ['name' => 'Homeopatia', 'code' => '2252-25', 'type' => 'medical_specialty'],
            ['name' => 'Infectologia', 'code' => '2252-30', 'type' => 'medical_specialty'],
            ['name' => 'Mastologia', 'code' => '2252-35', 'type' => 'medical_specialty'],
            ['name' => 'Medicina de Família e Comunidade', 'code' => '2252-40', 'type' => 'medical_specialty'],
            ['name' => 'Medicina do Trabalho', 'code' => '2252-45', 'type' => 'medical_specialty'],
            ['name' => 'Medicina de Tráfego', 'code' => '2252-50', 'type' => 'medical_specialty'],
            ['name' => 'Medicina Esportiva', 'code' => '2252-55', 'type' => 'medical_specialty'],
            ['name' => 'Medicina Física e Reabilitação', 'code' => '2252-60', 'type' => 'medical_specialty'],
            ['name' => 'Medicina Intensiva', 'code' => '2252-65', 'type' => 'medical_specialty'],
            ['name' => 'Medicina Legal e Perícia Médica', 'code' => '2252-70', 'type' => 'medical_specialty'],
            ['name' => 'Medicina Nuclear', 'code' => '2252-75', 'type' => 'medical_specialty'],
            ['name' => 'Medicina Preventiva e Social', 'code' => '2252-80', 'type' => 'medical_specialty'],
            ['name' => 'Nefrologia', 'code' => '2252-85', 'type' => 'medical_specialty'],
            ['name' => 'Neurocirurgia', 'code' => '2252-90', 'type' => 'medical_specialty'],
            ['name' => 'Neurologia', 'code' => '2252-95', 'type' => 'medical_specialty'],
            ['name' => 'Nutrologia', 'code' => '2253-00', 'type' => 'medical_specialty'],
            ['name' => 'Oftalmologia', 'code' => '2253-05', 'type' => 'medical_specialty'],
            ['name' => 'Oncologia Clínica', 'code' => '2253-10', 'type' => 'medical_specialty'],
            ['name' => 'Ortopedia e Traumatologia', 'code' => '2253-15', 'type' => 'medical_specialty'],
            ['name' => 'Otorrinolaringologia', 'code' => '2253-20', 'type' => 'medical_specialty'],
            ['name' => 'Patologia', 'code' => '2253-25', 'type' => 'medical_specialty'],
            ['name' => 'Patologia Clínica / Medicina Laboratorial', 'code' => '2253-30', 'type' => 'medical_specialty'],
            ['name' => 'Pediatria', 'code' => '2253-35', 'type' => 'medical_specialty'],
            ['name' => 'Pneumologia', 'code' => '2253-40', 'type' => 'medical_specialty'],
            ['name' => 'Psiquiatria', 'code' => '2253-45', 'type' => 'medical_specialty'],
            ['name' => 'Radiologia e Diagnóstico por Imagem', 'code' => '2253-50', 'type' => 'medical_specialty'],
            ['name' => 'Radioterapia', 'code' => '2253-55', 'type' => 'medical_specialty'],
            ['name' => 'Reumatologia', 'code' => '2253-60', 'type' => 'medical_specialty'],
            ['name' => 'Urologia', 'code' => '2253-65', 'type' => 'medical_specialty'],

             // 🔹 Profissões da área da saúde
            ['name' => 'Psicologia', 'code' => '2515-10', 'type' => 'health_profession'],
            ['name' => 'Fisioterapia', 'code' => '2236-05', 'type' => 'health_profession'],
            ['name' => 'Nutrição', 'code' => '2237-10', 'type' => 'health_profession'],
            ['name' => 'Enfermagem', 'code' => '2235-05', 'type' => 'health_profession'],
            ['name' => 'Farmácia', 'code' => '2234-05', 'type' => 'health_profession'],
            ['name' => 'Fonoaudiologia', 'code' => '2238-05', 'type' => 'health_profession'],
            ['name' => 'Terapia Ocupacional', 'code' => '2239-05', 'type' => 'health_profession'],
            ['name' => 'Educação Física', 'code' => '2241-10', 'type' => 'health_profession'],
            ['name' => 'Odontologia', 'code' => '2232-05', 'type' => 'health_profession'],
            ['name' => 'Biomedicina', 'code' => '2211-05', 'type' => 'health_profession'],
        ];

        foreach ($specialties as $specialty) {
            MedicalSpecialtyCatalog::updateOrCreate(
                ['name' => $specialty['name']],
                [
                    'id'         => (string) Str::uuid(),
                    'code'       => $specialty['code'] ?? null,
                    'type'       => $specialty['type'] ?? 'medical_specialty',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}
