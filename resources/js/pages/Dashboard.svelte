<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type BreadcrumbItem } from '@/types';
    
    // Shadcn components
    import * as Card from '@/components/ui/card';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Separator } from '@/components/ui/separator';
    import {
        Table,
        TableHeader,
        TableBody,
        TableHead,
        TableRow,
        TableCell
    } from '@/components/ui/table';
    import { type User as UserType } from '@/types';

    // Icons
    import {
        DollarSign,
        Package,
        Users,
        Receipt,
        TrendingUp,
        TrendingDown,
        Clock,
        CircleCheck,
        ArrowRight,
        Download,
        Plus,
        ChartColumn,
        Activity,
        CreditCard,
        MapIcon,
        Wrench,
        CalendarDays,
    } from 'lucide-svelte';
    import { Link, page } from '@inertiajs/svelte';
    import { onMount } from 'svelte';

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
    ];

    
   const iconMap = new Map<string, any>([
        ['DollarSign', DollarSign],
        ['Package', Package],
        ['Users', Users],
        ['Receipt', Receipt],
        ['TrendingUp', TrendingUp],
        ['TrendingDown', TrendingDown],
        ['Clock', Clock],
        ['CircleCheck', CircleCheck],
        ['ArrowRight', ArrowRight],
        ['Download', Download],
        ['Plus', Plus],
        ['ChartColumn', ChartColumn],
        ['Activity', Activity],
        ['CreditCard', CreditCard],
    ]);

    let { 
    stats, 
        dailyPerformance = [],  
        quickStats,
    } = $props();



    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    };

    const getStatusClass = (status: string) => {
        const classes = {
            completed: 'bg-emerald-100 text-emerald-700 border-emerald-200',
            processing: 'bg-blue-100 text-blue-700 border-blue-200',
            pending: 'bg-amber-100 text-amber-700 border-amber-200'
        };
        return classes[status as keyof typeof classes] || classes.pending;
    };

    // Safe calculations with checks for empty array
    const maxChartValue = $derived(
        dailyPerformance && dailyPerformance.length > 0 
            ? Math.max(...dailyPerformance.map((d:any) => d.value))
            : 0
    );

    // Calculate average for last 30 days
    const averageValue = $derived(
        dailyPerformance && dailyPerformance.length > 0
            ? dailyPerformance.reduce((acc: number, curr: any) => acc + curr.value, 0) / dailyPerformance.length
            : 0
    );

    // Calculate total for last 30 days
    const totalValue = $derived(
        dailyPerformance && dailyPerformance.length > 0
            ? dailyPerformance.reduce((acc: number, curr: any) => acc + curr.value, 0)
            : 0
    );

    // Get best day
    const bestDay = $derived(
        dailyPerformance && dailyPerformance.length > 0
            ? dailyPerformance.reduce((best: any, current: any) => 
                current.value > best.value ? current : best
            , dailyPerformance[0])
            : { day: 'N/A', value: 0 }
    );

    // Weekly aggregates with safety checks
    const week1Total = $derived(
        dailyPerformance.slice(0, 7).reduce((acc: number, d: any) => acc + d.value, 0).toFixed(1)
    );

    const week2Total = $derived(
        dailyPerformance.slice(7, 14).reduce((acc: number, d: any) => acc + d.value, 0).toFixed(1)
    );

    const week3Total = $derived(
        dailyPerformance.slice(14, 21).reduce((acc: number, d: any) => acc + d.value, 0).toFixed(1)
    );

    const week4Total = $derived(
        dailyPerformance.slice(21, 30).reduce((acc: number, d: any) => acc + d.value, 0).toFixed(1)
    );

    onMount(()=>{
    });

    const user = $page.props.auth.user as UserType;

    // Helper to format day display
    const formatDayLabel = (day: any) => {
        if (!day) return '';
        // If day has a date property, show day number only
        if (day.date) {
            return day.date.split('-')[2]; // Extract day from YYYY-MM-DD
        }
        // Otherwise assume it's already a day number or short label
        return day.day || '';
    };

</script>

