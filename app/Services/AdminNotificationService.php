<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminNotificationService
{
    /**
     * Send notification to admin users about important events
     */
    public function sendNotification($title, $message, $type = 'info', $roles = ['Super Admin', 'Agency Owner', 'HR'])
    {
        try {
            // Get users with specified roles
            $users = User::whereHas('roles', function($query) use ($roles) {
                $query->whereIn('name', $roles);
            })->get();

            foreach ($users as $user) {
                DB::table('admin_notifications')->insert([
                    'user_id' => $user->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type, // info, success, warning, danger
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Log::info("Admin notification sent: {$title}", [
                'message' => $message,
                'type' => $type,
                'recipient_count' => $users->count()
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send admin notification: {$e->getMessage()}");
        }
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications($userId, $limit = 10)
    {
        if (!DB::getSchemaBuilder()->hasTable('admin_notifications')) {
            return collect([]);
        }

        return DB::table('admin_notifications')
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all notifications for a user (both read and unread)
     */
    public function getAllNotifications($userId, $limit = 50)
    {
        if (!DB::getSchemaBuilder()->hasTable('admin_notifications')) {
            return collect([]);
        }

        return DB::table('admin_notifications')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        if (DB::getSchemaBuilder()->hasTable('admin_notifications')) {
            DB::table('admin_notifications')
                ->where('id', $notificationId)
                ->update(['is_read' => true, 'updated_at' => now()]);
        }
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId)
    {
        if (DB::getSchemaBuilder()->hasTable('admin_notifications')) {
            DB::table('admin_notifications')
                ->where('user_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true, 'updated_at' => now()]);
        }
    }

    /**
     * Get notification count for a user
     */
    public function getUnreadCount($userId)
    {
        if (!DB::getSchemaBuilder()->hasTable('admin_notifications')) {
            return 0;
        }

        return DB::table('admin_notifications')
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Trigger specific business event notifications
     */
    public function notifyPayrollCompleted($employeeCount, $totalAmount)
    {
        $this->sendNotification(
            'Payroll Processing Completed',
            "Payroll has been successfully processed for {$employeeCount} employees. Total amount: $" . number_format($totalAmount, 2),
            'success',
            ['Super Admin', 'Agency Owner', 'HR']
        );
    }

    public function notifyAttendanceAnomaly($employeeName, $anomalyType)
    {
        $this->sendNotification(
            'Attendance Anomaly Detected',
            "Attendance anomaly detected for {$employeeName}: {$anomalyType}",
            'warning',
            ['HR', 'Agency Owner']
        );
    }

    public function notifyClientPayment($clientName, $amount, $status)
    {
        $type = $status === 'completed' ? 'success' : 'warning';
        $this->sendNotification(
            'Client Payment Update',
            "Payment from {$clientName} for $" . number_format($amount, 2) . " is now {$status}",
            $type,
            ['Super Admin', 'Agency Owner']
        );
    }

    public function notifyNewEmployee($employeeName, $clientName)
    {
        $this->sendNotification(
            'New Employee Registered',
            "New employee {$employeeName} has been registered for client {$clientName}",
            'info',
            ['HR', 'Agency Owner']
        );
    }

    public function notifyKycCompleted($employeeName)
    {
        $this->sendNotification(
            'KYC Verification Completed',
            "KYC verification has been completed for {$employeeName}",
            'success',
            ['HR', 'Agency Owner']
        );
    }

    public function notifyInvoiceGenerated($invoiceNumber, $clientName, $amount)
    {
        $this->sendNotification(
            'New Invoice Generated',
            "Invoice #{$invoiceNumber} for {$clientName} ($" . number_format($amount, 2) . ") has been generated",
            'info',
            ['Agency Owner']
        );
    }

    public function notifySystemAlert($title, $message)
    {
        $this->sendNotification(
            $title,
            $message,
            'danger',
            ['Super Admin']
        );
    }
}