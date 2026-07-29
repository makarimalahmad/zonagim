<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TurnstileService
{
    protected string $secretKey;

    protected string $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct()
    {
        $this->secretKey = (string) config('services.turnstile.secret_key');
    }

    public function verify(string $token, ?string $remoteIp = null): bool
    {
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

            /** @var Response $response */
            $response = Http::asForm()
                ->connectTimeout(3)
                ->timeout(8)
                ->retry(
                    2,
                    100,
                    fn (Throwable $exception): bool => $exception instanceof ConnectionException
                        || ($exception instanceof RequestException
                            && $exception->response->serverError()),
                    throw: false,
                )
                ->post($this->verifyUrl, $data);

            if ($response->successful()) {
                $result = $response->json();

                $expectedHostname = config('services.turnstile.hostname');

                if (
                    ($result['success'] ?? false) === true
                    && (
                        ! is_string($expectedHostname)
                        || $expectedHostname === ''
                        || hash_equals($expectedHostname, (string) ($result['hostname'] ?? ''))
                    )
                ) {
                    return true;
                }

                if (isset($result['error-codes']) && ! empty($result['error-codes'])) {
                    Log::warning('Turnstile verification failed.', [
                        'error-codes' => $result['error-codes'],
                    ]);
                }
            } else {
                Log::error('Turnstile: HTTP request failed', [
                    'status' => $response->status(),
                ]);
            }
        } catch (Throwable $exception) {
            Log::error('Turnstile verification encountered an exception.', [
                'exception' => $exception::class,
            ]);
        }

        return false;
    }
}