<svelte:head>
    <title>Dashboard | Laundry Management</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="flex-1 space-y-6 p-6">
        <!-- Simple Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Dashboard</h1>
                <p class="text-sm text-gray-500">
                    {new Date().toLocaleDateString('en-US', { 
                        month: 'long', 
                        day: 'numeric', 
                        year: 'numeric' 
                    })}
                </p>
            </div>
            <div class="flex gap-2">
                <Button size="sm">
                    <Plus class="h-4 w-4 mr-2" />
                    New Order
                </Button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {#each stats as stat}
            {@const Icon = iconMap.get(stat.icon)}
                <div class="bg-white rounded-lg border p-6">
                    <div class="flex items-center justify-between mb-2">
                        <div class={`p-2 rounded-lg ${stat.bgColor}`}>
                            {#if Icon}
                                <Icon class={`h-5 w-5 ${stat.color}`} />
                            {/if}
                        </div>
                        <Badge variant="outline" class={stat.trend === 'up' ? 'text-emerald-600' : 'text-amber-600'}>
                            {stat.trend === 'up' ? '+' : '-'}{stat.change}%
                        </Badge>
                    </div>
                    <p class="text-sm text-gray-500">{stat.title}</p>
                    <p class="text-2xl font-bold">
                        {stat.title.includes('Revenue') || stat.title.includes('Outstanding') 
                            ? formatCurrency(stat.value)
                            : stat.value}
                    </p>
                </div>
            {/each}
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 30-Day Performance -->
            <div class="lg:col-span-2 bg-white rounded-lg border p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-semibold">Last 30 Days Performance</h3>
                        <p class="text-sm text-gray-500">Daily revenue trend</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <CalendarDays class="h-5 w-5 text-gray-400" />
                        <span class="text-xs text-gray-500">30 days</span>
                    </div>
                </div>

                <!-- Bar Chart - Scrollable for 30 days -->
                {#if dailyPerformance && dailyPerformance.length > 0}
                    <div class="overflow-x-auto pb-2">
                        <div class="h-40 flex items-end gap-1 min-w-[600px]">
                            {#each dailyPerformance as day}
                                <div class="flex-1 flex flex-col items-center min-w-[20px]">
                                    <div 
                                        class="w-full bg-blue-600 rounded-t hover:bg-blue-700 transition-colors"
                                        style="height: {maxChartValue > 0 ? (day.value / maxChartValue) * 120 : 0}px;"
                                        title={`Day ${formatDayLabel(day)}: {day.value}M`}
                                    ></div>
                                    <span class="text-[10px] text-gray-500 mt-2 rotate-45 origin-left">
                                        {formatDayLabel(day)}
                                    </span>
                                </div>
                            {/each}
                        </div>
                    </div>

                    <!-- Summary Stats for 30 Days -->
                    <div class="grid grid-cols-4 gap-4 mt-6 pt-4 border-t">
                        <div>
                            <p class="text-xs text-gray-500">Best Day</p>
                            <p class="text-sm font-medium">Day {formatDayLabel(bestDay)}</p>
                            <p class="text-xs text-gray-500">{bestDay.value}M</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Daily Average</p>
                            <p class="text-sm font-medium">{averageValue.toFixed(1)}M</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">30-Day Total</p>
                            <p class="text-sm font-medium">{totalValue.toFixed(1)}M</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">vs Last Month</p>
                            <p class="text-sm font-medium text-emerald-600">+8.2%</p>
                        </div>
                    </div>

                    <!-- Mini trend indicators -->
                    <div class="flex gap-4 mt-3 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                            Week 1: {week1Total}M
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                            Week 2: {week2Total}M
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                            Week 3: {week3Total}M
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                            Week 4: {week4Total}M
                        </span>
                    </div>
                {:else}
                    <div class="h-40 flex items-center justify-center text-gray-500">
                        No data available for the last 30 days
                    </div>
                {/if}
            </div>

            <!-- Today's Activity -->
            <div class="bg-white rounded-lg border p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-semibold">Today's Activity</h3>
                        <p class="text-sm text-gray-500">Real-time stats</p>
                    </div>
                    <Activity class="h-5 w-5 text-gray-400" />
                </div>

                <!-- Busy Level -->
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span>Business Level</span>
                        <span class="font-medium">{quickStats.busyLevel}%</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full">
                        <div 
                            class="h-2 bg-blue-600 rounded-full"
                            style="width: {quickStats.busyLevel}%;"
                        ></div>
                    </div>
                </div>

                <Separator class="my-4" />

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">Revenue</p>
                        <p class="text-lg font-semibold">{quickStats.todayRevenue}</p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">Avg Order</p>
                        <p class="text-lg font-semibold">{quickStats.averageOrderValue}</p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">Processing</p>
                        <p class="text-lg font-semibold">{quickStats.processing}</p>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="text-xs text-gray-500">Ready</p>
                        <p class="text-lg font-semibold">{quickStats.ready}</p>
                    </div>
                </div>

                <!-- Quick 7-day comparison -->
                <div class="mt-4 pt-4 border-t">
                    <p class="text-xs text-gray-500 mb-2">vs Last 7 Days</p>
                    <div class="flex justify-between items-center">
                        <span class="text-sm">Average</span>
                        <span class="text-sm font-medium text-emerald-600">+5.3%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AppLayout>