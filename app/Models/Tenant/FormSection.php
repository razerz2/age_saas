<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSection extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'form_sections';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = ['id', 'form_id', 'title', 'position'];

    protected $casts = [
        'position' => 'integer',
    ];

    public $timestamps = false;

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function questions()
    {
        return $this->hasMany(FormQuestion::class, 'section_id');
    }
}