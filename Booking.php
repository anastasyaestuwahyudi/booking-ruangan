<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'booking_category_id',
        'activity_name',
        'instance_name',
        'participants_count',
        'start_time',
        'end_time',
        'status',
        'reason_for_rejection',
        'surat_pinjam_path',
        'rundown_path',
        'disposisi_path',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function category()
    {
        return $this->belongsTo(BookingCategory::class, 'booking_category_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}