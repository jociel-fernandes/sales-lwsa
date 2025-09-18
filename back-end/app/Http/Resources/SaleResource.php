<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\SettingsService;
use App\Services\CommissionCalculator;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $settings = app(SettingsService::class);
        $calc = app(CommissionCalculator::class);
        $percent = $settings->getCommissionPercent();
        $value = (float) ($this->value ?? 0);
        $commission = $calc->compute($value, $percent);
        return [
            'id' => $this->id,
            'seller_id' => $this->seller_id,
            'seller' => $this->whenLoaded('seller'),
            'date' => $this->date,
            'value' => $value,
            'description' => $this->description,
            'commission' => $commission,
            'commission_formatted' => 'R$ ' . number_format($commission, 2, ',', '.'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
