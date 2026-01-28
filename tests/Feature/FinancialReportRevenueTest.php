<?php

namespace Tests\Feature;

use App\Models\RiceOrder;
use App\Models\RiceProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Carbon\Carbon;

class FinancialReportRevenueTest extends TestCase
{
    use DatabaseTransactions;

    public function test_financial_report_includes_revenue_from_paid_orders()
    {
        // 1. Setup Data
        $farmer = User::factory()->create(['role' => 'farmer']);

        // Create a product
        $product = RiceProduct::factory()->create([
            'farmer_id' => $farmer->id,
            'price_per_unit' => 100,
        ]);

        // Create a PAID order
        $paidOrder = RiceOrder::create([
            'buyer_id' => User::factory()->create()->id,
            'rice_product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 100,
            'total_amount' => 1000,
            'status' => 'confirmed',
            'payment_status' => RiceOrder::PAYMENT_PAID, // Key: PAID
            'payment_method' => 'cash',
            'order_date' => now(),
            'delivery_address' => ['address' => 'test'],
            'delivery_method' => 'pickup',
        ]);

        // Create an UNPAID order (should NOT be counted)
        $unpaidOrder = RiceOrder::create([
            'buyer_id' => User::factory()->create()->id,
            'rice_product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 100,
            'total_amount' => 500,
            'status' => 'confirmed',
            'payment_status' => RiceOrder::PAYMENT_PENDING, // Key: PENDING
            'payment_method' => 'cash',
            'order_date' => now(),
            'delivery_address' => ['address' => 'test'],
            'delivery_method' => 'pickup',
        ]);

        // 2. Act: Get Financial Report
        $response = $this->actingAs($farmer)->getJson('/api/reports/financial?period=30');

        // 3. Assert
        $response->assertStatus(200);

        // Revenue should reflect ONLY the paid order (1000)
        // If it counted both, it would be 1500. If it counted neither, 0.
        $this->assertEquals(1000, $response->json('data.financial_summary.total_revenue'), "Revenue should equal total of paid orders only.");
    }
}
