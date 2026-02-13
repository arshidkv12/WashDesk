<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type Flash, type BreadcrumbItem } from '@/types';
    import { Link, router } from '@inertiajs/svelte';
    import { onMount } from 'svelte';
    import { page } from '@inertiajs/svelte'
    import { toast } from "svelte-sonner";
    import CustomerTable from '@/components/customer/CustomerTable.svelte';
    import MobileCustomerTable from '@/components/customer/MobileCustomerTable.svelte';
    import { throttle } from 'lodash';

    type customers = {
        data: Array<{
            id: number;
            name: string;
            phone: string;
            email?: string;
            address?: string;
            created_at_formatted: string;
        }>;
        current_page: number;
        last_page: number;
        links: any[];
        total: number;
        per_page: number;
        from:number,
        to:number
    };

    let { customers } = $props<{
        customers: customers;
    }>();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
        {
        title: 'Customers',
        href: '/customers',
        },
    ];

    let search = $state('');
    let isMobile = $state(false);


    $effect(() => {   
        const flash = $page.flash as Flash;
        if (flash?.message) {  
            if (flash.type === 'success') {
                toast.success(flash.message);
            } else if (flash.type === 'error') {
                toast.error(flash.message);
            }
        }
    });

    onMount(() => {

        isMobile = window.innerWidth < 768;

        const handleResize = () => {
        isMobile = window.innerWidth < 768;
        };

        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    });

    function applyFilters() {
        router.get(
        '/customers',
        { search },
        { preserveState: true, replace: true }
        );
    }

    const throttledApplyFilters = throttle(applyFilters, 300); 
</script>

<svelte:head>
  <title>Customers</title>
</svelte:head>

<AppLayout {breadcrumbs}>

    <div class="p-4 md:p-6 space-y-6">
        <!-- Filters & Add Customer -->
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
        <input
            type="text"
            id="search-customers"
            placeholder="Search customer name / phone"
            bind:value={search}
            oninput={throttledApplyFilters}
            class="border rounded px-3 py-2 w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-400"
        />

        <Link
            href="/customers/create"
            class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition w-full sm:w-auto text-center"
        >
            + New Customer
        </Link>
        </div>

        <!-- Results Count -->
        {#if customers.total > 0}
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-gray-500">
                    Showing <span class="font-medium">{customers.from}</span> to <span class="font-medium">{customers.to}</span> of{' '}
                    <span class="font-medium">{customers.total}</span> customers
                </p>
            </div>
        {/if}

        <!-- Desktop Table -->
        {#if !isMobile}
            <CustomerTable {customers} />
        {:else}
        <!-- Mobile Cards -->
            <MobileCustomerTable {customers} />
        {/if}

        <!-- Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between pt-4 gap-4">
        <div class="text-sm text-gray-600">
            Page {customers.current_page} of {customers.last_page}
        </div>

        <div class="flex flex-wrap gap-1 justify-center">
            {#each customers.links as link}
            <button
                disabled={!link.url}
                onclick={() =>
                    link.url &&
                    router.get(link.url, {}, { preserveState: true, preserveScroll: true, replace: true })
                }
                class="cursor-pointer px-3 py-1 text-sm border rounded
                {link.active ? 'bg-black text-white border-black' : 'text-gray-700 hover:bg-gray-100'}
                disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {@html link.label}
            </button>
            {/each}
        </div>
        </div>
    </div>
</AppLayout>