<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'building_id',
        'cs_staff_id',
        'name',
        'code',
        'capacity',
        'status'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function csStaff()
    {
        return $this->belongsTo(CsStaff::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'room_facility');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}