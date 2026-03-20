<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'estados';
    protected $primaryKey = 'id_estado';
    public $timestamps = false;

    protected $fillable = [
        'uf',
        'nome_estado',
        'pais_id',
        'ibge_id',
    ];

    public function scopeByUf($query, string $uf)
    {
        return $query->whereRaw('UPPER(uf) = ?', [mb_strtoupper(trim($uf))]);
    }

    public function scopeByIbgeId($query, int|string $ibgeId)
    {
        return $query->where('ibge_id', (int) $ibgeId);
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id', 'id_pais');
    }

    public function cidades()
    {
        return $this->hasMany(Cidade::class, 'estado_id', 'id_estado');
    }
}
