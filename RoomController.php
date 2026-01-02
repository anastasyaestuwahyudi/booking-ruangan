<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::query()->with(['building', 'csStaff', 'facilities', 'reviews']);

        if ($request->has('building_id')) {
            $query->where('building_id', $request->building_id);
        }

        if ($request->has('min_capacity')) {
            $query->where('capacity', '>=', $request->min_capacity);
        }

        if ($request->has('facilities')) {
            $query->whereHas('facilities', function ($q) use ($request) {
                $q->whereIn('facilities.id', $request->facilities);
            });
        }

        if ($request->has('start_time') && $request->has('end_time')) {
            $start = $request->start_time;
            $end = $request->end_time;

            $query->whereDoesntHave('bookings', function ($q) use ($start, $end) {
                $q->where('status', 'approved')
                  ->where(function ($sub) use ($start, $end) {
                      $sub->whereBetween('start_time', [$start, $end])
                          ->orWhereBetween('end_time', [$start, $end])
                          ->orWhere(function ($w) use ($start, $end) {
                              $w->where('start_time', '<', $start)
                                ->where('end_time', '>', $end);
                          });
                  });
            });
        }

        return response()->json($query->get(), 200);
    }

    public function show($id)
    {
        $room = Room::with(['building', 'csStaff', 'facilities', 'reviews'])->findOrFail($id);

        $avgRoom = $room->reviews()->avg('rating_room');
        $avgCs = $room->reviews()->avg('rating_cs');
        $count = $room->reviews()->count();

        $payload = $room->toArray();
        $payload['average_rating_room'] = $avgRoom ? round($avgRoom, 2) : null;
        $payload['average_rating_cs'] = $avgCs ? round($avgCs, 2) : null;
        $payload['reviews_count'] = $count;

        return response()->json($payload, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'code' => 'required',
            'capacity' => 'required|integer',
            'building_id' => 'required|exists:buildings,id',
            'cs_staff_id' => 'nullable|exists:cs_staff,id',
            'status' => 'in:available,unavailable'
        ]);
        if (Room::where('code', $validated['code'])->exists()) {
            return response()->json(['message' => 'Kode ruangan sudah digunakan'], 409);
        }

        $room = Room::create($validated);
        return response()->json(['message' => 'Ruangan berhasil dibuat', 'data' => $room], 201);
    }

    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();
        return response()->noContent();
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|required',
            'code' => 'sometimes|required',
            'capacity' => 'sometimes|required|integer',
            'building_id' => 'sometimes|required|exists:buildings,id',
            'cs_staff_id' => 'nullable|exists:cs_staff,id',
            'status' => 'in:available,unavailable'
        ]);
        if (isset($validated['code']) && Room::where('code', $validated['code'])->where('id', '!=', $id)->exists()) {
            return response()->json(['message' => 'Kode ruangan sudah digunakan'], 409);
        }

        $room->update($validated);
        return response()->json(['message' => 'Ruangan berhasil diupdate', 'data' => $room], 200);
    }
}