<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Resources\SaleResource;
use App\Models\Sale;
use App\Models\Seller;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SaleResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_transforms_with_commission(): void
    {
        $seller = Seller::create(['name' => 'A', 'email' => 'a@example.com']);
        Setting::create(['input' => 'percent_commission', 'label' => 'Commission', 'value' => '20']);
        $sale = Sale::create(['seller_id' => $seller->id, 'date' => date('Y-m-d'), 'value' => 50]);
        $arr = (new SaleResource($sale->fresh()))->toArray(request());
        $this->assertEquals(10.0, $arr['commission']);
        $this->assertEquals('R$ 10,00', $arr['commission_formatted']);
    }
}
