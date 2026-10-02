<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Mail\PasswordResetOtpMail;
use App\Models\RepairRequest;
use App\Models\User;
use App\Notifications\RepairStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReviewRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_counts_include_all_owners_and_user_counts_stay_private(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        RepairRequest::factory()->create(['user_id' => $admin->id, 'status' => 'pending']);
        RepairRequest::factory()->create(['user_id' => $user->id, 'status' => 'repairing']);
        RepairRequest::factory()->create(['status' => 'completed']);

        $this->getJson('/admin/dashboard/counts')->assertUnauthorized();
        $this->actingAs($user)->getJson('/admin/dashboard/counts')->assertForbidden();
        $this->getJson('/dashboard/counts')->assertExactJson([
            'all' => 1, 'pending' => 0, 'repairing' => 1, 'completed' => 0, 'cancelled' => 0,
        ]);
        $this->get('/dashboard')->assertSee('data-counts-url="'.route('dashboard.counts').'"', false);

        $this->actingAs($admin)->getJson('/admin/dashboard/counts')->assertOk()->assertExactJson([
            'all' => 3, 'pending' => 1, 'repairing' => 1, 'completed' => 1, 'cancelled' => 0,
        ]);
        $this->get('/admin')->assertSee('data-counts-url="'.route('admin.dashboard.counts').'"', false);
        $this->getJson('/dashboard/counts')->assertExactJson([
            'all' => 1, 'pending' => 1, 'repairing' => 0, 'completed' => 0, 'cancelled' => 0,
        ]);
    }

    public function test_history_and_notification_use_locked_status_when_bound_model_is_stale(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $repair = RepairRequest::factory()->create(['status' => 'pending']);
        RepairRequest::whereKey($repair->id)->update(['status' => 'repairing']);
        $request = Request::create('/admin/repairs/'.$repair->id.'/status', 'PUT', [
            'status' => 'completed', 'admin_note' => 'ซ่อมเสร็จแล้ว',
        ]);
        $request->setUserResolver(fn () => $admin);

        app(AdminController::class)->updateStatus($request, $repair);

        $this->assertDatabaseHas('repair_histories', [
            'repair_request_id' => $repair->id, 'user_id' => $admin->id,
            'status_from' => 'repairing', 'status_to' => 'completed',
        ]);
        Notification::assertSentTo($repair->user, RepairStatusChanged::class,
            fn ($notification) => $notification->statusFrom === 'repairing'
                && $notification->statusTo === 'completed');
        $this->assertSame('completed', $repair->fresh()->status);
    }

    public function test_otp_response_is_identical_for_known_unknown_and_suspended_accounts(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $suspended = User::factory()->create(['is_active' => false]);
        $expected = 'หากอีเมลนี้มีบัญชีในระบบ เราจะส่งรหัส OTP ไปให้ รหัสมีอายุ 10 นาที';

        foreach ([$user->email, 'missing@example.com', $suspended->email] as $email) {
            $this->post('/forgot-password', ['email' => $email])
                ->assertRedirect(route('password.otp'))
                ->assertSessionHas('status', $expected)
                ->assertSessionHas('password_reset.email', strtolower($email));
        }

        Mail::assertSent(PasswordResetOtpMail::class, 1);
    }

    public function test_otp_mail_failure_uses_same_response_and_removes_unsent_code(): void
    {
        $user = User::factory()->create();
        Mail::shouldReceive('to')->once()->with($user->email)
            ->andThrow(new \RuntimeException('Simulated SMTP failure'));

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect(route('password.otp'))
            ->assertSessionHas('status', 'หากอีเมลนี้มีบัญชีในระบบ เราจะส่งรหัส OTP ไปให้ รหัสมีอายุ 10 นาที');
        $this->assertDatabaseCount('password_reset_otps', 0);
    }

    public function test_otp_verification_preserves_mixed_case_email_compatibility(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'Member@Example.com']);
        $this->post('/forgot-password', ['email' => 'MEMBER@example.com'])
            ->assertSessionHas('password_reset.email', 'member@example.com');
        $otp = Mail::sent(PasswordResetOtpMail::class)->first()->otp;

        $this->post('/verify-otp', ['email' => 'member@example.com', 'otp' => $otp])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('password_reset_otps', ['user_id' => $user->id, 'attempts' => 0]);
        $this->assertNotNull($user->fresh()->email);
    }
}
