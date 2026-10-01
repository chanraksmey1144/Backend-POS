<?php

namespace Tests\Feature;

use App\Services\CloudinaryImageService;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CloudinaryImageServiceTest extends TestCase
{
    public function test_it_uploads_product_images_to_cloudinary(): void
    {
        config([
            'cloudinary.cloud_name' => 'test-cloud',
            'cloudinary.api_key' => 'test-key',
            'cloudinary.api_secret' => 'test-secret',
        ]);

        Http::fake([
            'api.cloudinary.com/*' => Http::response([
                'secure_url' => 'https://res.cloudinary.com/test-cloud/image/upload/products/item.png',
                'public_id' => 'products/item',
            ]),
        ]);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'cloudinary-product-');
        file_put_contents($temporaryPath, 'fake image contents');

        try {
            $result = app(CloudinaryImageService::class)->upload(
                new UploadedFile($temporaryPath, 'item.png', 'image/png', UPLOAD_ERR_OK, true)
            );
        } finally {
            unlink($temporaryPath);
        }

        $this->assertSame('https://res.cloudinary.com/test-cloud/image/upload/products/item.png', $result['url']);
        $this->assertSame('products/item', $result['public_id']);

        Http::assertSent(function (Request $request): bool {
            return str_ends_with($request->url(), '/image/upload')
                && $request->isMultipart();
        });
    }
}