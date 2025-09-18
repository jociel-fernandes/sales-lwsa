<?php

namespace App\Services;

use App\Jobs\SendSellerDailySummary;
use App\Mail\DailyAdminSummaryMail;
use App\Models\Sale;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SalesSummaryService
{
    public function __construct(
        private SettingsService $settings,
        private CommissionCalculator $commissionCalc,
    ) {}

    public function computeSellerStatsForDate(Seller $seller, string $date): array
    {
        $row = Sale::select(
            DB::raw('count(*) as cnt'),
            DB::raw('sum(value) as total_value')
        )->where('seller_id', $seller->id)
         ->whereDate('date', $date)
         ->first();

        $count = (int) ($row->cnt ?? 0);
        $totalValue = (float) ($row->total_value ?? 0);
        $commission = $this->commissionCalc->compute($totalValue, $this->settings->getCommissionPercent());

        return [
            'date' => $date,
            'count' => $count,
            'total_value' => $totalValue,
            'commission' => $commission,
        ];
    }

    public function sendSellerSummary(Seller $seller, string $date, ?int $count = null, ?float $totalValue = null): bool
    {
        if (!$seller->email) return false;

        if ($count === null || $totalValue === null) {
            $stats = $this->computeSellerStatsForDate($seller, $date);
            $count = $stats['count'];
            $totalValue = $stats['total_value'];
            $commission = $stats['commission'];
        } else {
            $commission = $this->commissionCalc->compute($totalValue, $this->settings->getCommissionPercent());
        }

        $formattedDate = date('d/m/Y', strtotime($date));
        try {
            Log::info('SalesSummaryService: dispatching SendSellerDailySummary', ['email' => $seller->email, 'date' => $formattedDate]);
            SendSellerDailySummary::dispatch($seller->email, $seller->name, $formattedDate, $count, $totalValue, $commission);
            return true;
        } catch (\Throwable $e) {
            Log::error('SalesSummaryService: dispatch failed', ['err' => $e->getMessage()]);
            try {
                Log::info('SalesSummaryService: trying direct handle fallback', ['email' => $seller->email]);
                (new SendSellerDailySummary($seller->email, $seller->name, $formattedDate, $count, $totalValue, $commission))->handle();
                return true;
            } catch (\Throwable $ee) {
                Log::error('SalesSummaryService: fallback handle failed', ['err' => $ee->getMessage()]);
                return false;
            }
        }
    }

    public function computeAdminStatsForDate(string $date): array
    {
        $rows = Sale::select('seller_id', DB::raw('count(*) as cnt'), DB::raw('sum(value) as total_value'))
            ->whereDate('date', $date)
            ->groupBy('seller_id')
            ->get();

        $percent = $this->settings->getCommissionPercent();
        $totalCount = 0;
        $totalValue = 0.0;
        $totalCommission = 0.0;

        foreach ($rows as $row) {
            $c = (int) ($row->cnt ?? 0);
            $v = (float) ($row->total_value ?? 0);
            $totalCount += $c;
            $totalValue += $v;
            $totalCommission += $this->commissionCalc->compute($v, $percent);
        }

        return [
            'total_count' => $totalCount,
            'total_value' => $totalValue,
            'total_commission' => $totalCommission,
        ];
    }

    public function getAdminEmails(): array
    {
        $emails = User::role('admin')->pluck('email')->filter()->unique()->values()->all();
        if (empty($emails)) {
            $envAdmin = env('ADMIN_EMAIL') ?: config('mail.admin_address');
            if ($envAdmin) $emails = [$envAdmin];
        }
        return $emails;
    }

    public function sendAdminSummary(string $date, ?array $precomputedTotals = null): bool
    {
        $totals = $precomputedTotals ?? $this->computeAdminStatsForDate($date);
        $emails = $this->getAdminEmails();
        if (empty($emails)) return false;

        $formattedDate = date('d/m/Y', strtotime($date));
        try {
            Mail::to($emails)->send(new DailyAdminSummaryMail($formattedDate, $totals['total_count'], $totals['total_value'], $totals['total_commission']));
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
