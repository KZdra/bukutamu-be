<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyAnswer extends Model
{
    use HasFactory;
    protected $fillable = ['guest_id', 'question_id', 'answer_text'];

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class);
    }
}
