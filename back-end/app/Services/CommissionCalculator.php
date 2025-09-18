<?php

namespace App\Services;

class CommissionCalculator
{
    public function normalizePercent(null|string|int|float $raw): float
    {
        if ($raw === null) return 0.0;
        $p = trim((string)$raw);
        if ($p === '' || !is_numeric($p)) return 0.0;
        $f = (float)$p;
        return $f > 1 ? $f / 100.0 : $f;
    }

    public function compute(float $value, null|string|int|float $rawPercent): float
    {
        $percent = $this->normalizePercent($rawPercent);
        if ($percent <= 0) return 0.0;
        if (function_exists('bcmul')) {
            $res = bcmul(number_format($value, 8, '.', ''), number_format($percent, 8, '.', ''), 8);
            return round((float)$res, 2);
        }
        return round($value * $percent, 2);
    }
}
