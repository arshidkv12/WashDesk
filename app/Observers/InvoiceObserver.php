<?php

namespace App\Observers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        $this->clearUserCache($invoice);
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        $this->clearUserCache($invoice);
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        $this->clearUserCache($invoice);
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        $this->clearUserCache($invoice);
    }

    /**
     * Clear cache for the specific user
     */
    private function clearUserCache(Invoice $invoice): void
    {
        $userId = $invoice->user_id;
        
        if ($userId) {
            // Clear all caches for this user
            $cacheKeys = [
                "dashboard_data_{$userId}",
                "current_month_stats_{$userId}",
                "last_month_stats_{$userId}",
                "recent_orders_{$userId}",
                "daily_performance_{$userId}"
            ];
            
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            
            // Log::info("Cleared dashboard cache for user {$userId} due to invoice change", [
            //     'invoice_id' => $invoice->id,
            //     'action' => request()->route()->getActionMethod() ?? 'unknown'
            // ]);
        }
    }
}