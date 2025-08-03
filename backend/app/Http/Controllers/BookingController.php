<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class BookingController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware("auth:sanctum", except: ["index", "show"])
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


    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }
}
