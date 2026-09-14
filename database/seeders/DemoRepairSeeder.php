<?php

namespace Database\Seeders;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoRepairSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }
        $user = User::firstOrCreate(['email' => 'user@fixit.test'], [
            'name' => 'ผู้ใช้สาธิต FixIT', 'password' => Hash::make('FixIT-demo-2026!'),
        ]);
        foreach (range(1, 18) as $number) {
            $ticket = 'DEMO-2026-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
            if (RepairRequest::where('ticket_no', $ticket)->exists()) {
                continue;
            }
            $status = ['pending', 'repairing', 'completed', 'pending', 'cancelled', 'repairing'][$number % 6];
            RepairRequest::factory()->create([
                'ticket_no' => $ticket,
                'user_id' => $user->id,
                'status' => $status,
                'admin_note' => $status === 'completed' ? 'ข้อมูลสาธิต: ตรวจสอบและแก้ไขปัญหาแล้ว ทดสอบการใช้งานเรียบร้อย' : ($status === 'repairing' ? 'ข้อมูลสาธิต: อยู่ระหว่างตรวจสอบอุปกรณ์' : null),
                'completed_at' => $status === 'completed' ? now()->subDays(1) : null,
                'created_at' => now()->subDays($number),
            ]);
        }
    }
}
