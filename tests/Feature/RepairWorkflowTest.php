<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DemoRepairSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RepairWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function data(array $extra = []): array
    {
        return array_merge(['device_type' => 'Notebook', 'brand' => 'Lenovo', 'title' => 'เครื่องเปิดไม่ติด',
            'problem_description' => 'กดปุ่มแล้วไม่มีไฟแสดงผล', 'urgency' => 'high', 'contact_phone' => '0891234567'], $extra);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_guest_and_regular_user_cannot_access_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/repairs')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $this->get('/admin/repairs')->assertForbidden();
    }

    public function test_registration_cannot_choose_admin_role(): void
    {
        $this->post('/register', ['name' => 'Test', 'email' => 'new@example.com', 'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'admin'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'new@example.com', 'role' => 'user']);
    }

    public function test_create_upload_and_generated_ticket_ignore_protected_fields(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user)->post('/repairs', $this->data([
            'image' => UploadedFile::fake()->image('problem.png'),
            'user_id' => 9999, 'status' => 'completed', 'admin_note' => 'injected', 'ticket_no' => 'injected',
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $repair = RepairRequest::firstOrFail();
        $this->assertSame($user->id, $repair->user_id);
        $this->assertSame('pending', $repair->status);
        $this->assertNull($repair->admin_note);
        $this->assertStringStartsWith('REP-', $repair->ticket_no);
        Storage::disk('local')->assertExists($repair->image);
        $this->get(route('repairs.image', $repair))->assertOk();
        $this->get(route('repairs.show', $repair))->assertOk()->assertSee($repair->ticket_no);
    }

    public function test_required_fields_and_invalid_images_are_rejected(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create())->post('/repairs', [])->assertSessionHasErrors(['brand', 'title', 'problem_description', 'contact_phone']);
        $this->post('/repairs', $this->data(['image' => UploadedFile::fake()->create('bad.php', 10, 'text/plain')]))->assertSessionHasErrors('image');
        $this->post('/repairs', $this->data(['image' => UploadedFile::fake()->image('large.png')->size(2049)]))->assertSessionHasErrors('image');
        $this->assertDatabaseCount('repair_requests', 0);
    }

    public function test_owner_can_update_and_replace_or_remove_image(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $old = UploadedFile::fake()->image('old.jpg')->store('repairs', 'local');
        $repair = RepairRequest::factory()->create(['user_id' => $user->id, 'image' => $old]);
        $this->actingAs($user)->put(route('repairs.update', $repair), $this->data(['title' => 'แก้ไขแล้ว', 'status' => 'completed', 'image' => UploadedFile::fake()->image('new.png')]))->assertSessionHasNoErrors();
        $repair->refresh();
        $this->assertSame('pending', $repair->status);
        $this->assertSame('แก้ไขแล้ว', $repair->title);
        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($repair->image);
        $path = $repair->image;
        $this->put(route('repairs.update', $repair), $this->data(['remove_image' => 1]))->assertSessionHasNoErrors();
        $this->assertNull($repair->fresh()->image);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_other_users_cannot_read_edit_cancel_or_open_image(): void
    {
        $repair = RepairRequest::factory()->create();
        $this->actingAs(User::factory()->create());
        $this->get(route('repairs.show', $repair))->assertForbidden();
        $this->get(route('repairs.edit', $repair))->assertForbidden();
        $this->get(route('repairs.image', $repair))->assertForbidden();
        $this->put(route('repairs.update', $repair), $this->data())->assertForbidden();
        $this->patch(route('repairs.cancel', $repair))->assertForbidden();
        $this->put(route('admin.repairs.status', $repair), ['status' => 'repairing'])->assertForbidden();
        $this->get('/repairs?q='.$repair->ticket_no)->assertOk()->assertDontSee($repair->title);
    }

    public function test_cancel_preserves_record_and_cannot_reopen(): void
    {
        $repair = RepairRequest::factory()->create();
        $this->actingAs($repair->user)->patch(route('repairs.cancel', $repair))->assertRedirect();
        $this->assertDatabaseHas('repair_requests', ['id' => $repair->id, 'status' => 'cancelled']);
        $this->actingAs($this->admin())->put(route('admin.repairs.status', $repair), ['status' => 'pending'])->assertSessionHasErrors('status');
    }

    public function test_non_pending_requests_cannot_be_edited_or_cancelled(): void
    {
        foreach (['repairing', 'completed', 'cancelled'] as $status) {
            $repair = RepairRequest::factory()->create(['status' => $status]);
            $this->actingAs($repair->user);
            $this->get(route('repairs.edit', $repair))->assertForbidden();
            $this->put(route('repairs.update', $repair), $this->data())->assertForbidden();
            $this->patch(route('repairs.cancel', $repair))->assertForbidden();
        }
    }

    public function test_admin_workflow_validates_order_and_completion_time(): void
    {
        $repair = RepairRequest::factory()->create();
        $this->actingAs($this->admin());
        $url = route('admin.repairs.status', $repair);
        $this->put($url, ['status' => 'completed'])->assertSessionHasErrors('status');
        $this->put($url, ['status' => 'repairing', 'admin_note' => 'กำลังตรวจสอบ'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('repair_requests', ['id' => $repair->id, 'status' => 'repairing']);
        $this->put($url, ['status' => 'pending'])->assertSessionHasErrors('status');
        $this->put($url, ['status' => 'completed', 'admin_note' => 'ซ่อมแล้ว'])->assertSessionHasNoErrors();
        $this->assertNotNull($repair->fresh()->completed_at);
        $completed = $repair->fresh()->completed_at->toDateTimeString();
        $this->travel(1)->hours();
        $this->put($url, ['status' => 'completed', 'admin_note' => 'ส่งมอบแล้ว'])->assertSessionHasNoErrors();
        $this->assertSame($completed, $repair->fresh()->completed_at->toDateTimeString());
        $this->put($url, ['status' => 'repairing'])->assertSessionHasErrors('status');
        $this->actingAs($repair->user)->get(route('repairs.show', $repair))->assertSee('ส่งมอบแล้ว');
    }

    public function test_all_main_pages_render_with_records_and_search_filter_pagination_work(): void
    {
        $user = User::factory()->create(['name' => 'ผู้แจ้งค้นหา']);
        $repair = RepairRequest::factory()->create(['user_id' => $user->id, 'serial_number' => 'SERIAL-UNIQUE', 'status' => 'repairing', 'urgency' => 'high']);
        RepairRequest::factory()->count(12)->create(['status' => 'pending', 'urgency' => 'low']);
        $this->actingAs($user);
        foreach (['/dashboard', '/repairs', '/repairs/create', route('repairs.show', $repair)] as $url) {
            $this->get($url)->assertOk();
        }
        $pending = RepairRequest::factory()->create(['user_id' => $user->id]);
        $this->get(route('repairs.edit', $pending))->assertOk();
        $this->actingAs($this->admin());
        foreach (['/admin', '/admin/repairs', route('admin.repairs.show', $repair)] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/admin/repairs?q=SERIAL-UNIQUE')->assertSee($repair->ticket_no)->assertViewHas('repairs', fn ($rows) => $rows->total() === 1);
        $this->get('/admin/repairs?status=repairing&urgency=high')->assertViewHas('repairs', fn ($rows) => $rows->total() === 1);
        $this->get('/admin/repairs?q='.urlencode('ผู้แจ้งค้นหา'))->assertViewHas('repairs', fn ($rows) => $rows->total() === 2);
        $this->get('/admin/repairs?page=2')->assertViewHas('repairs', fn ($rows) => $rows->currentPage() === 2 && $rows->count() === 4);
    }

    public function test_account_with_repair_history_cannot_be_deleted(): void
    {
        $repair = RepairRequest::factory()->create();
        $this->actingAs($repair->user)->delete('/profile', ['password' => 'password'])->assertSessionHasErrorsIn('userDeletion');
        $this->assertDatabaseHas('users', ['id' => $repair->user_id]);
        $this->assertDatabaseHas('repair_requests', ['id' => $repair->id]);
    }

    public function test_demo_seeding_is_repeatable_without_duplicates(): void
    {
        $this->seed([AdminSeeder::class, DemoRepairSeeder::class]);
        $this->seed([AdminSeeder::class, DemoRepairSeeder::class]);
        $this->assertDatabaseCount('repair_requests', 18);
        $this->assertDatabaseHas('users', ['email' => 'admin@fixit.test', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'user@fixit.test', 'role' => 'user']);
    }

    public function test_sort_uses_report_date_rather_than_insertion_id(): void
    {
        $recent = RepairRequest::factory()->create(['created_at' => now()]);
        $older = RepairRequest::factory()->create(['created_at' => now()->subDays(7)]);
        $this->actingAs($this->admin());
        $this->get('/admin/repairs?sort=newest')->assertViewHas('repairs', fn ($rows) => $rows->first()->id === $recent->id);
        $this->get('/admin/repairs?sort=oldest')->assertViewHas('repairs', fn ($rows) => $rows->first()->id === $older->id);
    }
}
