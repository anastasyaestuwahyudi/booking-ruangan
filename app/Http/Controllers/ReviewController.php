<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        if (request()->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden: Hanya admin yang boleh mengakses daftar semua ulasan'], 403);
        }

        return response()->json(Review::with(['user', 'room'])->latest()->get(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating_room' => 'required|integer|min:1|max:5',
            'comment_room' => 'nullable|string|max:1000',
            'rating_cs' => 'required|integer|min:1|max:5',
            'comment_cs' => 'nullable|string|max:1000',
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized: Ini bukan peminjaman Anda'], 403);
        }

        if ($booking->status !== 'approved') {
            return response()->json(['message' => 'Hanya peminjaman yang disetujui yang bisa diulas'], 403);
        }

        if (Review::where('booking_id', $booking->id)->exists()) {
            return response()->json(['message' => 'Anda sudah mengulas peminjaman ini'], 409);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'booking_id' => $booking->id,
            'room_id' => $booking->room_id,
            'rating_room' => $request->rating_room,
            'comment_room' => $request->comment_room,
            'rating_cs' => $request->rating_cs,
            'comment_cs' => $request->comment_cs,
        ]);

        return response()->json(['message' => 'Ulasan berhasil dikirim', 'data' => $review], 201);
    }

    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $user = $request->user();
        if ((int)$user->id !== (int)$review->user_id) {
            return response()->json(['message' => 'Forbidden: Hanya pemilik ulasan yang boleh mengubah'], 403);
        }

        $request->validate([
            'rating_room' => 'sometimes|integer|min:1|max:5',
            'comment_room' => 'sometimes|nullable|string|max:1000',
            'rating_cs' => 'sometimes|integer|min:1|max:5',
            'comment_cs' => 'sometimes|nullable|string|max:1000',
        ]);

        $fields = [
            'rating_room', 'comment_room', 'rating_cs', 'comment_cs'
        ];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $review->{$field} = $request->input($field);
            }
        }
        $review->save();

        return response()->json(['message' => 'Ulasan berhasil diperbarui', 'data' => $review], 200);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        $user = request()->user();
        if ($user->role !== 'admin' && (int)$user->id !== (int)$review->user_id) {
            return response()->json(['message' => 'Forbidden: Anda tidak boleh menghapus ulasan orang lain'], 403);
        }

        $review->delete();
        return response()->noContent();
    }

    public function show($id)
    {
        $review = Review::with(['user', 'room'])->findOrFail($id);
        $user = request()->user();
        if ($user->role !== 'admin' && (int)$user->id !== (int)$review->user_id) {
            return response()->json(['message' => 'Unauthorized: Anda tidak boleh melihat ulasan orang lain'], 403);
        }
        return response()->json($review, 200);
    }

    public function myReviews(Request $request, $id)
    {
        if ($request->user()->role !== 'admin' && (int)$request->user()->id !== (int)$id) {
            return response()->json(['message' => 'Unauthorized: Anda tidak boleh melihat ulasan user lain'], 403);
        }

        if (!User::whereKey($id)->exists()) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $reviews = Review::with(['room'])
                         ->where('user_id', $id)
                         ->latest()
                         ->get();
        return response()->json($reviews, 200);
    }

    public function roomReviews($id)
    {
        if (!\App\Models\Room::find($id)) {
            abort(404);
        }

        $reviews = Review::with(['user'])
                         ->where('room_id', $id)
                         ->latest()
                         ->get();
        return response()->json($reviews, 200);
    }
}