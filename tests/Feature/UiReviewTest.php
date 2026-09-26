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
}
