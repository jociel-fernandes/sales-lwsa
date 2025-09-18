<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Seller;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class SalesApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        try {
            $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RoleAndPermissionSeeder']);
        } catch (\Throwable $e) {
        }
    }

    protected function authenticateAsAdmin(): User
    {
        $user = User::factory()->create([
            'password' => Hash::make('password')
        ]);
        // Ensure the 'admin' role exists for the default guard before assigning
        Role::findOrCreate('admin', config('auth.defaults.guard'));
        if (! $user->hasRole('admin')) {
            $user->assignRole('admin');
        }
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    public function test_index_returns_paginated_sales_with_commission(): void
    {
        $this->authenticateAsAdmin();
        $seller = Seller::create(['name' => 'John', 'email' => 'john@example.com']);
        Setting::create(['input' => 'percent_commission', 'label' => 'Commission', 'value' => '10']);
        Sale::create(['seller_id' => $seller->id, 'date' => date('Y-m-d'), 'value' => 100]);

        $res = $this->getJson('/api/sales');
        $res->assertOk();
        $json = $res->json();
        $this->assertArrayHasKey('data', $json);
        $first = $json['data'][0];
        $this->assertEquals(10.00, $first['commission']);
        $this->assertEquals('R$ 10,00', $first['commission_formatted']);
    }

    public function test_store_validates_and_creates(): void
    {
        $this->authenticateAsAdmin();
        $seller = Seller::create(['name' => 'Mary', 'email' => 'mary@example.com']);
        $payload = ['seller_id' => $seller->id, 'date' => date('Y-m-d'), 'value' => 123.45];

        $res = $this->postJson('/api/sales', $payload);
        $res->assertCreated();
        $this->assertDatabaseHas('sales', ['seller_id' => $seller->id, 'value' => 123.45]);
    }
}
