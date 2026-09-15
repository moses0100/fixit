<?php

namespace App\Http\Controllers\Auth;

use App\Mail\PasswordResetOtpMail;
use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::whereRaw('LOWER(email) = ?', [$email])
            ->where('is_active', true)
            ->first();

        if (! $user) {
            return back()->with('status', 'หากอีเมลนี้มีบัญชีในระบบ เราจะส่งรหัส OTP ไปให้');
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10);

        PasswordResetOtp::where('user_id', $user->id)->whereNull('used_at')->delete();
        $resetOtp = PasswordResetOtp::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make($otp),
            'expires_at' => $expiresAt,
        ]);

        try {
            Mail::to($user->email)->send(new PasswordResetOtpMail($user, $otp, $expiresAt));
        } catch (Throwable $error) {
            $resetOtp->delete();
            report($error);

            return back()->withInput()->withErrors([
                'email' => 'ไม่สามารถส่งอีเมลได้ กรุณาตรวจสอบการตั้งค่า SMTP แล้วลองใหม่',
            ]);
        }

        $request->session()->put('password_reset.email', $user->email);

        return redirect()->route('password.otp')->with('status', 'ส่งรหัส OTP ไปยังอีเมลแล้ว รหัสมีอายุ 10 นาที');
    }
}
