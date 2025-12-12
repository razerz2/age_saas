<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAddress extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'patient_addresses';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'patient_id',
        'postal_code',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
    ];

    public $timestamps = true;

    /**
     * Relacionamento com paciente
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
