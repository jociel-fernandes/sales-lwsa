<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_commission_percent_from_db_and_config(): void
    {
        // ensure config fallback is deterministic (tests may run with env override)
        Config::set('services.sales.percent_commission', null);

        // default from config fallback
        $svc = new SettingsService();
        $this->assertSame(0.0, $svc->getCommissionPercent());

        // when stored as '18' (means 18%) -> 0.18
        Setting::create(['input' => 'percent_commission', 'label' => 'Commission', 'value' => '18']);
        $this->assertSame(0.18, $svc->getCommissionPercent());

        // when stored as '0.2' -> 0.2
        Setting::query()->update(['value' => '0.2']);
        $this->assertSame(0.2, $svc->getCommissionPercent());
    }
}
