<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'paises';
    protected $primaryKey = 'id_pais';
    public $timestamps = false;

    protected $fillable = [
        'nome',
        'sigla2',
        'sigla3',
        'codigo',
    ];

    public function estados()
    {
        return $this->hasMany(Estado::class, 'pais_id', 'id_pais');
    }
}
