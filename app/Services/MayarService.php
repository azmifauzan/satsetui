<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Mayar Payment Gateway Service
 *
 * Handles communication with Mayar Headless API v1.
 * Documentation: https://docs.mayar.id/api-reference/introduction
 * Base URL (production): https://api.mayar.id/hl/v1
 */
class MayarService
{
    private string $baseUrl;

    private string $apiKey;

    private string $webhookSecret;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.mayar.base_url', 'https://api.mayar.id/hl/v1'), '/');
        $this->apiKey = config('services.mayar.api_key', '');
        $this->webhookSecret = config('services.mayar.webhook_secret', '');
    }

    /**
     * Create an invoice payment link for a top-up transaction.
     *
     * Uses the Mayar Headless API /invoice/create endpoint which accepts
     * line items directly without requiring pre-created products.
     *
     * @param  array{name: string, email: string, mobile: string, amount: int, description: string, reference_id: string, redirect_url: string}  $params
     * @return array{id: string, transaction_id: string, link: string}
     *
     * @throws RuntimeException
     */
    public function createPaymentLink(array $params): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post("{$this->baseUrl}/invoice/create", [
                    'name' => $params['name'],
                    'email' => $params['email'],
                    'mobile' => $params['mobile'] ?? '08000000000',
                    'description' => $params['description'],
                    'redirectUrl' => $params['redirect_url'],
                    'items' => [
                        [
                            'description' => $params['description'],
                            'quantity' => 1,
                            'rate' => $params['amount'],
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Mayar invoice creation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'params' => array_merge($params, ['email' => '[redacted]']),
                ]);

                $errorMsg = $response->json('messages') ?? $response->json('message') ?? 'Unknown error';

                throw new RuntimeException("Gagal membuat payment link: {$errorMsg}");
            }

            $data = $response->json('data') ?? $response->json();

            return [
                'id' => $data['id'],
                'transaction_id' => $data['transactionId'] ?? $data['id'],
                'link' => $data['link'],
            ];
        } catch (ConnectionException $e) {
            Log::error('Mayar API connection error', ['error' => $e->getMessage()]);
            throw new RuntimeException('Tidak dapat terhubung ke Mayar. Coba lagi nanti.');
        }
    }

    /**
     * Verify a webhook payload signature from Mayar.
     *
     * Mayar signs webhook payloads using HMAC-SHA256 and sends the signature
     * in the X-Mayar-Signature header. Format: "sha256=<hash>".
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            // If no secret configured, skip verification (not recommended for production)
            Log::warning('Mayar webhook secret not configured.');

            return true;
        }

        $expected = 'sha256='.hash_hmac('sha256', $payload, $this->webhookSecret);

        return hash_equals($expected, $signature);
    }
}
