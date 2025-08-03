<?php

namespace App\Models;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'check_in_date',
        'check_out_date',
        'check_in_time',
        'check_out_time',
        'room_id',
        'guest_name',
        'guest_nic',
        'contact_number',
        'occupancy',
        'total',
        'advance',
        'outstanding',
        'nic_front',
        'nic_back',
        'expected_arrival_time',
        'actual_leaving_time',
        'vehicle_number',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
