<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use Tests\TestCase;
use App\Models\User;
use App\Models\Station;
use App\Models\Payment;

class PaymentTest extends TestCase
{
public function test_user_can_create_payment()
{
    $user = User::factory()->create();
    $station = Station::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('payments.store'), [
            'station_id' => $station->id,
            'title' => 'Test Payment',
            'amount' => 1000,
            'due_date' => now()->addWeek()->format('Y-m-d'),
            'type' => 'utility'
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('payments', ['title' => 'Test Payment']);
}

public function test_admin_can_approve_payment()
{
    $admin = User::factory()->admin()->create();
    $payment = Payment::factory()->create();

    $response = $this->actingAs($admin)
        ->post(route('payments.approve', $payment));

    $response->assertRedirect();
    $this->assertEquals('approved', $payment->fresh()->status);
}
}
