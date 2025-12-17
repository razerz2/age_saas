<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DoctorBillingPrice extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'doctor_billing_prices';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'doctor_id',
        'specialty_id',
        'reservation_amount',
        'full_appointment_amount',
        'active',
    ];

    protected $casts = [
        'reservation_amount' => 'decimal:2',
        'full_appointment_amount' => 'decimal:2',
        'active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Relacionamento com médico
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Relacionamento com especialidade (opcional)
     */
    public function specialty()
    {
        return $this->belongsTo(MedicalSpecialty::class);
    }

    /**
     * Busca preço por médico e especialidade
     * 
     * @param string $doctorId ID do médico
     * @param string|null $specialtyId ID da especialidade (opcional)
     * @return DoctorBillingPrice|null
     */
    public static function findPrice($doctorId, $specialtyId = null)
    {
        // Se tem especialidade, busca por médico + especialidade
        if ($specialtyId) {
            $price = static::where('doctor_id', $doctorId)
                ->where('specialty_id', $specialtyId)
                ->where('active', true)
                ->first();
            
            if ($price) {
                return $price;
            }
        }
        
        // Se não encontrou ou não tem especialidade, busca apenas por médico
        return static::where('doctor_id', $doctorId)
            ->whereNull('specialty_id')
            ->where('active', true)
            ->first();
    }
}
