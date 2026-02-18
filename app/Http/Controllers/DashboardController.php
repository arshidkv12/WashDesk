<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Current date range
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Current month stats
        $currentMonthStats = [
            'revenue' => Invoice::whereBetween('created_at', [$startOfMonth, $now])
                ->sum('paid_amount'),
            
            'invoices' => Invoice::whereBetween('created_at', [$startOfMonth, $now])->count(),
            
            'customers' => Customer::whereBetween('created_at', [$startOfMonth, $now])->count(),
            
            'outstanding' => Invoice::whereBetween('created_at', [$startOfMonth, $now])
                ->sum(DB::raw('total_amount - paid_amount')),
        ];

        // Last month stats for comparison
        $lastMonthStats = [
            'revenue' => Invoice::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                ->sum('paid_amount'),
            
            'invoices' => Invoice::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count(),
            
            'customers' => Customer::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count(),
            
            'outstanding' => Invoice::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                ->sum(DB::raw('total_amount - paid_amount')),
        ];

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
                'icon' => 'Receipt',
                'color' => 'text-amber-600',
                'bgColor' => 'bg-amber-100',
            ],
        ];

        // Recent Invoices
        $recentOrders = Invoice::with('customer')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice' => $invoice->invoice_no ?? 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT),
                    'customer' => $invoice->customer->name ?? 'Guest',
                    'amount' => $invoice->total_amount,
                    'status' => $invoice->status,
                    'time' => $invoice->created_at->diffForHumans(),
                    'items' => $invoice->items()->count(),
                ];
            });

        // Daily performance (last 30 days) - FIXED: Changed from weekly to daily
        $startDate = now()->subDays(29)->startOfDay(); // 30 days including today
        $endDate = now()->endOfDay();
        
        $rawDaily = Invoice::selectRaw('DATE(created_at) as date, SUM(paid_amount) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('total', 'date');

        // Generate last 30 days with proper day numbers
        $dailyPerformance = collect(range(29, 0))->map(function ($daysAgo) use ($rawDaily) {
            $date = now()->subDays($daysAgo)->toDateString();
            $dayNumber = now()->subDays($daysAgo)->format('d'); // Get day of month
            $dayName = now()->subDays($daysAgo)->format('D'); // Get short day name
            
            $value = (float) ($rawDaily[$date] ?? 0);

            return [
                'day' => $dayNumber, // Use day number for label
                'date' => $date,
                'label' => $dayName . ' ' . $dayNumber, // Combined label if needed
                'value' => round($value / 1000, 2), // Convert to millions
            ];
        });

        // Quick stats for today
        $today = Carbon::today();
        $todayRevenue = Invoice::whereDate('created_at', $today)->sum('paid_amount');
        $todayAvgOrder = Invoice::whereDate('created_at', $today)->avg('paid_amount');
        
        $quickStats = [
            'todayRevenue' => $todayRevenue >= 1000000 
                ? round($todayRevenue / 1000000, 1) . 'M' 
                : round($todayRevenue / 1000, 0) . 'K',
            'processing' => Invoice::whereDate('created_at', $today)
                ->where('status', 'processing')
                ->count(),
            'ready' => Invoice::whereDate('created_at', $today)
                ->where('status', 'ready')
                ->count(),
            'completed' => Invoice::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->count(),
            'averageOrderValue' => $todayAvgOrder 
                ? (round($todayAvgOrder / 1000, 0) . 'K') 
                : '0K',
            'busyLevel' => $this->calculateBusyLevel(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'dailyPerformance' => $dailyPerformance, 
            'quickStats' => $quickStats,
            'currentMonth' => $now->format('F Y'),
            'statusOptions' => InvoiceStatus::options(),
        ]);
    }

    private function calculateChange($current, $previous)
    {
        if ($previous == 0) return 100;
        return round((($current - $previous) / $previous) * 100, 1);
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
        // Calculate business busy level based on current invoices vs capacity
        $totalInvoices = Invoice::whereDate('created_at', Carbon::today())->count();
        $maxCapacity = 50; // Set your max daily capacity
        
        return min(round(($totalInvoices / $maxCapacity) * 100), 100);
    }

    // Optional: API endpoint for real-time updates
    public function getQuickStats()
    {
        $today = Carbon::today();
        $todayRevenue = Invoice::whereDate('created_at', $today)->sum('paid_amount');
        
        return response()->json([
            'todayRevenue' => $todayRevenue >= 1000000 
                ? round($todayRevenue / 1000000, 1) . 'M' 
                : round($todayRevenue / 1000, 0) . 'K',
            'processing' => Invoice::whereDate('created_at', $today)
                ->where('status', 'processing')
                ->count(),
            'ready' => Invoice::whereDate('created_at', $today)
                ->where('status', 'ready')
                ->count(),
            'busyLevel' => $this->calculateBusyLevel(),
        ]);
    }
}