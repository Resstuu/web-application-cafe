<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_login_and_access_role_protected_api_with_sanctum_token(): void
    {
        User::factory()->create([
            'name' => 'Kasir Cafe',
            'email' => 'kasir@cafe.test',
            'password' => 'password',
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'kasir@cafe.test',
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('user.role', 'kasir');

        $token = $login->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'kasir@cafe.test');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/cashier/orders')
            ->assertOk();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/admin/users')
            ->assertForbidden();
    }

    public function test_public_menu_and_checkout_api_work_without_login(): void
    {
        $menu = Menu::create([
            'name' => 'Espresso',
            'category' => 'minuman',
            'price' => 15000,
            'is_active' => true,
        ]);

        $this->getJson('/api/menus?category=minuman')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Espresso');

        $this->postJson('/api/customer/checkout', [
            'customer_name' => 'Restu',
            'table_number' => '9',
            'items' => [
                ['menu_id' => $menu->id, 'qty' => 2],
            ],
        ])->assertCreated()
            ->assertJsonPath('order.status', 'pending_payment')
            ->assertJsonPath('demo_payment', true);
    }
}
