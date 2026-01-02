<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExternalMovieController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string|min:2',
        ]);

        $response = Http::baseUrl(config('services.tmdb.base_url'))
            ->timeout(10)
            ->retry(2, 200)
            ->get('search/movie', [
                'api_key' => config('services.tmdb.key'),
                'query' => $validated['search'],
                'language' => 'id-ID',
                'include_adult' => false,
            ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Gagal mengambil data dari server TMDB'], 502);
        }

        return response()->json($response->json(), 200);
    }

    public function show($id)
    {
        $response = Http::baseUrl(config('services.tmdb.base_url'))
            ->timeout(10)
            ->retry(2, 200)
            ->get('movie/' . $id, [
                'api_key' => config('services.tmdb.key'),
                'language' => 'id-ID',
            ]);

        if ($response->status() === 404) {
            return response()->json(['message' => 'Film tidak ditemukan'], 404);
        }
        if ($response->failed()) {
            return response()->json(['message' => 'Gagal mengambil data dari server TMDB'], 502);
        }

        return response()->json($response->json(), 200);
    }
}