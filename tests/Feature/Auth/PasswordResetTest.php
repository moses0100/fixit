<?php

namespace Tests\Feature\Auth;

use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_otp_can_be_requested_by_email(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertRedirect(route('password.otp'));
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use ($user) {
            return $mail->user->is($user) && strlen($mail->otp) === 6;
        });
        $this->assertDatabaseHas('password_reset_otps', ['user_id' => $user->id]);
    }

    public function test_otp_screen_can_be_rendered(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $this->get('/verify-otp')
            ->assertStatus(200)
            ->assertSee('ยืนยันรหัส OTP');
    }

    public function test_unknown_email_uses_the_same_otp_response_without_sending_mail(): void
    {
        Mail::fake();

        $response = $this->post('/forgot-password', ['email' => 'missing@example.com']);

        $response->assertRedirect(route('password.otp'))->assertSessionHas('status');
        Mail::assertNothingSent();
        $this->get('/verify-otp')->assertStatus(200)->assertSee('missing@example.com');
    }

    public function test_invalid_otp_is_rejected_and_attempt_is_recorded(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $this->post('/verify-otp', [
            'email' => $user->email,
            'otp' => '000000',
        ])->assertSessionHasErrors('otp');

        $this->assertDatabaseHas('password_reset_otps', [
            'user_id' => $user->id,
            'attempts' => 1,
        ]);
    }

    public function test_expired_otp_is_rejected(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);
        PasswordResetOtp::query()->where('user_id', $user->id)->update(['expires_at' => now()->subMinute()]);

        $this->post('/verify-otp', [
            'email' => $user->email,
            'otp' => '000000',
        ])->assertSessionHasErrors('otp');
    }

    public function test_otp_cannot_be_used_twice(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email]);

        $otp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$otp) {
            $otp = $mail->otp;

            return true;
        });

        $this->post('/verify-otp', ['email' => $user->email, 'otp' => $otp])->assertRedirect();

        $this->withSession(['password_reset.email' => $user->email])
            ->post('/verify-otp', ['email' => $user->email, 'otp' => $otp])
            ->assertSessionHasErrors('otp');
    }

    public function test_otp_is_blocked_after_five_invalid_attempts(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email]);

        $otp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$otp) {
            $otp = $mail->otp;

            return true;
        });

        foreach (range(1, 5) as $attempt) {
            $this->post('/verify-otp', ['email' => $user->email, 'otp' => '000000'])
                ->assertSessionHasErrors('otp');
        }

        $this->post('/verify-otp', ['email' => $user->email, 'otp' => $otp])
            ->assertSessionHasErrors('otp');
        $this->assertDatabaseHas('password_reset_otps', ['user_id' => $user->id, 'attempts' => 5]);
    }

    public function test_requesting_a_new_otp_invalidates_the_previous_otp(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email]);

        $oldOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$oldOtp) {
            $oldOtp = $mail->otp;

            return true;
        });

        $this->post('/forgot-password', ['email' => $user->email]);

        $newOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$newOtp) {
            $newOtp = $mail->otp;

            return true;
        });

        $this->post('/verify-otp', ['email' => $user->email, 'otp' => $oldOtp])
            ->assertSessionHasErrors('otp');
        $this->post('/verify-otp', ['email' => $user->email, 'otp' => $newOtp])
            ->assertRedirect();
    }

    public function test_password_can_be_reset_after_valid_otp(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'existing-device-session',
            'user_id' => $user->id,
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->post('/forgot-password', ['email' => $user->email]);

        $otp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$otp) {
            $otp = $mail->otp;

            return true;
        });

        $otpResponse = $this->post('/verify-otp', [
            'email' => $user->email,
            'otp' => $otp,
        ]);

        $resetUrl = $otpResponse->headers->get('Location');
        parse_str((string) parse_url($resetUrl, PHP_URL_QUERY), $query);
        $token = basename((string) parse_url($resetUrl, PHP_URL_PATH));

        $this->get($resetUrl)->assertStatus(200);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $query['email'],
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors()->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
        $this->assertNotNull(PasswordResetOtp::where('user_id', $user->id)->latest()->value('used_at'));
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    }
}
