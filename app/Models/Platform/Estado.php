<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $table = 'estados';
    protected $primaryKey = 'id_estado';
    public $timestamps = false;

    protected $fillable = [
        'uf',
        'nome_estado',
        'pais_id',
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id', 'id_pais');
    }

    public function cidades()
    {
        return $this->hasMany(Cidade::class, 'estado_id', 'id_estado');
    }
}
