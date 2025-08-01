<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class RoomController extends Controller implements HasMiddleware
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
            $rooms = Room::all();

            return response()->json([
                "success" => true,
                "message" => "Rooms retrieved successfully",
                "data" => $rooms
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
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
            "name" => "required|string|max:100|unique:rooms",
            "number" => "required|integer|unique:rooms",
            "price" => "required|numeric",
            "occupancy" => "required|integer|min:1",
            "bed_type" => "required|string",
            "room_type" => "required|string",
            "hasac" => "required|boolean",
            "status" => "required|string"
        ]);
        try {
            $request->user()->rooms()->create($feilds);
            return response()->json([
                "success" => true,
                "message" => "Room Created Successfully"
            ], 201);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Failed to create room",
                "error" => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        try {
            return response()->json([
                "success" => true,
                "message" => "Room Retrived Successfully",
                "data" => $room
            ]);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Failed to retrive room",
                "error" => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        $feilds = $request->validate([
            "name" => "required|string|max:100|unique:rooms,name," . $room->id,
            "number" => "required|integer|unique:rooms,number," . $room->id,
            "price" => "required|numeric",
            "occupancy" => "required|integer|min:1",
            "bed_type" => "required|string",
            "room_type" => "required|string",
            "hasac" => "required|boolean",
            "status" => "required|string"
        ]);
        try {
            $room->update($feilds);
            return response()->json([
                "success" => true,
                "message" => "Room Updated Successfully"
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Failed to update room",
                "error" => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        try {
            $room->delete();
            return response()->json([
                "success" => true,
                "message" => "Room Deleted Successfully"
            ]);
        } catch (Exception $ex) {
            return response()->json([
                "success" => false,
                "message" => "Failed to delete room",
                "error" => $ex->getMessage()
            ], 500);
        }
    }
}
