<?php

namespace App\Console\Commands;

use App\Models\RiceOrder;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AutoConfirmOrders extends Command
{
    protected $signature = 'orders:auto-confirm';

    protected $description = 'Auto-confirm shipped orders that have passed the 7-day waiting period';

    public function handle()
    {
        $ordersToConfirm = RiceOrder::where('status', RiceOrder::STATUS_SHIPPED)
            ->whereNotNull('auto_confirm_at')
            ->where('auto_confirm_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($ordersToConfirm as $order) {
            $order->update([
                'status' => RiceOrder::STATUS_DELIVERED,
                'actual_delivery_date' => now(),
            ]);
            $count++;
        }

        $this->info("Auto-confirmed {$count} orders.");

        return 0;
    }
}
