<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controllers\HasMiddleware;

class BookingController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware("auth:sanctum", except: ["index", "show", "getBookingStatusForMonth", "monthlyTrend", "currentBookings", "onGoingBookings", "checkedOutBookings"])
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $bookings = Booking::all();

            return response()->json([
                "success" => true,
                "message" => "Bookings Retrived Successfully",
                "data" => $bookings
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Failed to fetch Bookings",
                "error" => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $feilds = $request->validate([
                "guest_name" => "required",
                "guest_nic" => "required",
                "contact_number" => "required",
                "occupancy" => "required|numeric",
                "check_in_date" => "required|date",
                "check_out_date" => "required|date|after:check_in_date",
                "room_id" => "required|exists:rooms,id",
                "total" => "required|numeric",
                "advance" => "nullable|numeric",
                "outstanding" => "required|numeric",
                "nic_front" => "nullable|image|mimes:jpeg,png,jpg|max:2048",
                "nic_back" => "nullable|image|mimes:jpeg,png,jpg|max:2048",
                "vehicle_number" => "nullable",
                "check_in_time" => "nullable|date_format:H:i",
                "check_out_time" => "nullable|date_format:H:i",
                "expected_arrival_time" => "required|date_format:H:i",
                "actual_leaving_time" => "required|date_format:H:i",
                "status" => "required"
            ]);
            $room = Room::find($feilds["room_id"]);
            if (!$room->isAvailable()) {
                return response()->json([
                    "success" => false,
                    "message" => "Selected room isn't available for booking"
                ], 422);
            }
            if (!$room) {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid Room"
                ], 404);
            }
            if (!($room->canAccommodate($feilds["occupancy"]))) {
                return response()->json([
                    "success" => false,
                    "message" => "Booking occupancy exceeds the room capacity"
                ], 422);
            }

            $clashingID = Booking::getClashingBookingId($feilds);
            if ($clashingID !== null) {
                return response()->json([
                    "success" => false,
                    "message" => "Booking clashes with existing booking ID: " . $clashingID,
                ], 422);
            }

            if ($request->hasFile("nic_front") && $request->file('nic_front')->isValid()) {
                $nicFrontPath = $request->file('nic_front')->store("nic_front", "public");
                $feilds["nic_front"] = $nicFrontPath;
            }
            if ($request->hasFile("nic_back") && $request->file('nic_back')->isValid()) {
                $nicBackPath = $request->file('nic_back')->store("nic_back", "public");
                $feilds["nic_back"] = $nicBackPath;
            }

            $request->user()->booking()->create($feilds);
            return response()->json([
                "success" => true,
                "message" => "Room Created Successfully"
            ], 200);
        } catch (ValidationException $e) {
            // Return validation errors with 422 status (not 500)
            return response()->json([
                "success" => false,
                "error" => $e->errors()
            ], 422);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Failed to create booking",
                "error" => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        try {
            return response()->json([
                "success" => true,
                "message" => "Booking retrived Successfully",
                "data" => $booking
            ]);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Failed to retrive booking",
                "error" => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        $feilds = $request->validate([
            "guest_name" => "required",
            "guest_nic" => "required",
            "contact_number" => "required",
            "occupancy" => "required|numeric",
            "check_in_date" => "required|date",
            "check_out_date" => "required|date|after:check_in_date",
            "room_id" => "required|exists:rooms,id",
            "total" => "required|numeric",
            "advance" => "nullable|numeric",
            "outstanding" => "required|numeric",
            "nic_front" => "nullable|image|mimes:jpeg,png,jpg|max:2048",
            "nic_back" => "nullable|image|mimes:jpeg,png,jpg|max:2048",
            "vehicle_number" => "nullable",
            "check_in_time" => "nullable|date_format:H:i",
            "check_out_time" => "nullable|date_format:H:i",
            "expected_arrival_time" => "required|date_format:H:i",
            "actual_leaving_time" => "required|date_format:H:i",
            "status" => "required"
        ]);

        try {
            $room = Room::find($feilds["room_id"]);
            if (!$room->isAvailable()) {
                return response()->json([
                    "success" => false,
                    "message" => "Selected room isn't available for booking"
                ], 422);
            }
            if (!$room) {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid Room"
                ], 404);
            }
            if (!($room->canAccommodate($feilds["occupancy"]))) {
                return response()->json([
                    "success" => false,
                    "message" => "Booking occupancy exceeds the room capacity"
                ], 422);
            }

            $clashingID = Booking::getClashingBookingId($feilds, $booking->id);
            if ($clashingID !== null) {
                return response()->json([
                    "success" => false,
                    "message" => "Booking clashes with existing booking ID: " . $clashingID,
                ], 422);
            }

            if ($request->hasFile("nic_front")) {
                if ($booking->nic_front && Storage::disk('public')->exists($booking->nic_front)) {
                    Storage::disk('public')->delete($booking->nic_front);
                }
                $nicFrontPath = $request->file('nic_front')->store("nic_front", "public");
                $feilds["nic_front"] = $nicFrontPath;
            }
            if ($request->hasFile("nic_back")) {
                if ($booking->nic_back && Storage::disk('public')->exists($booking->nic_back)) {
                    Storage::disk('public')->delete($booking->nic_back);
                }
                $nicBackPath = $request->file('nic_back')->store("nic_back", "public");
                $feilds["nic_back"] = $nicBackPath;
            }

            $booking->update($feilds);
            return response()->json([
                "success" => true,
                "message" => "Booking Updated Successfully"
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Failed to update booking",
                "error" => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        try {
            $booking->delete();
            return response()->json([
                "success" => true,
                "message" => "Room Deleted Successfully"
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Failed to delete room",
                "error" => $ex
            ], 500);
        }
    }

    public function roomBookingStats(Request $request)
    {
        // Optional: allow month/year filter
        $month = $request->query('month', Carbon::now()->month);
        $year = $request->query('year', Carbon::now()->year);

        // Get bookings for the given month
        $stats = DB::table('bookings')
            ->select('room_id', DB::raw('COUNT(*) as total_bookings'))
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('room_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function getBookingStatusForMonth(Request $request)
    {
        try {
            $month = $request->query('month', Carbon::now()->month);
            $year = $request->query('year', Carbon::now()->year);
            $counts = Booking::getMonthlyStatusCount($month, $year);
            return response()->json([
                "success" => true,
                "message" => "Data Retrived Success",
                "data" => $counts
            ]);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Something went wrong!",
                "error" => $ex->getMessage()
            ]);
        }
    }

    public function monthlyTrend()
    {
        try {
            $data = Booking::getLastFiveMonthsCount();
            return response()->json([
                'success' => true,
                'message' => 'Data Retrieved Success',
                'data' => $data
            ]);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "error" => $ex->getMessage()
            ]);
        }
    }

    public function currentBookings()
    {
        try {
            $todayCheckIns = Booking::getTodayCheckIns();
            return response()->json([
                "success" => true,
                "message" => "Bookings retrived success",
                "data" => $todayCheckIns
            ]);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "error" => $ex->getMessage()
            ]);
        }
    }

    public function onGoingBookings()
    {
        try {
            $todayCheckIns = Booking::getOngoing();
            return response()->json([
                "success" => true,
                "message" => "Bookings retrived success",
                "data" => $todayCheckIns
            ]);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "error" => $ex->getMessage()
            ]);
        }
    }

    public function checkedOutBookings()
    {
        try {
            $todayCheckOuts = Booking::getTodayCheckOuts();
            return response()->json([
                "success" => true,
                "message" => "Bookings retrived success",
                "data" => $todayCheckOuts
            ]);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "error" => $ex->getMessage()
            ]);
        }
    }
}
