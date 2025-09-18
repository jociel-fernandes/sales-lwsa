<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyAdminSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $date;
    public $totalCount;
    public $totalValue;
    public $totalCommission;

    public function __construct($date, $totalCount, $totalValue, $totalCommission)
    {
        $this->date = $date;
        $this->totalCount = $totalCount;
        $this->totalValue = $totalValue;
        $this->totalCommission = $totalCommission;
    }

    public function build()
    {
        return $this->subject("Relatório diário de vendas - {$this->date}")
            ->view('emails.daily_admin_summary')
            ->with([
                'date' => $this->date,
                'totalCount' => $this->totalCount,
                'totalValue' => $this->totalValue,
                'totalCommission' => $this->totalCommission,
            ]);
    }
}
