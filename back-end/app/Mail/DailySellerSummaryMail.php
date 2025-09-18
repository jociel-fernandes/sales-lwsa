<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailySellerSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sellerName;
    public $date;
    public $count;
    public $totalValue;
    public $totalCommission;

    public function __construct($sellerName, $date, $count, $totalValue, $totalCommission)
    {
        $this->sellerName = $sellerName;
        $this->date = $date;
        $this->count = $count;
        $this->totalValue = $totalValue;
        $this->totalCommission = $totalCommission;
    }

    public function build()
    {
        return $this->subject("Resumo diário de vendas - {$this->date}")
            ->view('emails.daily_seller_summary')
            ->with([
                'sellerName' => $this->sellerName,
                'date' => $this->date,
                'count' => $this->count,
                'totalValue' => $this->totalValue,
                'totalCommission' => $this->totalCommission,
            ]);
    }
}
