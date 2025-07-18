<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;
    protected $table = 'status_guests';
    protected $fillable = [
        'name',
    ];
    public function guests()
    {
        return $this->hasMany(Guest::class);
    }    
}
