<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\AirtimePayment;
use App\Models\InternetPayment;
use App\Models\PaymentSchedule;
use App\Models\InternetProvider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExpiryNotificationMail;

class NotificationService
{
    protected $daysThreshold = 3; // Notify when 3 days or less remaining

    // Check for all expiring items
    public function checkAllExpiries()
    {
        $results = [
            'airtime' => $this->checkExpiringAirtime(),
            'internet' => $this->checkExpiringInternet(),
            'overdue_payments' => $this->checkOverdueInternetPayments(),
            'upcoming_schedules' => $this->checkUpcomingSchedules()
        ];

        return $results;
    }

    // Check for expiring airtime payments
    public function checkExpiringAirtime()
    {
        $today = Carbon::today();

        $expiringPayments = AirtimePayment::where('status', 'active')
            ->whereDate('expected_expiry', '<=', $today->copy()->addDays($this->daysThreshold))
            ->whereDate('expected_expiry', '>=', $today)
            ->with('station')
            ->get();

        foreach ($expiringPayments as $payment) {
            $daysRemaining = $today->diffInDays(Carbon::parse($payment->expected_expiry), false);

            if ($daysRemaining <= $this->daysThreshold && $daysRemaining >= 0) {
                $this->createExpiryNotification(
                    $payment,
                    'airtime_expiry',
                    $daysRemaining
                );
            }
        }

        return $expiringPayments;
    }

    // Check for upcoming internet payment due dates
    public function checkExpiringInternet()
    {
        $today = Carbon::today();

        $expiringInternet = InternetPayment::where('status', 'pending')
            ->whereDate('due_date', '<=', $today->copy()->addDays($this->daysThreshold))
            ->whereDate('due_date', '>=', $today)
            ->with(['station', 'vendor'])
            ->get();

        foreach ($payment as $payment) {
            $daysRemaining = $today->diffInDays(Carbon::parse($payment->due_date), false);

            if ($daysRemaining <= $this->daysThreshold && $daysRemaining >= 0) {
                $this->createExpiryNotification(
                    $payment,
                    'internet_due',
                    $daysRemaining
                );
            }
        }

        return $expiringInternet;
    }

    // Check for overdue internet payments
    public function checkOverdueInternetPayments()
    {
        $today = Carbon::today();

        $overduePayments = InternetPayment::where('status', 'pending')
            ->whereDate('due_date', '<', $today)
            ->with(['station', 'vendor'])
            ->get();

        foreach ($payment as $payment) {
            $daysOverdue = $today->diffInDays(Carbon::parse($payment->due_date), false) * -1;

            $this->createExpiryNotification(
                $payment,
                'internet_overdue',
                $daysOverdue * -1 // Negative to show overdue
            );
        }

        return $overduePayments;
    }

    // Check upcoming payment schedules
    public function checkUpcomingSchedules()
    {
        $today = Carbon::today();

        $upcomingSchedules = PaymentSchedule::where('status', 'scheduled')
            ->whereDate('scheduled_date', '<=', $today->copy()->addDays($this->daysThreshold))
            ->whereDate('scheduled_date', '>=', $today)
            ->with(['station', 'vendor'])
            ->get();

        foreach ($schedule as $schedule) {
            $daysRemaining = $today->diffInDays(Carbon::parse($schedule->scheduled_date), false);

            if ($daysRemaining <= $this->daysThreshold && $daysRemaining >= 0) {
                $this->createExpiryNotification(
                    $schedule,
                    'payment_schedule',
                    $daysRemaining
                );
            }
        }

        return $upcomingSchedules;
    }

    // Create notification
    private function createExpiryNotification($item, $type, $daysRemaining)
    {
        // Get admin users who should receive notifications
        $adminUsers = User::where('role', 'admin')->get();

        foreach ($adminUsers as $user) {
            // Check if notification already exists
            $existingNotification = Notification::where('user_id', $user->id)
                ->where('type', $type)
                ->where('related_id', $item->id)
                ->where('related_type', get_class($item))
                ->whereDate('created_at', Carbon::today())
                ->first();

            if (!$existingNotification) {
                $message = $this->generateMessage($type, $item, $daysRemaining);

                $notification = Notification::create([
                    'type' => $type,
                    'message' => $message,
                    'user_id' => $user->id,
                    'related_id' => $item->id,
                    'related_type' => get_class($item),
                    'metadata' => $this->getMetadata($type, $item, $daysRemaining),
                    'channel' => $user->notification_preferences ?? 'system,email'
                ]);

                // Send notifications based on channel
                $this->sendNotification($notification, $user);
            }
        }
    }

    // Generate message based on type
    private function generateMessage($type, $item, $daysRemaining)
    {
        $stationName = $item->station->name ?? 'Unknown Station';

        switch ($type) {
            case 'airtime_expiry':
                $mobile = $item->mobile_number ?? 'N/A';
                return "📱 Airtime for {$stationName} ({$mobile}) expires in {$daysRemaining} day(s)";

            case 'internet_due':
                $vendorName = $item->vendor->name ?? 'Unknown Provider';
                $amount = number_format($item->amount, 2);
                return "🌐 Internet bill for {$stationName} ({$vendorName}) due in {$daysRemaining} day(s). Amount: KES {$amount}";

            case 'internet_overdue':
                $vendorName = $item->vendor->name ?? 'Unknown Provider';
                $amount = number_format($item->amount, 2);
                $overdueDays = abs($daysRemaining);
                return "⚠️ Internet bill for {$stationName} ({$vendorName}) is {$overdueDays} day(s) overdue! Amount: KES {$amount}";

            case 'payment_schedule':
                $paymentType = $item->payment_type ?? 'payment';
                $amount = number_format($item->scheduled_amount, 2);
                return "📅 Scheduled {$paymentType} payment for {$stationName} in {$daysRemaining} day(s). Amount: KES {$amount}";

            default:
                return "Item expires in {$daysRemaining} day(s)";
        }
    }

