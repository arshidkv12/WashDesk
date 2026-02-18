<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type BreadcrumbItem } from '@/types';
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
        TrendingUpIcon,
    } from 'lucide-svelte';
    import { Link, page } from '@inertiajs/svelte';
    import ApexCharts from 'apexcharts';
    import { onDestroy } from 'svelte';

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
        recentOrders = [], 
        dailyPerformance = [],
        weeklyPerformance = {},
        quickStats,
        currentMonth,
        statusOptions = [],
        chartConfig = {}
    } = $props();

    let chartRef: HTMLDivElement|null = $state(null);
    let chart: any;

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    };

    const formatValue = (value: number, decimals: number = 1) => {
        if (value >= 1000) {
            return (value / 1000).toFixed(decimals) + 'M';
        }
        if (value >= 1) {
            return value.toFixed(decimals) + 'K';
        }
        return (value * 1000).toFixed(0) + '';
    };

    const getStatusBadgeColor = (status: string) => {
        const colors = {
            'completed': 'bg-emerald-100 text-emerald-700',
            'processing': 'bg-blue-100 text-blue-700',
            'pending': 'bg-amber-100 text-amber-700',
            'ready': 'bg-purple-100 text-purple-700',
            'cancelled': 'bg-red-100 text-red-700'
        };
        return colors[status as keyof typeof colors] || colors.pending;
    };

    const maxValue = $derived(
        dailyPerformance.length > 0 
            ? Math.max(...dailyPerformance.map((d: any) => d.value))
            : 0
    );

    const minValue = $derived(
        dailyPerformance.length > 0 
            ? Math.min(...dailyPerformance.map((d: any) => d.value))
            : 0
    );

    const averageValue = $derived(
        dailyPerformance.length > 0
            ? dailyPerformance.reduce((acc: number, curr: any) => acc + curr.value, 0) / dailyPerformance.length
            : 0
    );

    const totalValue = $derived(
        dailyPerformance.length > 0
            ? dailyPerformance.reduce((acc: number, curr: any) => acc + curr.value, 0)
            : 0
    );

    // Get trend direction
    const trend = $derived(() => {
        if (dailyPerformance.length < 2) return 'neutral';
        const first = dailyPerformance[0].value;
        const last = dailyPerformance[dailyPerformance.length - 1].value;
        if (last > first) return 'up';
        if (last < first) return 'down';
        return 'neutral';
    });

    // Prepare chart data
    $effect(() => {
        if (dailyPerformance && dailyPerformance.length > 0 && chartRef) {
            const chartData = {
                series: [{
                    name: 'Daily Revenue',
                    data: dailyPerformance.map(d => Number(d.value))
                }],
                chart: {
                    type: 'line',
                    height: 300,
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    },
                    background: 'transparent',
                    fontFamily: 'inherit',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    dropShadow: {
                        enabled: true,
                        top: 3,
                        left: 0,
                        blur: 4,
                        color: '#3b82f6',
                        opacity: 0.2
                    }
                },
                colors: ['#3b82f6'],
                stroke: {
                    curve: 'smooth',
                    width: 3,
                    lineCap: 'round'
                },
                markers: {
                    size: 4,
                    colors: ['#ffffff'],
                    strokeColors: '#3b82f6',
                    strokeWidth: 2,
                    hover: {
                        size: 6
                    }
                },
                dataLabels: {
                    enabled: false
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        shadeIntensity: 0.3,
                        gradientToColors: ['#60a5fa'],
                        inverseColors: false,
                        opacityFrom: 0.4,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: dailyPerformance.map(d => d.day),
                    labels: {
                        style: {
                            colors: '#6b7280',
                            fontSize: '10px',
                            fontWeight: 400,
                        },
                        rotate: -45,
                        rotateAlways: true,
                        hideOverlappingLabels: true,
                        trim: true,
                        maxHeight: 80,
                        formatter: function(val: string, index: number) {
                            // Show every 3rd day to avoid crowding
                            if (index % 3 === 0) {
                                return val;
                            }
                            return '';
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    tooltip: {
                        enabled: false
                    },
                    crosshairs: {
                        show: true,
                        width: 1,
                        position: 'back',
                        opacity: 0.3,
                        stroke: {
                            color: '#3b82f6',
                            width: 1,
                            dashArray: 3
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6b7280',
                            fontSize: '11px',
                            fontWeight: 400,
                        },
                        formatter: function(val: number) {
                            return formatValue(val, 0);
                        }
                    },
                    title: {
                        text: 'Revenue (K)',
                        style: {
                            color: '#6b7280',
                            fontSize: '11px',
                            fontWeight: 500,
                        }
                    },
                    min: minValue > 0 ? Math.max(0, minValue * 0.9) : 0,
                    forceNiceScale: true
                },
                grid: {
                    borderColor: '#e5e7eb',
                    strokeDashArray: 4,
                    xaxis: {
                        lines: {
                            show: true
                        }
                    },
                    yaxis: {
                        lines: {
                            show: true
                        }
                    },
                    padding: {
                        top: 20,
                        right: 10,
                        bottom: 10,
                        left: 10
                    }
                },
                tooltip: {
                    theme: 'light',
                    enabled: true,
                    shared: true,
                    intersect: false,
                    followCursor: true,
                    x: {
                        show: true,
                        format: 'dd MMM',
                        formatter: function(val: number, opts: any) {
                            const dataPoint = dailyPerformance[opts.dataPointIndex];
                            return dataPoint?.fullDate || `Day ${val}`;
                        }
                    },
                    y: {
                        formatter: function(val: number) {
                            return formatValue(val, 1);
                        }
                    },
                    marker: {
                        show: true
                    }
                },
                legend: {
                    show: false
                },
                states: {
                    hover: {
                        filter: {
                            type: 'lighten',
                            value: 0.05
                        }
                    }
                }
            };

            if (!chart) {
                chart = new ApexCharts(chartRef, chartData);
                chart.render();
            } else {
                chart.updateOptions(chartData);
            }
        }
    });

    onDestroy(()=>{
        chart.destroy();
    });

    const user = $page.props.auth.user as UserType;

</script>

<svelte:head>
    <title>Dashboard | Laundry Management</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="flex-1 space-y-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Dashboard</h1>
                <p class="text-sm text-gray-500">
                    {currentMonth || new Date().toLocaleDateString('en-US', { 
                        month: 'long', 
                        year: 'numeric' 
                    })}
                </p>
            </div>
            <div class="flex gap-2">
                <Button size="sm" href="/invoices/create">
                    <Plus class="h-4 w-4 mr-2" />
                    New Order
                </Button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {#each stats as stat}
            {@const Icon = iconMap.get(stat.icon)}
                <div class="bg-white rounded-lg border p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <div class={`p-2 rounded-lg ${stat.bgColor}`}>
                            {#if Icon}
                                <Icon class={`h-5 w-5 ${stat.color}`} />
                            {/if}
                        </div>
                        {#if stat.change !== undefined}
                            <Badge variant="outline" class={stat.trend === 'up' ? 'text-emerald-600 border-emerald-200 bg-emerald-50' : stat.trend === 'down' ? 'text-amber-600 border-amber-200 bg-amber-50' : 'text-gray-600 border-gray-200 bg-gray-50'}>
                                {stat.trend === 'up' ? '↑' : stat.trend === 'down' ? '↓' : '→'} {Math.abs(stat.change)}%
                            </Badge>
                        {/if}
                    </div>
                    <p class="text-sm text-gray-500">{stat.title}</p>
                    <p class="text-2xl font-bold">
                        {stat.title.toLowerCase().includes('revenue') || stat.title.toLowerCase().includes('outstanding') 
                            ? formatCurrency(stat.value)
                            : stat.value}
                    </p>
                </div>
            {/each}
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 30-Day Performance Line Chart -->
            <div class="lg:col-span-2 bg-white rounded-lg border p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">Revenue Trend</h3>
                        <p class="text-sm text-gray-500">Last 30 days performance</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-blue-600 rounded-full"></span>
                            <span class="text-xs text-gray-600">Daily Revenue</span>
                        </div>
                        <Badge variant="outline" class={trend() === 'up' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'}>
                            <TrendingUpIcon class="h-3 w-3 mr-1" />
                            {trend() === 'up' ? 'Upward trend' : 'Downward trend'}
                        </Badge>
                    </div>
                </div>

                <!-- Chart Container -->
                {#if dailyPerformance && dailyPerformance.length > 0}
                    <div bind:this={chartRef} class="w-full"></div>

                    <!-- Quick Stats Cards -->
                    <div class="grid grid-cols-4 gap-3 mt-4">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-3">
                            <p class="text-xs text-blue-600 font-medium">Highest</p>
                            <p class="text-lg font-bold text-blue-700">{formatValue(maxValue)}</p>
                            <p class="text-xs text-blue-600">Day {dailyPerformance.find(d => d.value === maxValue)?.day}</p>
                        </div>
                        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg p-3">
                            <p class="text-xs text-emerald-600 font-medium">Average</p>
                            <p class="text-lg font-bold text-emerald-700">{formatValue(averageValue)}</p>
                            <p class="text-xs text-emerald-600">per day</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-3">
                            <p class="text-xs text-purple-600 font-medium">Total</p>
                            <p class="text-lg font-bold text-purple-700">{formatValue(totalValue)}</p>
                            <p class="text-xs text-purple-600">30 days</p>
                        </div>
                        <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg p-3">
                            <p class="text-xs text-amber-600 font-medium">Lowest</p>
                            <p class="text-lg font-bold text-amber-700">{formatValue(minValue)}</p>
                            <p class="text-xs text-amber-600">Day {dailyPerformance.find(d => d.value === minValue)?.day}</p>
                        </div>
                    </div>

                {:else}
                    <div class="h-[300px] flex items-center justify-center text-gray-500 border-2 border-dashed rounded-lg">
                        No data available for the last 30 days
                    </div>
                {/if}
            </div>

            <!-- Today's Activity -->
            <div class="bg-white rounded-lg border p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">Today's Activity</h3>
                        <p class="text-sm text-gray-500">
                            {new Date().toLocaleDateString('en-US', { 
                                month: 'long', 
                                day: 'numeric' 
                            })}
                        </p>
                    </div>
                    <Activity class="h-5 w-5 text-gray-400" />
                </div>

                {#if quickStats}

                    <Separator class="my-4" />

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-3">
                            <p class="text-xs text-blue-600 font-medium">Revenue</p>
                            <p class="text-xl font-bold text-blue-700">{quickStats.todayRevenue || '0'}</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-3">
                            <p class="text-xs text-purple-600 font-medium">Avg Order</p>
                            <p class="text-xl font-bold text-purple-700">{quickStats.averageOrderValue || '0'}</p>
                        </div>
                        <div class="bg-amber-50 rounded-lg p-3">
                            <p class="text-xs text-amber-600 font-medium">Processing</p>
                            <p class="text-xl font-bold text-amber-700">{quickStats.processing || 0}</p>
                        </div>
                        <div class="bg-emerald-50 rounded-lg p-3">
                            <p class="text-xs text-emerald-600 font-medium">Ready</p>
                            <p class="text-xl font-bold text-emerald-700">{quickStats.ready || 0}</p>
                        </div>
                        <div class="bg-indigo-50 rounded-lg p-3 col-span-2">
                            <p class="text-xs text-indigo-600 font-medium">Completed Today</p>
                            <p class="text-xl font-bold text-indigo-700">{quickStats.completed || 0} orders</p>
                        </div>
                    </div>
                {:else}
                    <div class="h-64 flex items-center justify-center text-gray-500 border-2 border-dashed rounded-lg">
                        No activity data available
                    </div>
                {/if}
            </div>
        </div>

        <!-- Recent Orders Table -->
        {#if recentOrders && recentOrders.length > 0}
            <div class="bg-white rounded-lg border p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">Recent Orders</h3>
                        <p class="text-sm text-gray-500">Latest transactions</p>
                    </div>
                    <Button variant="ghost" size="sm" href="/invoices" class="gap-2">
                        View All
                        <ArrowRight class="h-4 w-4" />
                    </Button>
                </div>

                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-gray-50">
                                <TableHead class="font-semibold">Invoice</TableHead>
                                <TableHead class="font-semibold">Customer</TableHead>
                                <TableHead class="font-semibold">Items</TableHead>
                                <TableHead class="font-semibold">Amount</TableHead>
                                <TableHead class="font-semibold">Status</TableHead>
                                <TableHead class="font-semibold">Time</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {#each recentOrders as order}
                                <TableRow class="hover:bg-gray-50 transition-colors">
                                    <TableCell class="font-medium text-blue-600">{order.invoice}</TableCell>
                                    <TableCell>{order.customer}</TableCell>
                                    <TableCell>{order.items} items</TableCell>
                                    <TableCell class="font-semibold">{formatCurrency(order.amount)}</TableCell>
                                    <TableCell>
                                        <Badge class={`${getStatusBadgeColor(order.status)} px-3 py-1`}>
                                            {order.status}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-gray-500 text-sm">{order.time}</TableCell>
                                </TableRow>
                            {/each}
                        </TableBody>
                    </Table>
                </div>
            </div>
        {/if}
    </div>
</AppLayout>