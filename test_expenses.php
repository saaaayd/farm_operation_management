<?php

use App\Models\User;
use App\Models\Planting;
use App\Models\Field;
use App\Models\Task;
use App\Models\Laborer;
use App\Models\InventoryItem;
use App\Models\Expense;
use App\Models\RiceVariety;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create a test user
$user = User::first();
if (!$user) {
    try {
        $user = User::factory()->create();
    } catch (\Exception $e) {
        $user = new User();
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = bcrypt('password');
        $user->save();
    }
}
Auth::login($user);

echo "User ID: " . $user->id . "\n";

// Test Inventory Expense
echo "\nTesting Inventory Expense...\n";
$inventoryData = [
    'name' => 'Test Seed ' . uniqid(),
    'category' => 'seeds', // Will map to CATEGORY_SEEDS expense
    'unit' => 'kg',
    'quantity' => 10,
    'unit_price' => 100,
    'user_id' => $user->id,
];

try {
    $totalCost = $inventoryData['quantity'] * $inventoryData['unit_price'];

    $item = InventoryItem::create([
        'name' => $inventoryData['name'],
        'category' => $inventoryData['category'],
        'unit' => $inventoryData['unit'],
        'current_stock' => $inventoryData['quantity'],
        'minimum_stock' => 0,
        'unit_price' => $inventoryData['unit_price'],
        'user_id' => $inventoryData['user_id'],
    ]);

    if ($totalCost > 0) {
        $expenseCategory = \App\Models\Expense::CATEGORY_SEEDS;
        $expense = Expense::create([
            'description' => "Initial Stock: {$item->name} ({$item->current_stock} {$item->unit})",
            'amount' => $totalCost,
            'category' => $expenseCategory,
            'date' => now(),
            'user_id' => $user->id,
            'payment_method' => 'cash',
            'related_entity_type' => Expense::ENTITY_TYPE_INVENTORY_ITEM,
            'related_entity_id' => $item->id,
        ]);
        echo "Inventory Expense Created: ID {$expense->id}, Amount {$expense->amount}, Category {$expense->category}\n";
    }
} catch (\Exception $e) {
    echo "Inventory Test Failed: " . $e->getMessage() . "\n";
}

// Test Labor Expense
echo "\nTesting Labor Expense...\n";
try {
    // Setup
    $field = Field::first();
    if (!$field) {
        $field = Field::factory()->create(['user_id' => $user->id]);
    }

    $planting = Planting::whereHas('field', function ($q) use ($user) {
        $q->where('user_id', $user->id);
    })->first();

    if (!$planting) {
        // Create planting manually if factory fails or doesn't exist
        $variety = RiceVariety::first();
        if (!$variety) {
            $variety = RiceVariety::create(['name' => 'Test Variety', 'days_to_maturity' => 100]);
        }

        $planting = new Planting();
        $planting->field_id = $field->id;
        $planting->crop_type = 'Rice';
        $planting->rice_variety_id = $variety->id;
        $planting->planting_date = now();
        $planting->expected_harvest_date = now()->addDays($variety->days_to_maturity);
        $planting->area_planted = $field->size ?? 1;
        $planting->season = 'dry'; // Corrected season value
        $planting->status = 'planted'; // Corrected status value
        $planting->save();
    }

    $laborer = Laborer::create([
        'name' => 'Test Laborer ' . uniqid(),
        'phone' => '1234567890',
        'rate' => 500, // 500 per day
        'rate_type' => 'per_day',
        'status' => 'active',
        'hire_date' => now(),
        'user_id' => $user->id,
        'skill_level' => 'intermediate',
        'rate_type' => 'per_day',
    ]);

    $task = Task::create([
        'planting_id' => $planting->id,
        'task_type' => Task::TYPE_WEEDING,
        'due_date' => now(),
        'assigned_to' => $laborer->id,
        'status' => Task::STATUS_PENDING,
        'payment_type' => Task::PAYMENT_TYPE_WAGE,
    ]);

    // Simulate Task Completion
    $hoursWorked = 8;
    $rate = $laborer->rate;
    $wageAmount = $rate; // per day rate

    $laborWage = \App\Models\LaborWage::create([
        'laborer_id' => $laborer->id,
        'task_id' => $task->id,
        'hours_worked' => $hoursWorked,
        'wage_amount' => $wageAmount,
        'date' => now(),
        'user_id' => $user->id,
    ]);

    $expense = Expense::create([
        'description' => "Labor: {$laborer->name} - {$task->task_type} task",
        'amount' => $wageAmount,
        'category' => Expense::CATEGORY_LABOR,
        'date' => now(),
        'user_id' => $user->id,
        'payment_method' => 'cash',
        'related_entity_type' => Expense::ENTITY_TYPE_TASK,
        'related_entity_id' => $task->id,
    ]);

    echo "Labor Expense Created: ID {$expense->id}, Amount {$expense->amount}, Category {$expense->category}\n";

} catch (\Exception $e) {
    echo "Labor Test Failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
