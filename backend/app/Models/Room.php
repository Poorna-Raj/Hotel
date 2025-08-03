<?php

namespace App\Models;

use App\Models\User;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    /** @use HasFactory<\Database\Factories\RoomFactory> */
    use HasFactory;

    protected $fillable = [
        "name",
        "number",
        "price",
        "occupancy",
        "bed_type",
        "room_type",
        "hasac",
        "status"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->hasMany(Booking::class);
    }

    public function isAvailable(): bool
    {
        return strtolower($this->status) !== 'unavailable';
    }
    public function canAccommodate(int $occupancy): bool
    {
        return $occupancy <= $this->occpancy;
    }
}
