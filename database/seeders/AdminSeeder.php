<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }
        // บัญชีสาธิตในเครื่องเท่านั้น ไม่เปลี่ยนรหัสผ่าน/Role ของบัญชีที่มีอยู่แล้ว
        if (! User::where('email', 'admin@fixit.test')->exists()) {
            $user = new User;
            $user->name = 'ผู้ดูแล FixIT';
            $user->email = 'admin@fixit.test';
            $user->password = Hash::make('FixIT-demo-2026!');
            $user->role = 'admin';
            $user->save();
        }
    }
}
