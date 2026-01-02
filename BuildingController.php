<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function index()
    {
        return response()->json(Building::all(), 200);
    }

    public function show($id)
    {
        $building = Building::findOrFail($id);
        return response()->json($building, 200);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        if (Building::where('name', $request->name)->exists()) {
            return response()->json(['message' => 'Gedung dengan nama tersebut sudah ada'], 409);
        }
        $building = Building::create($request->only('name'));
        
        return response()->json(['message' => 'Gedung berhasil dibuat', 'data' => $building], 201);
    }

    public function update(Request $request, $id)
    {
        $building = Building::findOrFail($id);
        $request->validate(['name' => 'required|string|max:255']);
        if (Building::where('name', $request->name)->where('id', '!=', $id)->exists()) {
            return response()->json(['message' => 'Gedung dengan nama tersebut sudah ada'], 409);
        }
        $building->update($request->only('name'));
        
        return response()->json(['message' => 'Gedung berhasil diupdate', 'data' => $building], 200);
    }

    public function destroy($id)
    {
        Building::findOrFail($id)->delete();
        return response()->noContent();
    }
}