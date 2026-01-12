<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use Tests\TestCase;

class InventoryUpdateTest extends TestCase
{
    public function test_inventory_update_authorization()
    {
        $user = User::find(1);
        if (!$user) {
            $this->markTestSkipped('User 1 not found');
        }

        $item = InventoryItem::where('user_id', $user->id)->first();
        if (!$item) {
            $this->markTestSkipped('No item found for user 1');
        }

        echo "\nTesting update for Item ID: {$item->id} (User ID: {$item->user_id}) as User ID: {$user->id}\n";

        $this->actingAs($user);

        $payload = [
            'name' => $item->name,
            'category' => $item->category,
            'unit' => $item->unit,
            'expiry_date' => '2025-12-31',
            'current_stock' => $item->current_stock,
            'minimum_stock' => $item->minimum_stock,
        ];

        $response = $this->putJson("/api/inventory/{$item->id}", $payload);

        $response->dump();

        // Check specifically for the authorization failure
        if ($response->status() === 403) {
            echo "\nFAILED: 403 Unauthorized. Message: " . $response->json('message') . "\n";
        }

        $response->assertStatus(200);
    }
}
