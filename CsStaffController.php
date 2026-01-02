<?php

namespace App\Http\Controllers;

use App\Models\CsStaff;
use Illuminate\Http\Request;

class CsStaffController extends Controller
{
    public function index()
    {
        return response()->json(CsStaff::all(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|string'
        ]);
        if (CsStaff::where('phone_number', $request->phone_number)->exists()) {
            return response()->json(['message' => 'Nomor telepon petugas sudah terdaftar'], 409);
        }
        $staff = CsStaff::create($request->only('name', 'phone_number'));
        
        return response()->json(['message' => 'Petugas berhasil ditambahkan', 'data' => $staff], 201);
    }

    public function update(Request $request, $id)
    {
        $staff = CsStaff::findOrFail($id);
        $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|string'
        ]);
        if (CsStaff::where('phone_number', $request->phone_number)->where('id', '!=', $id)->exists()) {
            return response()->json(['message' => 'Nomor telepon petugas sudah terdaftar'], 409);
        }
        $staff->update($request->only('name', 'phone_number'));
        
        return response()->json(['message' => 'Data petugas berhasil diupdate', 'data' => $staff], 200);
    }

    public function destroy($id)
    {
        CsStaff::findOrFail($id)->delete();
        return response()->noContent();
    }

    public function show($id)
    {
        $staff = CsStaff::findOrFail($id);
        return response()->json($staff, 200);
    }
}