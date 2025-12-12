<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'genders';

    protected $fillable = [
        'name',
        'abbreviation',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public $timestamps = true;

    /**
     * Relacionamento com pacientes
     */
    public function patients()
    {
        return $this->hasMany(Patient::class);
    }
}
