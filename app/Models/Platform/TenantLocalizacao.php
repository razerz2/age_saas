<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TenantLocalizacao extends Model
{
    protected $table = 'tenant_localizacoes';
    protected $primaryKey = 'id_localizacao';

    protected $fillable = [
        'tenant_id',
        'endereco',
        'n_endereco',
        'complemento',
        'bairro',
        'cep',
        'pais_id',
        'estado_id',
        'cidade_id',
    ];

    // ⚙️ Configuração para uso com UUID
    protected $casts = [
        'tenant_id' => 'string',
    ];

    // Se futuramente a tabela usar UUID como PK, podemos definir:
    // public $incrementing = false;
    // protected $keyType = 'string';

    /* -------------------------
     |  RELACIONAMENTOS
     ------------------------- */

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id', 'id_pais');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id_estado');
    }

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'cidade_id', 'id_cidade');
    }
}
