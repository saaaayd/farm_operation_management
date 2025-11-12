<?php

namespace App\Console\Commands;

use App\Models\RiceOrder;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendPreOrderNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pre-orders:send-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS notifications for pre-orders (when available and day before pickup)';

    /**
     * Execute the console command.
     */
    public function handle(SmsService $smsService)
    {
        $this->info('Starting pre-order notification process...');

        // Get pre-orders that need notifications
        $preOrders = RiceOrder::where('is_pre_order', true)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['buyer', 'riceProduct'])
            ->get();

        $availableNotificationsSent = 0;
        $dayBeforeNotificationsSent = 0;

        foreach ($preOrders as $order) {
            try {
                // Check if product is now available
                if ($order->riceProduct->production_status === 'available' && !$order->notification_sent_available) {
                    // Product is now available, send notification
                    if ($smsService->sendPreOrderAvailableNotification($order)) {
                        $order->update(['notification_sent_available' => true]);
                        $availableNotificationsSent++;
                        $this->info("Sent available notification for order #{$order->id}");
                    }
                }

                // Check if we need to send day-before notification
                $availableDate = $order->available_date ?? $order->expected_delivery_date;
                
                if ($availableDate && !$order->notification_sent_day_before) {
                    $tomorrow = Carbon::tomorrow();
                    $availableDateCarbon = Carbon::parse($availableDate);

                    // Send notification if available date is tomorrow
                    if ($availableDateCarbon->isSameDay($tomorrow)) {
                        if ($smsService->sendDayBeforePickupNotification($order)) {
                            $order->update(['notification_sent_day_before' => true]);
                            $dayBeforeNotificationsSent++;
                            $this->info("Sent day-before notification for order #{$order->id}");
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error sending notification for pre-order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
                $this->error("Error processing order #{$order->id}: {$e->getMessage()}");
            }
        }

        $this->info("Notification process completed.");
        $this->info("Available notifications sent: {$availableNotificationsSent}");
        $this->info("Day-before notifications sent: {$dayBeforeNotificationsSent}");

        return Command::SUCCESS;
    }
}



