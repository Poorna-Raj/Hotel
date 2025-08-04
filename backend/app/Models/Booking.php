<?php

namespace App\Models;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

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

    public static function getClashingBookingId(array $data, ?int $ignoreBookingId = null): ?int
    {
        $potentialClashes = self::where('room_id', $data['room_id'])
            ->where('status', '!=', ['Cancelled', 'Checked Out'])
            ->when($ignoreBookingId, function ($query) use ($ignoreBookingId) {
                $query->where('id', '!=', $ignoreBookingId);
            })
            ->where(function ($query) use ($data) {
                $query->whereBetween('check_in_date', [$data['check_in_date'], $data['check_out_date']])
                    ->orWhereBetween('check_out_date', [$data['check_in_date'], $data['check_out_date']])
                    ->orWhere(function ($sub) use ($data) {
                        $sub->where('check_in_date', '<=', $data['check_in_date'])
                            ->where('check_out_date', '>=', $data['check_out_date']);
                    });
            })
            ->get();

        $start1 = Carbon::parse($data['check_in_date'] . ' ' . $data['expected_arrival_time']);
        $end1 = Carbon::parse($data['check_out_date'] . ' ' . $data['actual_leaving_time']);

        foreach ($potentialClashes as $existing) {
            $start2 = Carbon::parse($existing->check_in_date . ' ' . $existing->expected_arrival_time)->subMinutes(30);
            $end2 = Carbon::parse($existing->check_out_date . ' ' . $existing->actual_leaving_time)->addMinutes(30);

            if ($start1->lt($end2) && $end1->gt($start2)) {
                return $existing->id;
            }
        }

        return null; // No clash found
    }

}
