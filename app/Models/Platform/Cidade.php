<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'cidades';
    protected $primaryKey = 'id_cidade';
    public $timestamps = false;

    protected $fillable = [
        'estado_id',
        'uf',
        'nome_cidade',
    ];

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id_estado');
    }
}
