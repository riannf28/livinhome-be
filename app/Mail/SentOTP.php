<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;

class SentOTP extends Mailable
{
    use Queueable, SerializesModels;

    public $email;

    /**
     * Create a new message instance.
     */
    public function __construct(Request $request)
    {
        $this->email = $request;
    }

    public function build()
    {
        $recipient_email = env('RECIPIENT_EMAIL');
        // dd($this->email);

        return $this->from($recipient_email)
            ->subject('[LivinHome] Verifikasi Akun: Kode OTP Anda, Jangan Berbagi dengan Siapapun!')
            ->view('email-otp')
            ->to('info@livinhome.com')
            ->replyTo($this->email->email)
            ->with(
                [
                    'name' => $this->email->name,
                    'content' => $this->email->message,
                    'date' => $this->email->date
                ]
            );
    }
}
