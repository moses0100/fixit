<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyPasswordOtpRequest;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetOtpController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('password_reset.email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp', compact('email'));
    }

    public function verify(VerifyPasswordOtpRequest $request): RedirectResponse
    {
        $sessionEmail = $request->session()->get('password_reset.email');
        $email = $request->validated('email');

        if (! $sessionEmail || strcasecmp($sessionEmail, $email) !== 0) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'คำขอรีเซ็ตรหัสผ่านหมดอายุ กรุณาเริ่มใหม่อีกครั้ง',
            ]);
        }

        $user = User::where('email', $sessionEmail)->where('is_active', true)->first();

        if (! $user) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'คำขอรีเซ็ตรหัสผ่านไม่ถูกต้อง กรุณาเริ่มใหม่อีกครั้ง',
            ]);
        }

        $error = null;
        $verified = DB::transaction(function () use ($request, $user, &$error): bool {
            $otp = PasswordResetOtp::query()
                ->where('user_id', $user->id)
                ->whereNull('used_at')
                ->latest('created_at')
                ->lockForUpdate()
                ->first();

            if (! $otp || $otp->expires_at->isPast()) {
                $error = 'รหัส OTP หมดอายุหรือไม่ถูกต้อง กรุณาขอรหัสใหม่';

                return false;
            }

            if ($otp->attempts >= 5) {
                $error = 'คุณกรอกรหัส OTP ผิดเกินจำนวนที่กำหนด กรุณาขอรหัสใหม่';

                return false;
            }

            if (! Hash::check($request->validated('otp'), $otp->otp_hash)) {
                $otp->increment('attempts');
                $error = 'รหัส OTP ไม่ถูกต้อง';

                return false;
            }

            $otp->forceFill(['used_at' => now()])->save();

            return true;
        });

        if (! $verified) {
            return back()->withInput(['email' => $email])->withErrors(['otp' => $error]);
        }

        $token = Password::createToken($user);
        $request->session()->forget('password_reset');

        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);
    }
}
