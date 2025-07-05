<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'institution',
        'purpose',
        'photo_path'
    ];
    public function getPhotoUrlAttribute()
    {
        return asset('storage/' . $this->photo_path);
    }
}
