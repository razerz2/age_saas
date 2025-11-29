<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormQuestion extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'form_questions';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id', 'form_id', 'section_id', 'label',
        'help_text', 'type', 'required', 'position'
    ];

    protected $casts = [
        'required' => 'boolean',
        'position' => 'integer',
    ];

    public $timestamps = false;

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function section()
    {
        return $this->belongsTo(FormSection::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id');
    }
}
