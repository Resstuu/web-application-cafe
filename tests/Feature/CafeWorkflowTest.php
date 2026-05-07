<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CafeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_checkout_creates_pending_midtrans_order(): void
    {
        $food = Menu::create(['name' => 'Nasi Goreng', 'category' => 'makanan', 'price' => 18000, 'is_active' => true]);
        $drink = Menu::create(['name' => 'Thai Tea', 'category' => 'minuman', 'price' => 16000, 'is_active' => true]);

        $response = $this->postJson(route('public.checkout'), [
            'customer_name' => 'Restu',
            'table_number' => '7',
            'items' => [
                ['menu_id' => $food->id, 'qty' => 2],
                ['menu_id' => $drink->id, 'qty' => 1],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['demo_payment' => true]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Restu',
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'total' => 52000,
        ]);
        $this->assertDatabaseCount('order_items', 2);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_midtrans_success_notification_confirms_order_for_production(): void
    {
        $order = Order::create([
            'code' => 'ORD-TEST',
            'customer_name' => 'Restu',
            'table_number' => '3',
            'source' => 'customer',
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'total' => 34000,
        ]);
        $order->items()->create(['menu_name' => 'Nasi Goreng', 'category' => 'makanan', 'price' => 18000, 'qty' => 1]);
        $order->items()->create(['menu_name' => 'Thai Tea', 'category' => 'minuman', 'price' => 16000, 'qty' => 1]);
        $order->payment()->create(['gateway_order_id' => 'ORD-TEST', 'gateway' => 'midtrans']);

        $this->postJson(route('midtrans.notification'), [
            'order_id' => 'ORD-TEST',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'fraud_status' => 'accept',
        ])->assertOk();

        $this->assertDatabaseHas('orders', [
            'code' => 'ORD-TEST',
            'status' => 'confirmed',
            'payment_status' => 'lunas',
        ]);
    }

    public function test_kitchen_and_barista_see_only_their_items_and_complete_order(): void
    {
        $kitchen = User::factory()->create(['role' => 'kitchen', 'is_active' => true]);
        $barista = User::factory()->create(['role' => 'barista', 'is_active' => true]);
        $order = Order::create([
            'code' => 'ORD-PROD',
            'customer_name' => 'Restu',
            'table_number' => '5',
            'source' => 'customer',
            'status' => 'confirmed',
            'payment_status' => 'lunas',
            'total' => 34000,
            'confirmed_at' => now(),
        ]);
        $order->items()->create(['menu_name' => 'Nasi Goreng', 'category' => 'makanan', 'price' => 18000, 'qty' => 1]);
        $order->items()->create(['menu_name' => 'Thai Tea', 'category' => 'minuman', 'price' => 16000, 'qty' => 1]);

        $this->actingAs($kitchen)->get(route('production.index', 'makanan'))
            ->assertOk()
            ->assertSee('Nasi Goreng')
            ->assertDontSee('Thai Tea');

        $this->actingAs($barista)->get(route('production.index', 'minuman'))
            ->assertOk()
            ->assertSee('Thai Tea')
            ->assertDontSee('Nasi Goreng');

        $this->actingAs($kitchen)->patch(route('production.complete', [$order, 'makanan']))->assertRedirect();
        $this->assertSame('partially_done', $order->fresh()->status);

        $this->actingAs($barista)->patch(route('production.complete', [$order, 'minuman']))->assertRedirect();
        $this->assertSame('done', $order->fresh()->status);
    }
}
