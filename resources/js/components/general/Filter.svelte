<script lang="ts">
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Calendar } from '@/components/ui/calendar';
    import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
    import { Badge } from '@/components/ui/badge';
    import { Search, CalendarIcon, X, Funnel } from 'lucide-svelte';
    import { format } from 'date-fns';
    import { router } from '@inertiajs/svelte';
    import * as Select from '@/components/ui/select';
    import _, { throttle } from 'lodash';
    import { type Filters } from '@/types';
    import { onMount } from 'svelte';

    let {
        filters = $bindable(),
        routePath,
        routePathArg,
        statusOptions,
        placeholder = 'Search records',
        enableDateRange = true,
        enableStatusFilter = true,
    } = $props<{
        filters?: Filters;
        routePath: string;
        routePathArg?: string;
        statusOptions?: { value: string; label: string }[];
        categoryOptions?: { value: string; label: string }[];
        placeholder?: string;
        enableDateRange?: boolean;
        enableStatusFilter?: boolean;
        enableCategoryFilter?: boolean;
    }>();

    let localFilters = $state({ ...filters });
    let isAdvancedOpen = $state(false);
    let dateFromPopover = $state(false);
    let dateToPopover = $state(false);
    let routePathArgVal = $state("");

    const statusMap = $derived(_.keyBy(statusOptions, 'value'));
    const getLabel = (val:any) => statusMap[val]?.label ?? 'Select Status';

    const hasActiveFilters = $derived.by(() => {
        return Object.values(localFilters).some(
            value => value !== '' && value !== null && value !== undefined
        );
    });

    const activeFilterCount = $derived.by(() => {
        return Object.values(localFilters).filter(v => v && v !== '').length;
    });

    const throttledApplyFilters = throttle(applyFilters, 1000); 

    function formatDate(dateString?: string): string {
        if (!dateString) return 'Select date';
        try {
            return format(new Date(dateString), 'MMM dd, yyyy');
        } catch {
            return 'Invalid date';
        }
    }

    onMount(()=>{
        routePathArgVal = routePathArg;
    });

    function applyFilters() { 
        const cleanFilters: Record<string, any> = {};
        
        Object.entries(localFilters).forEach(([key, value]) => {
            if (value !== '' && value !== null && value !== undefined) {
                localFilters[key] = value;
            }
        });

        router.get(route(routePath, routePathArgVal), localFilters, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }


    function resetFilters() {
        localFilters = { ...localFilters };
        Object.keys(localFilters).forEach(key => {
            localFilters[key] = '';
        });

        router.get(route(routePath, routePathArgVal), localFilters, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }


    function clearFilter(key: string) {
        localFilters = { ...localFilters, [key]: '' };

        router.get(route(routePath, routePathArgVal), localFilters, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }

    // Handle Enter key in search
    function handleKeyPress(e: KeyboardEvent) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    }

    // Quick date preset functions
    function setLast7Days() {
        const today = new Date();
        const sevenDaysAgo = new Date(today);
        sevenDaysAgo.setDate(today.getDate() - 7);
        
        localFilters.date_from = format(sevenDaysAgo, 'yyyy-MM-dd');
        localFilters.date_to = format(today, 'yyyy-MM-dd');
        applyFilters();
    }

    function setLast30Days() {
        const today = new Date();
        const thirtyDaysAgo = new Date(today);
        thirtyDaysAgo.setDate(today.getDate() - 30);
        
        localFilters.date_from = format(thirtyDaysAgo, 'yyyy-MM-dd');
        localFilters.date_to = format(today, 'yyyy-MM-dd');
        applyFilters();
    }

    function setThisMonth() {
        const today = new Date();
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        
        localFilters.date_from = format(firstDayOfMonth, 'yyyy-MM-dd');
        localFilters.date_to = format(today, 'yyyy-MM-dd');
        applyFilters();
    }

    function clearDates() {
        localFilters.date_from = '';
        localFilters.date_to = '';
        applyFilters();
    }
</script>

<div class="space-y-4">
    <!-- Main Search and Filter Bar -->
    <div class="flex flex-col sm:flex-row gap-3">
        <!-- Search Input -->
        <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
            <Input
                type="text"
                placeholder={placeholder}
                bind:value={localFilters.search}
                oninput={throttledApplyFilters}
                class="pl-9"
            />
        </div>

        <!-- Advanced Filter Toggle Button -->
        <Button
            variant={isAdvancedOpen ? "secondary" : "outline"}
            onclick={() => isAdvancedOpen = !isAdvancedOpen}
            class="gap-2 cursor-pointer"
        >
            <Funnel class="h-4 w-4" />
            {isAdvancedOpen ? 'Hide Filters' : 'Filters'}
            {#if hasActiveFilters}
                <Badge variant="secondary" class="ml-1 h-5 w-5 p-0 flex items-center justify-center">
                    {activeFilterCount}
                </Badge>
            {/if}
        </Button>

        <!-- Apply & Reset Buttons -->
        <div class="flex gap-2">
            <Button onclick={applyFilters} class="gap-2 cursor-pointer bg-blue-600 hover:bg-blue-700">
                Apply
            </Button>
            {#if hasActiveFilters}
                <Button variant="outline" onclick={resetFilters} class="gap-2 cursor-pointer">
                    <X class="h-4 w-4" />
                    Clear All
                </Button>
            {/if}
        </div>
    </div>

    <!-- Active Filters Badges -->
    {#if hasActiveFilters}
        <div class="flex flex-wrap gap-2 items-center">
            <span class="text-sm text-muted-foreground">Active filters:</span>
            {#each Object.entries(localFilters) as [key, value]}
                {#if value && value !== ''}
                    <Badge variant="secondary" class="gap-1 pl-2">
                        {key.replace('_', ' ')}: {value}
                        <Button
                            variant="ghost"
                            size="sm"
                            onclick={() => clearFilter(key)}
                            class="h-4 w-4 p-0 hover:bg-transparent cursor-pointer"
                        >
                            <X class="h-3 w-3" />
                        </Button>
                    </Badge>
                {/if}
            {/each}
        </div>
    {/if}

    <!-- Advanced Filters Panel -->
    {#if isAdvancedOpen}
        <div class="border rounded-lg p-4 space-y-4 animate-in fade-in duration-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Date Range Filters -->
                {#if enableDateRange}
                    <div class="space-y-2">
                        <Label for="date_from">From Date</Label>
                        <Popover bind:open={dateFromPopover}>
                            <PopoverTrigger>
                                <Button variant="outline" class="w-full justify-start text-left font-normal">
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    {formatDate(localFilters.date_from)}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0">
                                <Calendar
                                    type="single"
                                    captionLayout="dropdown"
                                    onValueChange={(v) => {
                                        if(!v) return;
                                        localFilters.date_from =format(v.toString(), 'yyyy-MM-dd');
                                        dateToPopover = false;
                                        applyFilters();
                                    }}
                                />
                            </PopoverContent>
                        </Popover>
                    </div>

                    <div class="space-y-2">
                        <Label for="date_to">To Date</Label>
                        <Popover bind:open={dateToPopover}>
                            <PopoverTrigger>
                                <Button variant="outline" class="w-full justify-start text-left font-normal">
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    {formatDate(localFilters.date_to)}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0">
                                <Calendar
                                    type="single"
                                    class="rounded-md border shadow-sm"
                                    captionLayout="dropdown"
                                    onValueChange={(v) => {
                                        if(!v) return;
                                        localFilters.date_to =format(v.toString(), 'yyyy-MM-dd');
                                        dateToPopover = false;
                                    }}
                                />
                            </PopoverContent>
                        </Popover>
                    </div>
                {/if}

                <!-- Status Filter -->
                {#if enableStatusFilter && statusOptions.length > 0}
                    <div class="space-y-2">
                        <Label for="status">Status</Label>
                        <Select.Root 
                            type='single'
                            value={localFilters.status || ''} 
                            onValueChange={(value) => {
                                localFilters.status = value; 
                                applyFilters();
                            }}
                        >
                            <Select.Trigger>
                                {localFilters.status ? getLabel(localFilters.status) : 'Select status'}
                            </Select.Trigger>
                            <Select.Content>
                                <Select.Item value="">All Statuses</Select.Item>
                                {#each statusOptions as option}
                                    <Select.Item value={option.value}>{option.label}</Select.Item>
                                {/each}
                            </Select.Content>
                        </Select.Root>
                    </div>
                {/if}

            </div>

            <!-- Quick Date Presets -->
            {#if enableDateRange}
                <div class="pt-2">
                    <Label class="text-sm text-muted-foreground mb-2 block">Quick Date Ranges:</Label>
                    <div class="flex flex-wrap gap-2">
                        <Button variant="outline" size="sm" onclick={setLast7Days}>
                            Last 7 Days
                        </Button>
                        <Button variant="outline" size="sm" onclick={setLast30Days}>
                            Last 30 Days
                        </Button>
                        <Button variant="outline" size="sm" onclick={setThisMonth}>
                            This Month
                        </Button>
                        <Button variant="outline" size="sm" onclick={clearDates}>
                            Clear Dates
                        </Button>
                    </div>
                </div>
            {/if}
        </div>
    {/if}
</div>

<style>
    .animate-in {
        animation: animate-in 0.2s ease-out;
    }
    
    .fade-in {
        animation: fadeIn 0.2s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>