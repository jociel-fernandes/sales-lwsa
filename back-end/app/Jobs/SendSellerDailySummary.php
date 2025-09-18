<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailySellerSummaryMail;

class SendSellerDailySummary implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public $email;
    public $sellerName;
    public $date;
    public $count;
    public $totalValue;
    public $totalCommission;

    public function __construct($email, $sellerName, $date, $count, $totalValue, $totalCommission)
    {
        $this->email = $email;
        $this->sellerName = $sellerName;
        $this->date = $date;
        $this->count = $count;
        $this->totalValue = $totalValue;
        $this->totalCommission = $totalCommission;
    }

    public function handle()
    {
        try {
            Mail::to($this->email)->send(new DailySellerSummaryMail($this->sellerName, $this->date, $this->count, $this->totalValue, $this->totalCommission));
        } catch (\Throwable $e) {
        }
    }
}
