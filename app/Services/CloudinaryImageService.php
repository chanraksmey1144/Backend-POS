<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CloudinaryImageService
{
    /**
     * @return array{url: string, public_id: string}
     */
    public function upload(UploadedFile $image): array
    {
        $credentials = $this->credentials();
        $timestamp = time();
        $parameters = [
            'folder' => 'products',
            'timestamp' => $timestamp,
        ];

        $response = Http::asMultipart()
            ->attach('file', file_get_contents($image->getRealPath()), $image->getClientOriginalName())
            ->post($this->endpoint($credentials['cloud_name'], 'upload'), [
                ...$parameters,
                'api_key' => $credentials['api_key'],
                'signature' => $this->signature($parameters, $credentials['api_secret']),
            ])
            ->throw()
            ->json();

        if (empty($response['secure_url']) || empty($response['public_id'])) {
            throw new RuntimeException('Cloudinary did not return the uploaded image details.');
        }

        return [
            'url' => $response['secure_url'],
            'public_id' => $response['public_id'],
        ];
    }

    public function delete(?string $publicId): void
    {
        if (!$publicId) {
            return;
        }

        try {
            $credentials = $this->credentials();
            $parameters = [
                'invalidate' => 'true',
                'public_id' => $publicId,
                'timestamp' => time(),
            ];

            $response = Http::asForm()
                ->post($this->endpoint($credentials['cloud_name'], 'destroy'), [
                    ...$parameters,
                    'api_key' => $credentials['api_key'],
                    'signature' => $this->signature($parameters, $credentials['api_secret']),
                ]);

            if (!$response->successful() || $response->json('result') !== 'ok') {
                Log::warning('Cloudinary product image deletion failed.', [
                    'public_id' => $publicId,
                    'status' => $response->status(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Cloudinary product image deletion failed.', [
                'public_id' => $publicId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return array{cloud_name: string, api_key: string, api_secret: string}
     */
    private function credentials(): array
    {
        $credentials = [
            'cloud_name' => (string) config('cloudinary.cloud_name'),
            'api_key' => (string) config('cloudinary.api_key'),
            'api_secret' => (string) config('cloudinary.api_secret'),
        ];

        if (in_array('', $credentials, true)) {
            throw new RuntimeException('Cloudinary credentials are not configured.');
        }

        return $credentials;
    }

    private function endpoint(string $cloudName, string $action): string
    {
        return "https://api.cloudinary.com/v1_1/{$cloudName}/image/{$action}";
    }

    /**
     * @param array<string, string|int> $parameters
     */
    private function signature(array $parameters, string $apiSecret): string
    {
        ksort($parameters);
        $serialized = implode('&', array_map(
            fn (string $key): string => $key.'='.$parameters[$key],
            array_keys($parameters)
        ));

        return sha1($serialized.$apiSecret);
    }
}