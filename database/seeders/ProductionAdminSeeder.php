<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// สร้าง admin บนโฮสจริงจากค่า env ADMIN_EMAIL / ADMIN_PASSWORD เท่านั้น
// ไม่รันถ้าไม่ได้ตั้งค่า ใช้ได้ทุก environment
class ProductionAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) env('ADMIN_EMAIL'));
        $password = (string) env('ADMIN_PASSWORD');
        if ($email === '' || $password === '') {
            return;
        }
        $user = User::firstOrNew(['email' => $email]);
        $user->name = 'ผู้ดูแล FixIT';
        $user->password = Hash::make($password);
        $user->role = 'admin';
        $user->is_active = true;
        $user->save();
    }
}
