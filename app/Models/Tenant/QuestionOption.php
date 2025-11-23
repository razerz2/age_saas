<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = ['id','question_id','label','value','position'];

    public function question()
    {
        return $this->belongsTo(FormQuestion::class);
    }
}
