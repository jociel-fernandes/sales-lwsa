<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sellerName;
    public $email;
    public $link;

    /**
     * Create a new message instance.
     */
    public function __construct(string $sellerName, string $email, string $link)
    {
        $this->sellerName = $sellerName;
        $this->email = $email;
        $this->link = $link;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Convite para acessar o sistema')
            ->view('emails.seller_credentials')
            ->with([
                'name' => $this->sellerName,
                'email' => $this->email,
                'link' => $this->link,
            ]);
    }
}
