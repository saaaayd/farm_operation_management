<?php

namespace App\Console\Commands;

use App\Models\InventoryItem;
use App\Models\Notification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckInventoryExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for inventory items that are expiring soon or have expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking inventory for expiring items...');

        $items = InventoryItem::whereNotNull('expiry_date')
            ->where('current_stock', '>', 0) // Only check items that are in stock
            ->get();

        $count = 0;

        foreach ($items as $item) {
            $expiryDate = Carbon::parse($item->expiry_date);
            $now = Carbon::now();
            $daysUntilExpiry = $now->diffInDays($expiryDate, false); // false = return negative if past

            // Check if already expired or expiring soon (within 7 days)
            if ($daysUntilExpiry < 0) {
                // Expired
                $this->sendNotification(
                    $item,
                    'Expired Item Alert',
                    "Action Required: {$item->name} has expired on {$expiryDate->format('M d, Y')}.",
                    'critical'
                );
                $count++;
            } elseif ($daysUntilExpiry <= 7) {
                // Expiring soon
                $this->sendNotification(
                    $item,
                    'Expiration Warning',
                    "Warning: {$item->name} will expire in " . ceil($daysUntilExpiry) . " days ({$expiryDate->format('M d, Y')}).",
                    'warning'
                );
                $count++;
            } elseif ($daysUntilExpiry <= 30 && $daysUntilExpiry > 29) {
                // Expiring in 30 days (specifically on the 30th day mark approximately)
                // Just to have a monthly heads up
                $this->sendNotification(
                    $item,
                    'Expiration Notice',
                    "Notice: {$item->name} will expire in 30 days ({$expiryDate->format('M d, Y')}).",
                    'info'
                );
                $count++;
            }
        }

        $this->info("Processed {$items->count()} items. Sent {$count} notifications.");
    }

    private function sendNotification($item, $title, $message, $severity = 'info')
    {
        // Simple duplicate check: Don't send if a similar notification was sent today
        // This stops spam if the command runs multiple times a day
        $exists = Notification::where('user_id', $item->user_id)
            ->where('type', Notification::TYPE_EXPIRY_ALERT)
            ->where('data->inventory_item_id', $item->id)
            ->whereDate('created_at', Carbon::today())
            ->exists();

        if ($exists) {
            return;
        }

        Notification::notify(
            $item->user_id,
            Notification::TYPE_EXPIRY_ALERT,
            $title,
            $message,
            [
                'inventory_item_id' => $item->id,
                'name' => $item->name,
                'days_remaining' => Carbon::now()->diffInDays(Carbon::parse($item->expiry_date), false),
                'expiry_date' => $item->expiry_date,
                'severity' => $severity
            ],
            '/inventory/' . $item->id
        );

        $this->line("Sent notification for {$item->name}");
    }
}
