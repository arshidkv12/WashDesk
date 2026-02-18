<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->id;
        // Cache dashboard data for 1 hour (3600 seconds)
        $dashboardData = Cache::remember("dashboard_data_{$userId}", 3600, function () {
            return $this->generateDashboardData();
        });

        return Inertia::render('Dashboard', $dashboardData);
    }

    private function generateDashboardData()
    {
        
        // Current date range
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();
        $userId = Auth::user()->id;

        // Cache individual heavy queries if needed
        $currentMonthStats = Cache::remember("current_month_stats_{$userId}", 1800, function () use ($startOfMonth, $now) {
            return [
                'revenue' => Invoice::whereBetween('created_at', [$startOfMonth, $now])
                    ->sum('paid_amount'),
                
                'invoices' => Invoice::whereBetween('created_at', [$startOfMonth, $now])->count(),
                
                'customers' => Customer::whereBetween('created_at', [$startOfMonth, $now])->count(),
                
                'outstanding' => Invoice::whereBetween('created_at', [$startOfMonth, $now])
                    ->sum(DB::raw('total_amount - paid_amount')),
            ];
        });

        $lastMonthStats = Cache::remember("last_month_stats_{$userId}", 3600, function () use ($startOfLastMonth, $endOfLastMonth) {
            return [
                'revenue' => Invoice::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                    ->sum('paid_amount'),
                
                'invoices' => Invoice::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count(),
                
                'customers' => Customer::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count(),
                
                'outstanding' => Invoice::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                    ->sum(DB::raw('total_amount - paid_amount')),
            ];
        });

        $stats = [
            [
                'title' => 'Total Revenue',
                'value' => $currentMonthStats['revenue'],
                'change' => $this->calculateChange($currentMonthStats['revenue'], $lastMonthStats['revenue']),
                'trend' => $this->getTrend($currentMonthStats['revenue'], $lastMonthStats['revenue']),
                'icon' => 'CreditCard',
                'color' => 'text-emerald-600',
                'bgColor' => 'bg-emerald-100',
            ],
            [
                'title' => 'Total Invoices',
                'value' => $currentMonthStats['invoices'],
                'change' => $this->calculateChange($currentMonthStats['invoices'], $lastMonthStats['invoices']),
                'trend' => $this->getTrend($currentMonthStats['invoices'], $lastMonthStats['invoices']),
                'icon' => 'Package',
                'color' => 'text-blue-600',
                'bgColor' => 'bg-blue-100',
            ],
            [
                'title' => 'New Customers',
                'value' => $currentMonthStats['customers'],
                'change' => $this->calculateChange($currentMonthStats['customers'], $lastMonthStats['customers']),
                'trend' => $this->getTrend($currentMonthStats['customers'], $lastMonthStats['customers']),
                'icon' => 'Users',
                'color' => 'text-violet-600',
                'bgColor' => 'bg-violet-100',
            ],
            [
                'title' => 'Outstanding',
                'value' => $currentMonthStats['outstanding'],
                'change' => $this->calculateChange($currentMonthStats['outstanding'], $lastMonthStats['outstanding']),
                'trend' => $this->getTrend($currentMonthStats['outstanding'], $lastMonthStats['outstanding'], true),
                'icon' => 'ReceiptText',
                'color' => 'text-amber-600',
                'bgColor' => 'bg-amber-100',
            ],
        ];

        // Cache recent orders (short TTL since they change frequently)
        // $recentOrders = Cache::remember("recent_orders_{$userId}", 300, function () {
        //     return Invoice::with('customer')
        //         ->latest()
        //         ->limit(5)
        //         ->get()
        //         ->map(function ($invoice) {
        //             return [
        //                 'id' => $invoice->id,
        //                 'invoice' => $invoice->invoice_no ?? 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT),
        //                 'customer' => $invoice->customer->name ?? 'Guest',
        //                 'amount' => $invoice->total_amount,
        //                 'status' => $invoice->status,
        //                 'time' => $invoice->created_at->diffForHumans(),
        //                 'items' => $invoice->items()->count(),
        //             ];
        //         });
        // });

        // Cache daily performance (30 days) - changes daily, so 1 hour is fine
        $dailyPerformance = Cache::remember("daily_performance_{$userId}", 3600, function () {
            $startDate = now()->subDays(29)->startOfDay();
            $endDate = now()->endOfDay();
            
            $rawDaily = Invoice::selectRaw('DATE(created_at) as date, SUM(paid_amount) as total')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->pluck('total', 'date');

            return collect(range(29, 0))->map(function ($daysAgo) use ($rawDaily) {
                $date = now()->subDays($daysAgo)->toDateString();
                $dayNumber = now()->subDays($daysAgo)->format('d');
                $dayName = now()->subDays($daysAgo)->format('D');
                $fullDate = now()->subDays($daysAgo)->format('M d');
                
                $value = (float) ($rawDaily[$date] ?? 0);

                return [
                    'day' => $dayNumber,
                    'date' => $date,
                    'fullDate' => $fullDate,
                    'dayName' => $dayName,
                    'label' => $dayName . ' ' . $dayNumber,
                    'value' => $value / 1000,
                    'originalValue' => $value,
                ];
            })->values();
        });

        $weeklyPerformance = [
            'thisWeek' => $dailyPerformance->slice(0, 7)->sum('value'),
            'lastWeek' => $dailyPerformance->slice(7, 7)->sum('value'),
            'weekOverWeekGrowth' => $this->calculateGrowth(
                $dailyPerformance->slice(0, 7)->sum('value'),
                $dailyPerformance->slice(7, 7)->sum('value')
            ),
        ];

        // Today's stats - don't cache or cache very briefly
        $today = Carbon::today();
        $todayRevenue = Invoice::whereDate('created_at', $today)->sum('paid_amount');
        $todayAvgOrder = Invoice::whereDate('created_at', $today)->avg('paid_amount');
        
        $formatCurrency = function($amount) {
            if ($amount >= 1000000) {
                return round($amount / 1000000, 2) . 'M';
            } elseif ($amount >= 1000) {
                return round($amount / 1000, 1) . 'K';
            } else {
                return (string) number_format($amount, 2, '.', '');
            }
        };

        $quickStats = [
            'todayRevenue' => $formatCurrency($todayRevenue),
            'todayRevenueRaw' => $todayRevenue,
            'processing' => Invoice::whereDate('created_at', $today)
                ->where('status', 'processing')
                ->count(),
            'ready' => Invoice::whereDate('created_at', $today)
                ->where('status', 'ready')
                ->count(),
            'completed' => Invoice::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->count(),
            'averageOrderValue' => $todayAvgOrder ? $formatCurrency($todayAvgOrder) : '0',
            'averageOrderValueRaw' => $todayAvgOrder ?? 0,
            'busyLevel' => $this->calculateBusyLevel(),
        ];

        return [
            'stats' => $stats,
            // 'recentOrders' => $recentOrders,
            'dailyPerformance' => $dailyPerformance,
            'weeklyPerformance' => $weeklyPerformance,
            'quickStats' => $quickStats,
            'currentMonth' => now()->format('F Y'),
            'statusOptions' => InvoiceStatus::options(),
            'chartConfig' => [
                'colors' => [
                    'primary' => '#3b82f6',
                    'secondary' => '#10b981',
                    'warning' => '#f59e0b',
                    'danger' => '#ef4444',
                ],
            ],
        ];
    }

    private function calculateChange($current, $previous)
    {
        if ($previous == 0) return 100;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) return 0;
        return (($current - $previous) / $previous) * 100;
    }

    private function getTrend($current, $previous, $inverse = false)
    {
        if ($current == $previous) return 'neutral';
        
        if ($inverse) {
            return $current < $previous ? 'up' : 'down';
        }
        return $current > $previous ? 'up' : 'down';
    }

    private function calculateBusyLevel()
    {
        $totalInvoices = Invoice::whereDate('created_at', Carbon::today())->count();
        $maxCapacity = 50;
        
        return min(round(($totalInvoices / $maxCapacity) * 100), 100);
    }

    public function getQuickStats()
    {
        $today = Carbon::today();
        $todayRevenue = Invoice::whereDate('created_at', $today)->sum('paid_amount');
        
        $formatCurrency = function($amount) {
            if ($amount >= 1000000) {
                return round($amount / 1000000, 2) . 'M';
            } elseif ($amount >= 1000) {
                return round($amount / 1000, 1) . 'K';
            } else {
                return (string) $amount;
            }
        };
        
        return response()->json([
            'todayRevenue' => $formatCurrency($todayRevenue),
            'todayRevenueRaw' => $todayRevenue,
            'processing' => Invoice::whereDate('created_at', $today)
                ->where('status', 'processing')
                ->count(),
            'ready' => Invoice::whereDate('created_at', $today)
                ->where('status', 'ready')
                ->count(),
        ]);
    }
}