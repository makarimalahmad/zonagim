<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    protected string $secretKey;
    protected string $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct()
    {
        // Cast ke string supaya tidak TypeError saat secret belum di-set
        // (mis. di environment testing).
        $this->secretKey = (string) config('services.turnstile.secret_key');
    }

    /**
     * Verify a Turnstile token
     *
     * @param string $token The token from the Turnstile widget
     * @param string|null $remoteIp Optional IP address of the user
     * @return bool
     */
    public function verify(string $token, ?string $remoteIp = null): bool
    {
        // Lewati verifikasi bila Turnstile dimatikan (testing/local).
        if (! config('services.turnstile.enabled', true)) {
            return true;
        }

        if (empty($token)) {
            Log::warning('Turnstile: Empty token received');
            return false;
        }

        try {
            $data = [
                'secret' => $this->secretKey,
                'response' => $token,
            ];

            if ($remoteIp) {
                $data['remoteip'] = $remoteIp;
            }

            Log::info('Turnstile: Verifying token...', [
                'ip' => $remoteIp,
                'token_preview' => substr($token, 0, 20) . '...',
            ]);

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::asForm()->post($this->verifyUrl, $data);

            if ($response->successful()) {
                $result = $response->json();

                Log::info('Turnstile: Verification response', [
                    'success' => $result['success'] ?? false,
                    'hostname' => $result['hostname'] ?? null,
                    'challenge_ts' => $result['challenge_ts'] ?? null,
                    'error_codes' => $result['error-codes'] ?? [],
                ]);

                if (isset($result['success']) && $result['success'] === true) {
                    Log::info('Turnstile: ✅ Verification SUCCESSFUL');
                    return true;
                }

                // Log error codes for debugging
                if (isset($result['error-codes']) && !empty($result['error-codes'])) {
                    Log::warning('Turnstile: ❌ Verification FAILED', [
                        'error-codes' => $result['error-codes']
                    ]);
                }
            } else {
                Log::error('Turnstile: HTTP request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Turnstile: Exception during verification', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return false;
    }
}
