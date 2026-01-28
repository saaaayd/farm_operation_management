<?php

namespace App\Console\Commands;

use App\Models\ScheduledReport;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled reports to users based on their configured frequency';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled reports...');

        $reports = ScheduledReport::where('is_active', true)->get();
        $processedCount = 0;

        foreach ($reports as $report) {
            if ($this->shouldSendReport($report)) {
                $this->sendReport($report);
                $processedCount++;
            }
        }

        $this->info("Processed {$reports->count()} scheduled reports. Sent {$processedCount} reports.");
    }

    /**
     * Determine if a report should be sent based on its frequency
     */
    private function shouldSendReport(ScheduledReport $report): bool
    {
        $now = Carbon::now();
        $lastSent = $report->last_sent_at ? Carbon::parse($report->last_sent_at) : null;

        // If never sent, send now
        if (!$lastSent) {
            return true;
        }

        switch ($report->frequency) {
            case 'daily':
                return $lastSent->diffInDays($now) >= 1;
            case 'weekly':
                return $lastSent->diffInDays($now) >= 7;
            case 'monthly':
                return $lastSent->diffInDays($now) >= 30;
            default:
                return false;
        }
    }

    /**
     * Generate and send the report
     */
    private function sendReport(ScheduledReport $report): void
    {
        try {
            $user = $report->user;
            $email = $report->email ?? $user->email;

            if (!$email) {
                $this->warn("No email for report ID {$report->id}");
                return;
            }

            // Generate report data based on type
            $reportData = $this->generateReportData($report);

            // Send email with report
            Mail::send('emails.scheduled-report', [
                'user' => $user,
                'report' => $report,
                'data' => $reportData,
            ], function ($message) use ($email, $report) {
                $message->to($email)
                    ->subject("Farm Report: {$this->getReportTypeName($report->report_type)}");
            });

            // Update last_sent_at
            $report->update(['last_sent_at' => now()]);

            $this->line("Sent {$report->report_type} report to {$email}");

        } catch (\Exception $e) {
            $this->error("Failed to send report ID {$report->id}: {$e->getMessage()}");
        }
    }

    /**
     * Generate report data based on type
     */
    private function generateReportData(ScheduledReport $report): array
    {
        $user = $report->user;
        $parameters = $report->parameters ?? [];
        $period = $parameters['period'] ?? 30;

        switch ($report->report_type) {
            case 'financial':
                return $this->generateFinancialData($user, $period);
            case 'crop_yield':
                return $this->generateCropYieldData($user, $period);
            case 'weather':
                return $this->generateWeatherData($user, $period);
            default:
                return ['message' => 'Unknown report type'];
        }
    }

    private function generateFinancialData(User $user, int $period): array
    {
        $startDate = now()->subDays($period);

        $revenue = $user->sales()
            ->where('sale_date', '>=', $startDate)
            ->sum('total_amount');

        $expenses = $user->expenses()
            ->where('expense_date', '>=', $startDate)
            ->sum('amount');

        return [
            'period_days' => $period,
            'total_revenue' => $revenue,
            'total_expenses' => $expenses,
            'net_profit' => $revenue - $expenses,
        ];
    }

    private function generateCropYieldData(User $user, int $period): array
    {
        $startDate = now()->subDays($period);

        $harvests = $user->harvests()
            ->where('harvest_date', '>=', $startDate)
            ->get();

        return [
            'period_days' => $period,
            'total_harvests' => $harvests->count(),
            'total_yield' => $harvests->sum('quantity'),
            'varieties' => $harvests->groupBy('planting.rice_variety_id')->count(),
        ];
    }

    private function generateWeatherData(User $user, int $period): array
    {
        // Simple weather summary
        return [
            'period_days' => $period,
            'message' => 'Weather data summary for the past ' . $period . ' days',
        ];
    }

    private function getReportTypeName(string $type): string
    {
        $names = [
            'financial' => 'Financial Summary',
            'crop_yield' => 'Crop Yield Report',
            'weather' => 'Weather Analysis',
        ];
        return $names[$type] ?? ucfirst($type);
    }
}
