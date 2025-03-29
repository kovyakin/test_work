<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Bookings;
use App\Models\Resource;
use Illuminate\Http\Request;

class BookingController extends Controller
{

    public function index(int $resource_id)
    {
        $resource = Resource::find($resource_id);

        if (!$resource) {
            return response()->json(['message' => 'Not Found'], 404);
        }
        return BookingResource::collection(Resource::find($resource_id)->bookings);
    }

    public function store(BookingRequest $request)
    {
        Bookings::query()->create([
            'user_id' => $request->user_id,
            'resource_id' => $request->resource_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json(['message' => 'Booking created'], 201);
    }

    public function destroy(Request $request, $id)
    {
        $booking = Bookings::query()->find($id);

        if ($booking) {
            $booking->delete();
            return response()->json(['message' => 'Booking deleted']);
        }

        return response()->json(['message' => 'Booking not found']);
    }
}
