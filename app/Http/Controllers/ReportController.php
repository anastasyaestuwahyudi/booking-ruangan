<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        if ($request->user()->role === 'admin') {
            return response()->json(['message' => 'Forbidden: Hanya peminjam (non-admin) yang boleh membuat laporan'], 403);
        }
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'title' => 'required',
            'description' => 'required',
            'proof' => 'nullable|image|max:2048'
        ]);

        $path = null;
        if ($request->hasFile('proof')) {
            $path = $request->file('proof')->store('public/reports');
        }

        $report = Report::create([
            'user_id' => $request->user()->id,
            'room_id' => $request->room_id,
            'title' => $request->title,
            'description' => $request->description,
            'proof_path' => $path,
            'status' => 'pending'
        ]);
        return response()->json([
            'message' => 'Laporan berhasil dibuat',
            'data' => $report
        ], 201);
    }

    public function index()
    {
        if (request()->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden: Hanya admin yang boleh mengakses daftar semua laporan'], 403);
        }

        return response()->json(Report::with(['user', 'room'])->latest()->get());
    }

    public function myReports(Request $request, $id)
    {
        if ($request->user()->role !== 'admin' && (int)$request->user()->id !== (int)$id) {
            return response()->json(['message' => 'Unauthorized: Anda tidak boleh melihat laporan user lain'], 403);
        }

        if (!User::whereKey($id)->exists()) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $reports = Report::with(['room'])
                         ->where('user_id', $id)
                         ->latest()
                         ->get();
        return response()->json($reports, 200);
    }

    public function show($id)
    {
        $report = Report::with(['user', 'room'])->findOrFail($id);
        $user = request()->user();
        if ($user->role !== 'admin' && (int)$user->id !== (int)$report->user_id) {
            return response()->json(['message' => 'Unauthorized: Anda tidak boleh melihat laporan orang lain'], 403);
        }
        return response()->json($report);
    }

    public function updateStatus(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden: Hanya admin yang boleh mengubah status laporan'], 403);
        }

        $report = Report::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved'
        ]);

        $report->update(['status' => $request->status]);
        return response()->json([
            'message' => 'Status laporan diperbarui',
            'data' => $report
        ], 200);
    }
}