<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function index()
    {
        return response()->json(Facility::all(), 200);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        if (Facility::where('name', $request->name)->exists()) {
            return response()->json(['message' => 'Fasilitas dengan nama tersebut sudah ada'], 409);
        }
        $facility = Facility::create($request->only('name'));
        
        return response()->json(['message' => 'Fasilitas berhasil dibuat', 'data' => $facility], 201);
    }

    public function update(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);
        $request->validate(['name' => 'required|string|max:255']);
        if (Facility::where('name', $request->name)->where('id', '!=', $id)->exists()) {
            return response()->json(['message' => 'Fasilitas dengan nama tersebut sudah ada'], 409);
        }
        $facility->update($request->only('name'));
        
        return response()->json(['message' => 'Fasilitas berhasil diupdate', 'data' => $facility], 200);
    }

    public function show($id)
    {
        $facility = Facility::findOrFail($id);
        return response()->json($facility, 200);
    }

    public function destroy($id)
    {
        Facility::findOrFail($id)->delete();
        return response()->noContent();
    }
}