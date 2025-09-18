<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService
{
    public function getCommissionPercent(): float
    {
        $val = null;
        $setting = Setting::where('input', 'percent_commission')->first();
        if ($setting && is_numeric($setting->value)) {
            $val = (string)$setting->value;
        } else {
            $val = (string)config('services.sales.percent_commission', '0');
        }
        $p = trim((string)$val);
        if ($p === '' || !is_numeric($p)) return 0.0;
        $f = (float)$p;
        return $f > 1 ? $f / 100.0 : $f;
    }
}
