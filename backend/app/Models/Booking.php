<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

    public static function getMonthlyStatusCount($month, $year)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        return Booking::select('status', DB::raw('COUNT(*) as total'))
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->groupBy('status')
            ->get();
    }
    public static function getLastFiveMonthsCount()
    {
        $startDate = Carbon::now()->subMonths(4)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        return self::select(
            DB::raw("strftime('%Y', check_in_date) as year"),
            DB::raw("strftime('%m', check_in_date) as month_number"),
            DB::raw("strftime('%Y', check_in_date) || '-' || strftime('%m', check_in_date) as month_key"),
            DB::raw("CASE strftime('%m', check_in_date)
                        WHEN '01' THEN 'January'
                        WHEN '02' THEN 'February'
                        WHEN '03' THEN 'March'
                        WHEN '04' THEN 'April'
                        WHEN '05' THEN 'May'
                        WHEN '06' THEN 'June'
                        WHEN '07' THEN 'July'
                        WHEN '08' THEN 'August'
                        WHEN '09' THEN 'September'
                        WHEN '10' THEN 'October'
                        WHEN '11' THEN 'November'
                        WHEN '12' THEN 'December'
                    END as month_name"),
            DB::raw('COUNT(*) as total')
        )
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->groupBy(DB::raw("strftime('%Y', check_in_date)"), DB::raw("strftime('%m', check_in_date)"))
            ->orderBy(DB::raw("strftime('%Y', check_in_date)"), 'asc')
            ->orderBy(DB::raw("strftime('%m', check_in_date)"), 'asc')
            ->get();
    }

    public static function getTodayCheckIns()
    {
        return self::whereDate('check_in_date', now()->toDateString())
            ->where('status', 'Booked')
            ->get();
    }

    public static function getOngoing()
    {
        return self::where('status', 'Checked In')->get();
    }

    public static function getTodayCheckOuts()
    {
        return self::whereDate('check_out_date', Carbon::today())
            ->where('status', 'Checked In')
            ->get();
    }
}
