<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ForgotPwApiController extends Controller
{
    
    public function sendVerificationCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $email = $request->email;

        $code = rand(1000, 9999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['verification_code' => $code, 'created_at' => now()]
        );

        try {
            Mail::raw("Kode verifikasi Anda adalah: $code", function ($message) use ($email) {
                $message->to($email)->subject('Kode Verifikasi Reset Password');
            });
        } catch (\Exception $e) {
            \Log::error('Gagal kirim email di API: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengirim email', 'error' => $e->getMessage()], 500);
        }

        

        return response()->json(['message' => 'Kode verifikasi dikirim ke email.']);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'verification_code' => 'required|digits:4'
        ]);

        $exists = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('verification_code', $request->verification_code)
            ->exists();

        if (!$exists) {
            return response()->json(['success' => false, 'message' => 'Kode verifikasi tidak valid.'], 422);
        }
        return response()->json(['success' => true, 'message' => 'Kode verifikasi valid.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|confirmed|min:8',
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Password successfully reset.'], 200);
    }

}
