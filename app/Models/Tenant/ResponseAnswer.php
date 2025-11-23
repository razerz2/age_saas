<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseAnswer extends Model
{
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id','response_id','question_id',
        'value_text','value_number','value_date','value_boolean'
    ];

    protected $casts = [
        'value_date' => 'date',
        'value_boolean' => 'boolean',
    ];
}
