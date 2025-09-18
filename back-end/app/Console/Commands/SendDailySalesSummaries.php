<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SalesSummaryService;

class SendDailySalesSummaries extends Command
{
    protected $signature = 'sales:send-daily-summaries {--date=}';
    protected $description = 'Enviar resumo diário de vendas para vendedores e admin';

    public function __construct(private SalesSummaryService $summary)
    {
        parent::__construct();
    }

    public function handle()
    {
        $date = $this->option('date') ?: date('Y-m-d');

        // per seller
        $sellerIds = \App\Models\Sale::whereDate('date', $date)->distinct()->pluck('seller_id');
        foreach ($sellerIds as $sid) {
            $seller = \App\Models\Seller::find($sid);
            if ($seller) {
                $this->summary->sendSellerSummary($seller, $date);
            }
        }

        // admin summary
        $this->summary->sendAdminSummary($date);

        $this->info('Daily sales summaries processed for ' . $date);
        return 0;
    }
}
