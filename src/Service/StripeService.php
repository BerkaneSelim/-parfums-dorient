<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
    private string $secretKey;
    private string $publicKey;

    public function __construct(string $secretKey, string $publicKey)
    {
        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;
        Stripe::setApiKey($this->secretKey);
    }

    public function createPaymentIntent(int $amount, string $currency = 'eur'): PaymentIntent
    {
        return PaymentIntent::create([
            'amount'   => $amount * 100, // Stripe travaille en centimes
            'currency' => $currency,
            'payment_method_types' => ['card'],
        ]);
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }
}