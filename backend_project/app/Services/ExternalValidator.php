<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalValidator
{
    protected ?string $url;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->url = config('services.external_validator.url') ?: env('EXTERNAL_VALIDATION_URL');
        $this->apiKey = config('services.external_validator.api_key') ?: env('EXTERNAL_VALIDATION_API_KEY');
    }

    /**
     * Validate applicant data using external API.
     * Returns ['valid' => bool, 'errors' => array, 'raw' => mixed]
     */
    public function validateApplicant(array $applicant): array
    {
        if (!$this->url) {
            return ['valid' => true, 'errors' => [], 'raw' => null, 'note' => 'no-external-validator-configured'];
        }

        try {
            $resp = Http::timeout(5)
                ->withHeaders(array_filter([
                    'Accept' => 'application/json',
                    'Authorization' => $this->apiKey ? 'Bearer ' . $this->apiKey : null,
                ]))
                ->post($this->url, $applicant);

            if ($resp->successful()) {
                $body = $resp->json();
                // Expecting { valid: bool, errors: [...] }
                return [
                    'valid' => $body['valid'] ?? true,
                    'errors' => $body['errors'] ?? [],
                    'raw' => $body,
                ];
            }

            // non-2xx
            Log::warning('ExternalValidator non-success response', ['status' => $resp->status(), 'body' => $resp->body()]);
            return ['valid' => true, 'errors' => [], 'raw' => $resp->body(), 'note' => 'external-unavailable'];
        } catch (\Throwable $e) {
            Log::error('ExternalValidator exception', ['error' => $e->getMessage()]);
            return ['valid' => true, 'errors' => [], 'raw' => null, 'note' => 'external-exception', 'exception' => $e->getMessage()];
        }
    }
}