    // Get metadata for notifications
    private function getMetadata($type, $item, $daysRemaining)
    {
        $metadata = [
            'days_remaining' => $daysRemaining,
            'station_id' => $item->station_id,
            'station_name' => $item->station->name ?? 'N/A',
        ];

        switch ($type) {
            case 'airtime_expiry':
                $metadata['expiry_date'] = $item->expected_expiry;
                $metadata['mobile_number'] = $item->mobile_number;
                $metadata['network_provider'] = $item->network_provider;
                $metadata['amount'] = $item->amount;
                break;

            case 'internet_due':
            case 'internet_overdue':
                $metadata['due_date'] = $item->due_date;
                $metadata['billing_month'] = $item->billing_month;
                $metadata['vendor_id'] = $item->vendor_id;
                $metadata['vendor_name'] = $item->vendor->name ?? 'N/A';
                $metadata['account_number'] = $item->account_number;
                $metadata['amount'] = $item->amount;
                $metadata['total_due'] = $item->total_due;
                break;

            case 'payment_schedule':
                $metadata['scheduled_date'] = $item->scheduled_date;
                $metadata['scheduled_amount'] = $item->scheduled_amount;
                $metadata['payment_type'] = $item->payment_type;
                $metadata['frequency'] = $item->frequency;
                if ($item->vendor_id) {
                    $metadata['vendor_name'] = $item->vendor->name ?? 'N/A';
                }
                break;
        }

        return $metadata;
    }

    // Send notification through different channels
    private function sendNotification(Notification $notification, User $user)
    {
        $channels = explode(',', $notification->channel);

        foreach ($channels as $channel) {
            switch (trim($channel)) {
                case 'email':
                    $this->sendEmailNotification($notification, $user);
                    break;

                case 'sms':
                    $this->sendSmsNotification($notification, $user);
                    break;

                case 'system':
                    // System notifications are stored in DB by default
                    break;
            }
        }

        $notification->markAsSent();
    }

    // Send email notification
    private function sendEmailNotification(Notification $notification, User $user)
    {
        try {
            Mail::to($user->email)->send(new ExpiryNotificationMail($notification));
        } catch (\Exception $e) {
            Log::error('Failed to send email notification: ' . $e->getMessage());
        }
    }

    // Send SMS notification
    private function sendSmsNotification(Notification $notification, User $user)
    {
        if (!$user->phone) {
            return;
        }

        try {
            // Format phone number
            $phone = $this->formatPhoneNumber($user->phone);

            // For Kenya, you might use AfricasTalking or other providers
            // Example: $this->sendViaAfricasTalking($phone, $notification->message);

            // For now, log it
            Log::info("SMS Notification to {$phone}: {$notification->message}");

        } catch (\Exception $e) {
            Log::error('Failed to send SMS notification: ' . $e->getMessage());
        }
    }

    // Format phone number
    private function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // If it starts with 0, convert to +254
        if (strlen($phone) == 10 && $phone[0] == '0') {
            $phone = '+254' . substr($phone, 1);
        }
        // If it's 9 digits without country code, add +254
        elseif (strlen($phone) == 9) {
            $phone = '+254' . $phone;
        }
        // If it starts with 254, add +
        elseif (strlen($phone) == 12 && substr($phone, 0, 3) == '254') {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    // Get unread notifications count for a user
    public function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->count();
    }

    // Get notifications for a user with related data
    public function getUserNotifications($userId, $limit = 10)
    {
        return Notification::where('user_id', $userId)
            ->with(['related.station', 'related.vendor'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    // Get dashboard statistics
    public function getDashboardStats()
    {
        $today = Carbon::today();

        $stats = [
            'airtime_expiring_soon' => AirtimePayment::where('status', 'active')
                ->whereDate('expected_expiry', '<=', $today->copy()->addDays($this->daysThreshold))
                ->whereDate('expected_expiry', '>=', $today)
                ->count(),

            'internet_due_soon' => InternetPayment::where('status', 'pending')
                ->whereDate('due_date', '<=', $today->copy()->addDays($this->daysThreshold))
                ->whereDate('due_date', '>=', $today)
                ->count(),

            'internet_overdue' => InternetPayment::where('status', 'pending')
                ->whereDate('due_date', '<', $today)
                ->count(),

            'upcoming_schedules' => PaymentSchedule::where('status', 'scheduled')
                ->whereDate('scheduled_date', '<=', $today->copy()->addDays($this->daysThreshold))
                ->whereDate('scheduled_date', '>=', $today)
                ->count(),
        ];

        $stats['total_notifications'] = array_sum($stats);

        return $stats;
    }
}
