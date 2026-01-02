<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use App\Models\User as UserModel;
use Illuminate\Auth\Events\Verified;

class AuthVerificationController extends Controller
{
    public function publicResend(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = (string) $request->input('email');
        $user = UserModel::where('email', $email)->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'message' => 'Email verifikasi telah dikirim'
        ], 200);
    }
    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah terverifikasi'], 200);
        }

        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Email verifikasi telah dikirim'], 200);
    }

    public function verify(Request $request, $id, $hash)
    {
        $user = UserModel::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Tautan verifikasi tidak valid'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah terverifikasi'], 409);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['message' => 'Email berhasil diverifikasi'], 200);
    }
}
