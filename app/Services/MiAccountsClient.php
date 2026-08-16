<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Throwable;

class MiAccountsClient
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => rtrim(config('miaccounts.base_url'), '/') . '/',
            'timeout' => (float) config('miaccounts.timeout'),
            'http_errors' => false,
        ]);
    }

    public function enabled(): bool
    {
        return (bool) config('miaccounts.enabled') && config('miaccounts.api_key') !== '';
    }

    /**
     * POST a payload to MiAccounts. Never throws — a down ERP must not break checkout.
     */
    public function post(string $path, array $payload): array
    {
        if (! $this->enabled()) {
            return $this->result(false, null, 'MiAccounts integration disabled');
        }

        try {
            $response = $this->client->post(ltrim($path, '/'), [
                'headers' => [
                    'X-Api-Key' => config('miaccounts.api_key'),
                    'Accept' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $status = $response->getStatusCode();
            $body = json_decode((string) $response->getBody(), true);

            if ($status < 200 || $status >= 300) {
                Log::warning('MiAccounts push failed', ['path' => $path, 'status' => $status, 'body' => $body]);

                return $this->result(false, $body, 'HTTP ' . $status, $status);
            }

            return $this->result(true, $body, null, $status);
        } catch (GuzzleException | Throwable $e) {
            Log::error('MiAccounts push error', ['path' => $path, 'error' => $e->getMessage()]);

            return $this->result(false, null, $e->getMessage());
        }
    }

    public function ping(): array
    {
        return $this->post('api/third-party-hub/consumer/ping', []);
    }

    protected function result(bool $ok, ?array $body, ?string $error, ?int $status = null): array
    {
        return [
            'ok' => $ok,
            'status' => $status,
            'body' => $body,
            'error' => $error,
        ];
    }
}
