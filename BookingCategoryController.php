<?php

namespace App\Http\Controllers;

use App\Models\BookingCategory;
use Illuminate\Http\Request;

class BookingCategoryController extends Controller
{
    public function index()
    {
        return response()->json(BookingCategory::all(), 200);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        if (BookingCategory::where('name', $request->name)->exists()) {
            return response()->json(['message' => 'Kategori dengan nama tersebut sudah ada'], 409);
        }
        $category = BookingCategory::create($request->only('name'));
        
        return response()->json(['message' => 'Kategori berhasil dibuat', 'data' => $category], 201);
    }

    public function update(Request $request, $id)
    {
        $category = BookingCategory::findOrFail($id);
        $request->validate(['name' => 'required|string']);
        if (BookingCategory::where('name', $request->name)->where('id', '!=', $id)->exists()) {
            return response()->json(['message' => 'Kategori dengan nama tersebut sudah ada'], 409);
        }
        $category->update($request->only('name'));
        
        return response()->json(['message' => 'Kategori berhasil diupdate', 'data' => $category], 200);
    }

    public function destroy($id)
    {
        BookingCategory::findOrFail($id)->delete();
        return response()->noContent();
    }

    public function show($id)
    {
        $category = BookingCategory::findOrFail($id);
        return response()->json($category, 200);
    }
}