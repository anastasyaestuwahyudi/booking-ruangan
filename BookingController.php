<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        if (request()->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden: Hanya admin yang boleh mengakses daftar semua booking'], 403);
        }

        $bookings = Booking::with(['user', 'room', 'category'])->latest()->get();
        return response()->json($bookings, 200);
    }

    public function store(Request $request)
    {
        if ($request->user()->role === 'admin') {
            return response()->json(['message' => 'Forbidden: Hanya peminjam (non-admin) yang boleh membuat booking'], 403);
        }

        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'booking_category_id' => 'required|exists:booking_categories,id',
            'activity_name' => 'required',
            'instance_name' => 'required',
            'participants_count' => 'required|integer',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'surat_pinjam' => 'required|file|mimes:pdf,jpg,png,doc,docx|max:2048',
            'rundown'      => 'required|file|mimes:pdf,jpg,png,doc,docx|max:2048',
        ]);

        $suratPath = $request->file('surat_pinjam')->store('public/surat_pinjam');
        $rundownPath = $request->file('rundown')->store('public/rundown');

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'room_id' => $request->room_id,
            'booking_category_id' => $request->booking_category_id,
            'activity_name' => $request->activity_name,
            'instance_name' => $request->instance_name,
            'participants_count' => $request->participants_count,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'pending',
            'surat_pinjam_path' => $suratPath,
            'rundown_path' => $rundownPath,
        ]);

        return response()->json(['message' => 'Pengajuan berhasil!', 'data' => $booking], 201);
    }

    public function show($id)
    {
        $booking = Booking::with(['user', 'room', 'category'])->findOrFail($id);

        $user = request()->user();
        if ($user->role !== 'admin' && (int)$user->id !== (int)$booking->user_id) {
            return response()->json(['message' => 'Unauthorized: Anda tidak boleh melihat booking orang lain'], 403);
        }

        return response()->json($booking, 200);
    }

    public function myBookings(Request $request, $id)
    {
        if ($request->user()->role !== 'admin' && (int)$request->user()->id !== (int)$id) {
            return response()->json(['message' => 'Unauthorized: Anda tidak boleh melihat booking user lain'], 403);
        }

        if (!User::whereKey($id)->exists()) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $bookings = Booking::with(['room', 'category'])
            ->where('user_id', $id)
            ->latest()
            ->get();

        return response()->json($bookings, 200);
    }

    public function updateStatus(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden: Hanya admin yang boleh mengubah status booking'], 403);
        }

        $booking = Booking::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'reason' => 'required_if:status,rejected'
        ]);

        if ($request->status === 'approved') {
            $sudahAdaYangApproved = Booking::where('room_id', $booking->room_id)
                ->where('status', 'approved')
                ->where('id', '!=', $id)
                ->where(function ($query) use ($booking) {
                    $query->where('start_time', '<', $booking->end_time)
                          ->where('end_time', '>', $booking->start_time);
                })
                ->exists();

            if ($sudahAdaYangApproved) {
                return response()->json([
                    'message' => 'Gagal Approve: Ruangan sudah resmi dipakai kegiatan lain di jam tersebut!',
                ], 409);
            }
        }

        $booking->update([
            'status' => $request->status,
            'reason_for_rejection' => $request->status == 'rejected' ? $request->reason : null
        ]);

        if ($request->status === 'approved') {
            $affectedRows = Booking::where('room_id', $booking->room_id)
                ->where('status', 'pending') // Hanya yang masih pending
                ->where('id', '!=', $id)     // Jangan tolak diri sendiri
                ->where(function ($query) use ($booking) {
                    $query->where('start_time', '<', $booking->end_time)
                          ->where('end_time', '>', $booking->start_time);
                })
                ->update([
                    'status' => 'rejected',
                    'reason_for_rejection' => 'Sistem Otomatis: Maaf, ruangan telah disetujui untuk kegiatan lain di waktu yang sama.'
                ]);
        }

        return response()->json([
            'message' => 'Status berhasil diperbarui.',
            'data' => $booking,
            'auto_rejected_count' => $request->status === 'approved' ? ($affectedRows ?? 0) : 0
        ], 200);
    }

    public function uploadDisposisi(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden: Hanya admin yang boleh mengunggah disposisi'], 403);
        }

        if ($booking->status !== 'approved') {
            return response()->json(['message' => 'Gagal: Booking belum disetujui!'], 403);
        }

        $request->validate([
            'disposisi' => 'required|file|mimes:pdf,jpg,png|max:2048'
        ]);

        $path = $request->file('disposisi')->store('public/disposisi');
        $booking->update(['disposisi_path' => $path]);

        return response()->json(['message' => 'Surat disposisi berhasil diupload', 'data' => $booking], 200);
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $user = request()->user();
        if ($user->role !== 'admin' && (int)$user->id !== (int)$booking->user_id) {
            return response()->json(['message' => 'Unauthorized: Anda tidak boleh menghapus booking orang lain'], 403);
        }
        
        if($booking->status !== 'pending') {
             return response()->json(['message' => 'Hanya booking pending yang bisa dibatalkan'], 403);
        }

        $booking->delete();
        return response()->noContent();
    }
}