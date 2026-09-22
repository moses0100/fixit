<?php

namespace Tests\Feature;

use App\Exports\RepairsExport;
use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Tests\TestCase;

class SuggestExportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_suggest_needs_login_and_hides_job_details(): void
    {
        $this->getJson('/repairs/suggest?q=เปิด')->assertUnauthorized();
        $owner = User::factory()->create();
        RepairRequest::factory()->create([
            'user_id' => $owner->id, 'ticket_no' => 'REP-2026-99999',
            'title' => 'เปิดเครื่องไม่ติด', 'status' => 'completed',
            'admin_note' => 'แนวทางสาธารณะ: ตรวจแรมก่อน', 'is_guidance' => true,
        ]);
        $res = $this->actingAs(User::factory()->create())->getJson('/repairs/suggest?q=เปิดเครื่อง');
        $res->assertOk()->assertJson(['count' => 1]);
        $res->assertJsonMissing(['ticket' => 'REP-2026-99999']);
        $this->assertStringNotContainsString('REP-2026-99999', $res->getContent());
        $this->assertStringNotContainsString('เปิดเครื่องไม่ติด', $res->getContent());
        $this->assertArrayNotHasKey('cases', $res->json());
    }

    public function test_suggest_ignores_private_notes(): void
    {
        RepairRequest::factory()->create([
            'ticket_no' => 'REP-2026-88888', 'title' => 'คีย์บอร์ดพิมพ์เบิ้ลเฉพาะกิจ',
            'status' => 'completed', 'admin_note' => 'โทรกลับ 0812345678 คุณสมชาย',
        ]);
        $res = $this->actingAs(User::factory()->create())->getJson('/repairs/suggest?q=พิมพ์เบิ้ลเฉพาะกิจ');
        $res->assertOk()->assertJson(['count' => 0, 'hint' => null]);
        $this->assertStringNotContainsString('0812345678', $res->getContent());
        $this->assertStringNotContainsString('สมชาย', $res->getContent());
    }

    public function test_suggest_counts_beyond_ten_jobs(): void
    {
        RepairRequest::factory()->count(12)->create([
            'title' => 'แบตเสื่อมชาร์จไม่เข้าเคสทดสอบ', 'status' => 'completed',
            'admin_note' => 'แนวทางสาธารณะ: เปลี่ยนแบต', 'is_guidance' => true,
        ]);
        $res = $this->actingAs(User::factory()->create())->getJson('/repairs/suggest?q=ชาร์จไม่เข้าเคสทดสอบ');
        $res->assertOk()->assertJson(['count' => 12]);
        $this->assertNotNull($res->json('hint'));
    }

    public function test_suggest_short_query_returns_empty(): void
    {
        $this->actingAs(User::factory()->create())->getJson('/repairs/suggest?q=x')
            ->assertOk()->assertJson(['count' => 0, 'hint' => null]);
    }

    public function test_export_downloads_xlsx_for_admin_only(): void
    {
        $this->get('/admin/repairs/export')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/repairs/export')->assertForbidden();
        RepairRequest::factory()->create(['ticket_no' => 'REP-2026-00001', 'title' => '=CMD(test)', 'status' => 'pending']);
        $res = $this->actingAs($this->admin())->get('/admin/repairs/export?status=pending');
        $res->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $res->headers->get('content-type'));
        $this->assertStringContainsString('.xlsx', (string) $res->headers->get('content-disposition'));
    }

    public function test_export_query_respects_status_filter(): void
    {
        RepairRequest::factory()->create(['ticket_no' => 'REP-2026-00002', 'status' => 'pending']);
        RepairRequest::factory()->create(['ticket_no' => 'REP-2026-00003', 'status' => 'completed']);
        $request = \Illuminate\Http\Request::create('/admin/repairs/export', 'GET', ['status' => 'pending']);
        $statuses = (new RepairsExport($request))->query()->pluck('status')->unique()->values()->all();
        $this->assertSame(['pending'], $statuses);
    }

    public function test_export_binds_formula_like_text_as_string(): void
    {
        foreach (['=CMD(test)', '+123', '-123', '@user'] as $text) {
            $export = new RepairsExport;
            $cell = $this->createMock(Cell::class);
            $cell->expects($this->once())->method('setValueExplicit')
                ->with($text, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $this->assertTrue($export->bindValue($cell, $text));
        }
        $export = new RepairsExport;
        $cell = $this->createMock(Cell::class);
        $cell->expects($this->once())->method('setValueExplicit')
            ->with('ข้อความปกติ', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $this->assertTrue($export->bindValue($cell, 'ข้อความปกติ'));
    }
}
