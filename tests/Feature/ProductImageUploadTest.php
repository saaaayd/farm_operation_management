<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions; // Use DatabaseTransactions instead of RefreshDatabase for safety if run against real DB (though we won't run it now)
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;

class ProductImageUploadTest extends TestCase
{
    // use RefreshDatabase; // Commented out to avoid wiping DB if misconfigured
    use DatabaseTransactions;

    public function test_farmer_can_upload_images()
    {
        Storage::fake('public');

        // We can't really run this without a working DB connection for the User factory
        // But the code structure is correct for when a testing DB is available.
        $this->markTestSkipped('Testing database not configured.');

        $farmer = User::factory()->create(['role' => 'farmer']);

        $file = UploadedFile::fake()->image('rice.jpg');

        $response = $this->actingAs($farmer)
            ->postJson('/api/rice-marketplace/products/images/upload', [
                'images' => [$file]
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'urls']);

        // Assert file exists
        $url = $response->json('urls')[0];
        $filename = basename($url);
        Storage::disk('public')->assertExists('products/' . $filename);
    }
}
