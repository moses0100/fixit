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
            'admin_note' => 'แนวทางภายใน: ตรวจแรมก่อน',
        ]);
        $res = $this->actingAs(User::factory()->create())->getJson('/repairs/suggest?q=เปิดเครื่อง');
        $res->assertOk()->assertJson(['count' => 1]);
        $res->assertJsonMissing(['ticket' => 'REP-2026-99999']);
        $this->assertStringNotContainsString('REP-2026-99999', $res->getContent());
        $this->assertArrayNotHasKey('cases', $res->json());
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

    public function test_export_binds_formula_like_text_as_string(): void
    {
        $export = new RepairsExport;
        $cell = $this->createMock(Cell::class);
        $cell->expects($this->once())->method('setValueExplicit')
            ->with('=CMD(test)', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $this->assertTrue($export->bindValue($cell, '=CMD(test)'));
    }
}
