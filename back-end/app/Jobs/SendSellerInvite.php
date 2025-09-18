<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\SellerCredentialsMail;
use Illuminate\Support\Facades\Mail;

class SendSellerInvite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $name;
    public string $email;
    public string $link;

    /**
     * Create a new job instance.
     */
    public function __construct(string $name, string $email, string $link)
    {
        $this->name = $name;
        $this->email = $email;
        $this->link = $link;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Mail::to($this->email)->send(new SellerCredentialsMail($this->name, $this->email, $this->link));
    }
}
