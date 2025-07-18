<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'phone',                // WA / no HP tamu
        'purpose',
        'unit_id',              // relasi ke unit/bagian
        'id_card_number',       // opsional
        'type',                 // 'perorangan' atau 'badan_usaha'
        'institution',          // instansi (bisa perusahaan / pribadi)
        'institution_address',
        'status_id',      // foreign key ke status_guests
        'photo_path',
    ];

    public function getPhotoUrlAttribute()
    {
        return asset('storage/' . $this->photo_path);
    }
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class,'unit_id');
    }
}
