<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BrevoEmailService
{
    protected $apiKey;
    protected $senderEmail;
    protected $senderName;

    public function __construct()
    {
        $this->apiKey = env('BREVO_API_KEY');
        $this->senderEmail = env('MAIL_FROM_ADDRESS');
        $this->senderName = env('MAIL_FROM_NAME', 'Signix');
    }

    public function sendEmail($toEmail, $subject, $htmlContent, $toName = null)
    {
        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => $this->senderName,
                'email' => $this->senderEmail,
            ],
            'to' => [[
                'email' => $toEmail,
                'name' => $toName ?? '',
            ]],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ]);

        return $response->successful();
    }

    public function sendOtp($toEmail, $otp)
    {
        $subject = 'Kode OTP Anda';
        $htmlContent = "<h3>OTP Anda: <strong>{$otp}</strong></h3><p>Gunakan kode ini untuk verifikasi akun Anda.</p>";
        return $this->sendEmail($toEmail, $subject, $htmlContent);
    }
}