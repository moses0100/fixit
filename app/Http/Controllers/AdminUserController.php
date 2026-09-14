<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminResetUserPasswordRequest;
use App\Http\Requests\AdminUpdateUserRequest;
use App\Http\Requests\AdminUpdateUserStatusRequest;
use App\Http\Requests\AdminUserIndexRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(AdminUserIndexRequest $request): View
    {
        $filters = $request->validated();
        $users = User::query()
            ->withCount('repairRequests')
            ->when($filters['q'] ?? null, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhereHas('repairRequests', function ($query) use ($keyword) {
                            $query->where('ticket_no', 'like', "%{$keyword}%")
                                ->orWhere('contact_phone', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'suspended', fn ($query) => $query->where('is_active', false))
            ->latest('created_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->loadCount('repairRequests');
        $repairs = $user->repairRequests()->latest('created_at')->latest('id')->limit(5)->get();

        return view('admin.users.show', compact('user', 'repairs'));
    }

    public function update(AdminUpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureRegularUser($user);
        $user->update($request->validated());

        return back()->with('success', 'บันทึกข้อมูลสมาชิกแล้ว');
    }

    public function updatePassword(AdminResetUserPasswordRequest $request, User $user): RedirectResponse
    {
        $this->ensureRegularUser($user);
        $user->forceFill([
            'password' => Hash::make($request->validated('password')),
            'remember_token' => Str::random(60),
        ])->save();
        $this->logoutUser($user);

        return back()->with('success', 'ตั้งรหัสผ่านชั่วคราวแล้ว และออกจากระบบในอุปกรณ์เดิมทั้งหมดแล้ว');
    }

    public function updateStatus(AdminUpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        $this->ensureRegularUser($user);
        $isActive = $request->boolean('is_active');
        $user->forceFill(['is_active' => $isActive])->save();
        if (! $isActive) {
            $this->logoutUser($user);
        }

        return back()->with('success', $isActive ? 'เปิดใช้งานบัญชีแล้ว' : 'ระงับบัญชีและออกจากระบบทุกอุปกรณ์แล้ว');
    }

    public function sendResetLink(User $user): RedirectResponse
    {
        $this->ensureRegularUser($user);
        abort_unless($user->is_active, 422, 'ไม่สามารถส่งลิงก์ให้บัญชีที่ถูกระงับ');
        $status = Password::sendResetLink(['email' => $user->email]);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'ส่งลิงก์ตั้งรหัสผ่านใหม่แล้ว')
            : back()->withErrors(['email' => __($status)]);
    }

    private function ensureRegularUser(User $user): void
    {
        abort_if($user->role === 'admin', 403, 'ไม่อนุญาตให้แก้ไขบัญชีผู้ดูแลระบบจากหน้านี้');
    }

    private function logoutUser(User $user): void
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
    }
}
