<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_home_but_user_is_redirected_to_dashboard(): void
    {
        $this->get('/')->assertOk();
        $this->actingAs(User::factory()->create())->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_phone_number_must_be_9_to_20_chars(): void
    {
        $user = User::factory()->create();
        $data = ['device_type' => 'Notebook', 'brand' => 'Lenovo', 'title' => 'จอเสีย',
            'problem_description' => 'จอไม่ติด', 'urgency' => 'medium'];
        $this->actingAs($user)->post('/repairs', $data + ['contact_phone' => '1234567'])
            ->assertSessionHasErrors('contact_phone');
        $this->actingAs($user)->post('/repairs', $data + ['contact_phone' => str_repeat('1', 21)])
            ->assertSessionHasErrors('contact_phone');
        $this->actingAs($user)->post('/repairs', $data + ['contact_phone' => '0812345678'])
            ->assertSessionHasNoErrors();
    }

    public function test_admin_and_user_shells_look_different(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin')->assertSee('ศูนย์ดูแลระบบ', false)->assertSee('theme-admin', false)->assertSee('sidebar', false);
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertSee('usernav', false)->assertSee('รายการของฉัน', false)->assertDontSee('theme-admin', false)->assertDontSee('ศูนย์ดูแลระบบ', false);
    }

    public function test_create_form_shows_own_devices_and_last_phone(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/repairs', ['device_type' => 'Notebook', 'brand' => 'Acer',
            'model' => 'Nitro 5', 'title' => 'จอเสีย', 'problem_description' => 'จอไม่ติด',
            'urgency' => 'medium', 'contact_phone' => '0811111111'])->assertSessionHasNoErrors();
        $res = $this->actingAs($user)->get(route('repairs.create'))->assertOk();
        $res->assertSee('อุปกรณ์ของฉัน', false)->assertSee('Acer', false)->assertSee('0811111111', false);
        $this->actingAs(User::factory()->create())->get(route('repairs.create'))->assertDontSee('อุปกรณ์ของฉัน', false);
    }

    public function test_topbar_has_notification_bell(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')
            ->assertSee('aria-label="การแจ้งเตือน"', false)
            ->assertSee(route('notifications.index'), false);
    }

    public function test_user_cta_always_visible(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('repairs.index'))->assertSee('แจ้งซ่อมใหม่', false);
        $this->actingAs($user)->get('/dashboard')->assertSee('metric-grid', false)->assertSee('action-strip', false);
        $this->actingAs($user)->get('/dashboard')->assertSee('status=pending', false)->assertSee('status=repairing', false);
    }
}
