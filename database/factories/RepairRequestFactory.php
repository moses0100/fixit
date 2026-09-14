<?php

namespace Database\Factories;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RepairRequestFactory extends Factory
{
    protected $model = RepairRequest::class;

    public function definition(): array
    {
        return [
            'ticket_no' => 'DEMO-'.fake()->unique()->numerify('########'),
            'user_id' => User::factory(),
            'device_type' => fake()->randomElement(RepairRequest::DEVICES),
            'brand' => fake()->randomElement(['Lenovo', 'Dell', 'HP', 'ASUS', 'Acer', 'Apple']),
            'model' => fake()->randomElement(['ThinkPad T14', 'Latitude 5420', 'Pavilion', 'VivoBook']),
            'serial_number' => strtoupper(fake()->bothify('SN-??######')),
            'title' => fake()->randomElement(['เปิดเครื่องไม่ติด', 'หน้าจอกะพริบระหว่างใช้งาน', 'เครื่องช้าและค้างบ่อย', 'เชื่อมต่อ Wi-Fi ไม่ได้', 'แบตเตอรี่ไม่เก็บไฟ', 'พัดลมมีเสียงดัง']),
            'problem_description' => 'พบอาการระหว่างใช้งานตามปกติ ลองปิดและเปิดเครื่องใหม่แล้วแต่ยังพบปัญหา กรุณาช่วยตรวจสอบอุปกรณ์',
            'urgency' => fake()->randomElement(array_keys(RepairRequest::URGENCIES)),
            'contact_phone' => '0891234567',
            'status' => 'pending',
        ];
    }
}
