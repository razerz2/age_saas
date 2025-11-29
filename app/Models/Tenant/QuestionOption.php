<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'question_options';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = ['id', 'question_id', 'label', 'value', 'position'];

    protected $casts = [
        'position' => 'integer',
    ];

    public $timestamps = false;

    public function question()
    {
        return $this->belongsTo(FormQuestion::class);
    }
}
