<?php

namespace Database\Factories\Tenant;

use App\Models\Tenant\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        $faker = fake('pt_BR');

        return [
            'id' => (string) Str::uuid(),
            'full_name' => $faker->name(),
            'cpf' => $faker->unique()->cpf(true),
            'birth_date' => $faker->dateTimeBetween('-90 years', '-18 years')->format('Y-m-d'),
            'email' => $faker->boolean(80) ? $faker->unique()->safeEmail() : null,
            'phone' => $this->generateBrazilianPhone(),
            'asaas_customer_id' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function generateBrazilianPhone(): string
    {
        $ddd = str_pad((string) random_int(11, 99), 2, '0', STR_PAD_LEFT);
        $subscriber = '9' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

        return $ddd . $subscriber;
    }
}
