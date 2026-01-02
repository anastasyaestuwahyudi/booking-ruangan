<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsStaff extends Model
{
    use HasFactory;
    protected $table = 'cs_staff'; 
    protected $fillable = ['name', 'phone_number'];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}