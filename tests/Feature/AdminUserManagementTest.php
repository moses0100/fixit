<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_only_admin_can_open_member_management(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/users')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin/users')->assertOk()->assertSee('จัดการสมาชิก');
    }

    public function test_admin_can_search_member_by_identity_and_repair_information(): void
    {
        $user = User::factory()->create(['name' => 'สมาชิกค้นหา', 'email' => 'member@example.com']);
        $repair = RepairRequest::factory()->create(['user_id' => $user->id, 'ticket_no' => 'REP-SEARCH-001', 'contact_phone' => '0891112222']);
        User::factory()->create(['name' => 'คนอื่น']);
        $this->actingAs($this->admin());

        foreach (['สมาชิกค้นหา', 'member@example.com', 'REP-SEARCH-001', '0891112222'] as $keyword) {
            $this->get('/admin/users?q='.urlencode($keyword))->assertSee($user->email)->assertDontSee('คนอื่น');
        }
        $this->get(route('admin.users.show', $user))->assertOk()->assertSee($repair->ticket_no);
    }

    public function test_admin_can_update_regular_member_without_changing_role(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($this->admin())->put(route('admin.users.update', $user), [
            'name' => 'ชื่อใหม่',
            'email' => 'new@example.com',
            'role' => 'admin',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'ชื่อใหม่', 'email' => 'new@example.com', 'role' => 'user']);
    }

    public function test_admin_can_reset_member_password_and_send_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->actingAs($this->admin())->put(route('admin.users.password', $user), [
            'password' => 'Temporary123!',
            'password_confirmation' => 'Temporary123!',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('Temporary123!', $user->fresh()->password));

        $this->post(route('admin.users.reset-link', $user))->assertSessionHasNoErrors();
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_suspended_member_cannot_login_or_continue_session_and_can_be_reactivated(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);
        $admin = $this->admin();
        $this->actingAs($admin)->patch(route('admin.users.status', $user), ['is_active' => false])->assertSessionHasNoErrors();
        $this->assertFalse($user->fresh()->is_active);
        $this->post('/logout');
        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])->assertSessionHasErrors('email');

        $this->actingAs($user->fresh())->get('/dashboard')->assertRedirect('/login');
        $this->actingAs($admin)->patch(route('admin.users.status', $user), ['is_active' => true])->assertSessionHasNoErrors();
        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])->assertRedirect('/dashboard');
    }

    public function test_admin_accounts_cannot_be_modified_from_member_management(): void
    {
        $target = $this->admin();
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.users.update', $target), ['name' => 'เปลี่ยน', 'email' => 'changed@example.com'])->assertForbidden();
        $this->put(route('admin.users.password', $target), ['password' => 'Temporary123!', 'password_confirmation' => 'Temporary123!'])->assertForbidden();
        $this->patch(route('admin.users.status', $target), ['is_active' => false])->assertForbidden();
    }
}
