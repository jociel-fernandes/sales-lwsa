<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Seller;
use App\Models\Sale;

class SalesTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Ensure roles exist (Spatie)
        if (method_exists($this, 'artisan')) {
            // ignore errors if roles already exist
            try {
                $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RoleAndPermissionSeeder']);
            } catch (\Throwable $e) {
            }
        }
    }

    public function test_admin_can_create_update_and_delete_sale()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $sellerUser = User::factory()->create();
        $seller = Seller::create(['name' => 'Seller A', 'email' => $sellerUser->email, 'user_id' => $sellerUser->id]);

        $this->actingAs($admin, 'sanctum');

        // create
        $resp = $this->postJson('/api/sales', [
            'seller_id' => $seller->id,
            'date' => now()->toDateString(),
            'value' => 100.50,
        ]);
        $resp->assertStatus(201);
        $saleId = $resp->json('id');

        // update
        $this->putJson("/api/sales/{$saleId}", [
            'seller_id' => $seller->id,
            'date' => now()->toDateString(),
            'value' => 200.00,
    ])->assertStatus(200)->assertJsonFragment(['value' => 200]);

        // delete
        $this->deleteJson("/api/sales/{$saleId}")->assertStatus(200);
    }

    public function test_seller_can_create_own_sale_but_not_other()
    {
        $sellerUser = User::factory()->create();
        $sellerUser->assignRole('sellers');
        $seller = Seller::create(['name' => 'Seller B', 'email' => $sellerUser->email, 'user_id' => $sellerUser->id]);

        $otherSellerUser = User::factory()->create();
        $otherSeller = Seller::create(['name' => 'Seller C', 'email' => $otherSellerUser->email, 'user_id' => $otherSellerUser->id]);

        $this->actingAs($sellerUser, 'sanctum');

        // create without sending seller_id
        $resp = $this->postJson('/api/sales', [
            'date' => now()->toDateString(),
            'value' => 55.25,
        ]);
        $resp->assertStatus(201);
        $saleId = $resp->json('id');
        $this->assertDatabaseHas('sales', ['id' => $saleId, 'seller_id' => $seller->id]);

        // attempt to update a sale of other seller
        $otherSale = Sale::create(['seller_id' => $otherSeller->id, 'date' => now()->toDateString(), 'value' => 10]);
        $this->putJson("/api/sales/{$otherSale->id}", [
            'date' => now()->toDateString(), 'value' => 11,
        ])->assertStatus(403);
    }

    public function test_guest_cannot_access_sales()
    {
        $this->getJson('/api/sales')->assertStatus(401);
    }

    public function test_commission_is_calculated_and_returned()
    {
        // set commission percent to 0.10 (10%)
        \Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
            ['input' => 'percent_commission'],
            ['value' => '0.10', 'label' => 'Percent Commission']
        );

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $sellerUser = User::factory()->create();
        $seller = Seller::create(['name' => 'Seller D', 'email' => $sellerUser->email, 'user_id' => $sellerUser->id]);

        $this->actingAs($admin, 'sanctum');

        // create a sale with value 100.00
        $resp = $this->postJson('/api/sales', [
            'seller_id' => $seller->id,
            'date' => now()->toDateString(),
            'value' => 100.00,
        ]);
        $resp->assertStatus(201);
        $saleId = $resp->json('id');

        // listing should include commission and formatted
        $list = $this->getJson('/api/sales');
        $list->assertStatus(200);
    $list->assertJsonPath('data.0.commission', 10);
        $list->assertJsonPath('data.0.commission_formatted', 'R$ 10,00');

        // show should include commission
        $show = $this->getJson("/api/sales/{$saleId}");
        $show->assertStatus(200);
    $show->assertJsonPath('commission', 10);
        $show->assertJsonPath('commission_formatted', 'R$ 10,00');
    }
}
