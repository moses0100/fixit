<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_jobs_and_latest_progress_appear_before_summary_and_hide_other_owners(): void
    {
        $user = User::factory()->create();
        $active = RepairRequest::factory()->create(['user_id' => $user->id, 'status' => 'repairing']);
        $active->histories()->create(['user_id' => $user->id, 'status_to' => 'repairing', 'note' => 'กำลังเปลี่ยนพัดลม']);
        RepairRequest::factory()->create(['user_id' => $user->id, 'status' => 'completed']);
        $other = RepairRequest::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($user)->get('/dashboard')->assertOk()
            ->assertViewHas('activeRepairs', fn ($jobs) => $jobs->count() === 1 && $jobs->first()->is($active))
            ->assertViewHas('repairs', fn ($jobs) => $jobs->count() === 1 && $jobs->first()->status === 'completed')
            ->assertSeeInOrder(['งานที่กำลังดำเนินการ', 'กำลังเปลี่ยนพัดลม', 'ประวัติงานล่าสุด', 'สรุปงานซ่อม'])
            ->assertSee('aria-current="step"', false)->assertDontSee($other->ticket_no)
            ->assertDontSee('MY WORKSPACE')->assertDontSee('welcome-card', false);
        $this->assertStringContainsString('ดูรายละเอียด', $response->getContent());
    }

    public function test_empty_dashboard_and_admin_layout_remain_available(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk()
            ->assertSee('ไม่มีงานซ่อมค้างอยู่')->assertSee('แจ้งซ่อมใหม่');
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin')->assertOk()
            ->assertSee('welcome-card', false)->assertSee('metric-grid', false)
            ->assertDontSee('user-dashboard', false)->assertSee('<table', false);
    }

    public function test_filtered_ajax_results_and_pagination_use_private_cards(): void
    {
        $user = User::factory()->create();
        RepairRequest::factory()->count(11)->create(['user_id' => $user->id, 'status' => 'pending']);
        $other = RepairRequest::factory()->create(['status' => 'pending']);
        $this->actingAs($user)->get('/repairs?status=pending')->assertOk()
            ->assertSee('repair-card-grid', false)->assertDontSee('<table', false)->assertDontSee($other->ticket_no);
        $response = $this->getJson('/repairs?status=pending&page=2', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('total', 11);
        $this->assertStringContainsString('repair-card', $response->json('html'));
        $this->assertStringContainsString('ดูรายละเอียด', $response->json('html'));
        $this->assertStringNotContainsString($other->ticket_no, $response->json('html'));
    }
}
