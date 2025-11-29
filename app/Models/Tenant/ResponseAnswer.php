<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseAnswer extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'response_answers';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id', 'response_id', 'question_id',
        'value_text', 'value_number', 'value_date', 'value_boolean'
    ];

    protected $casts = [
        'value_date' => 'date',
        'value_boolean' => 'boolean',
        'value_number' => 'decimal:2',
    ];

    public $timestamps = false;

    public function response()
    {
        return $this->belongsTo(FormResponse::class, 'response_id');
    }

    public function question()
    {
        return $this->belongsTo(FormQuestion::class);
    }

    /**
     * Acessor para obter o valor formatado da resposta
     */
    public function getValueAttribute()
    {
        if ($this->value_text !== null) {
            // Verifica se é JSON (multi_choice)
            $decoded = json_decode($this->value_text, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return implode(', ', $decoded);
            }
            return $this->value_text;
        }
        if ($this->value_number !== null) {
            return $this->value_number;
        }
        if ($this->value_date !== null) {
            return $this->value_date->format('d/m/Y');
        }
        if ($this->value_boolean !== null) {
            return $this->value_boolean ? 'Sim' : 'Não';
        }
        return null;
    }
}
