<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSection extends Model
{
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = ['id','form_id','title','position'];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function questions()
    {
        return $this->hasMany(FormQuestion::class, 'section_id');
    }
}