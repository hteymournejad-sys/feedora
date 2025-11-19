<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateRemainingDays implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // دریافت همه کاربرانی که پرداخت فعال دارند
        $users = User::whereHas('payments', function ($query) {
            $query->where('status', 'completed');
        })->get();

        foreach ($users as $user) {
            // پیدا کردن آخرین پرداخت موفق
            $latestPayment = $user->payments()
                ->where('status', 'completed')
                ->latest('payment_date')
                ->first();

            if ($latestPayment) {
                $startDate = Carbon::parse($latestPayment->start_date);
                $durationDays = $latestPayment->duration_days;
                $endDate = $startDate->copy()->addDays($durationDays);
                $today = Carbon::today();

                if ($today->lessThanOrEqualTo($endDate)) {
                    // محاسبه روزهای باقیمانده
                    $remainingDays = $today->diffInDays($endDate);
                } else {
                    // اگر تاریخ اعتبار تمام شده، روزهای باقیمانده صفر است
                    $remainingDays = 0;
                }

                // به‌روزرسانی مقدار در جدول users
                $user->update([
                    'remaining_days' => $remainingDays,
                ]);
            } else {
                // اگر پرداخت موفقی وجود نداشت، روزهای باقیمانده صفر است
                $user->update([
                    'remaining_days' => 0,
                ]);
            }
        }
    }
}