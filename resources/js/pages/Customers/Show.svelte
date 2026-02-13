<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type Flash, type BreadcrumbItem } from '@/types';
    import { onMount } from 'svelte';

    import { Button } from '@/components/ui/button';
    import { Label } from '@/components/ui/label';
    import { Badge } from '@/components/ui/badge';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Separator } from '@/components/ui/separator';
    import { 
        Phone, 
        Mail, 
        MapPin, 
        SquarePen, 
        ArrowLeft,
        FileText,
        Receipt,
        Briefcase,
        Copy,
        Check,
        Plus,
        ChevronRight,
    } from 'lucide-svelte';
    import { Link, page, router } from '@inertiajs/svelte';
    import { toast } from 'svelte-sonner';
    import DeleteConfirmDialog from '@/components/confirm/DeleteConfirmDialog.svelte';

    export let customer: {
        id: number;
        name: string;
        phone?: string;
        email?: string;
        address?: string;
        created_at_formatted?: string;
    };

    export let stats: {
        job_cards_count?: number;
        invoices_count?: number;
        total_invoiced?: number;
        pending_invoices?: number;
    };

    let copyStatus: { [key: string]: boolean } = {
        phone: false,
        email: false,
        address: false
    };

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Customers', href: '/customers' },
        { title: `ID - ${customer.id}`, href: `/customers/${customer.id}` },
    ];

    onMount(() => {
        const flash = $page.flash as Flash;
        if (flash.message && flash.type === 'success') {
            toast.success(flash.message);
        }
    });

    function copyToClipboard(text: string, type: 'phone' | 'email' | 'address') {
        if (!text || text === '—') return;
        
        navigator.clipboard.writeText(text).then(() => {
            copyStatus[type] = true;
            setTimeout(() => {
                copyStatus[type] = false;
            }, 2000);
        });
    }

    function formatCurrency(amount: number) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }
</script>

<svelte:head>
    <title>{customer.name} - Customer Details</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <!-- Pure white background -->
    <div class="min-h-screen bg-white">
        <!-- Main Content -->
        <div class="p-4 md:p-6  max-w-6xl mx-auto">
            <!-- Customer Header -->
            <div class="mb-8">
                <div class="space-y-4 lg:space-y-0 lg:flex lg:items-center lg:justify-between mb-6">
                    <div class="space-y-2">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 truncate">{customer.name}</h1>
                        <div class="flex flex-wrap items-center gap-2 md:gap-4">
                            <p class="text-sm text-gray-500">
                                ID: <span class="font-medium">#{customer.id.toString().padStart(4, '0')}</span>
                            </p>
                            <span class="text-gray-300 hidden sm:inline">•</span>
                            <p class="text-sm text-gray-500">
                                Since: <span class="font-medium">{customer.created_at_formatted || '—'}</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex gap-2">
                            <Link href={`/job-cards/create?customer_id=${customer.id}`}>
                                <Button variant="default" 
                                    class="gap-1 sm:gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm md:text-base flex-1 sm:flex-none"
                                >
                                    <Plus class="h-4 w-4" />
                                    <span class="hidden sm:inline">New Job Card</span>
                                </Button>
                            </Link>
                            <Link href={`/customers/${customer.id}/edit`}>
                                <Button 
                                    href={`/customers/${customer.id}/edit`} 
                                    variant="outline" 
                                    class="gap-1 sm:gap-2 text-sm md:text-base flex-1 sm:flex-none"
                                >
                                    <SquarePen class="h-4 w-4" />
                                    Edit
                                </Button>
                            </Link>
                        </div>
                        
                        <!-- <Button 
                            variant="destructive" 
                            class="gap-1 sm:gap-2 text-sm md:text-base cursor-pointer"
                        >
                            <Trash2 class="h-4 w-4" />
                            Delete
                        </Button> -->
                       
                    </div>
                </div>
                <Separator />
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Customer Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Contact Information Card -->
                    <Card class="border border-gray-200 shadow-none">
                        <CardHeader class="pb-4">
                            <CardTitle class="text-lg font-semibold text-gray-900">
                                Contact Information
                            </CardTitle>
                            <CardDescription>
                                Customer contact details
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-5">
                            <!-- Phone -->
                            <div class="flex items-center justify-between group">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="p-2.5 bg-blue-50 rounded-lg">
                                        <Phone class="h-5 w-5 text-blue-600" />
                                    </div>
                                    <div class="flex-1">
                                        <Label class="text-sm font-medium text-gray-500 block mb-1">Phone Number</Label>
                                        <p class="text-base text-gray-900">
                                            {customer.phone || '—'}
                                        </p>
                                    </div>
                                </div>
                                {#if customer.phone}
                                    <Button 
                                        size="sm" 
                                        variant="ghost" 
                                        class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-opacity"
                                        onclick={() => copyToClipboard(customer.phone!, 'phone')}
                                    >
                                        {#if copyStatus.phone}
                                            <Check class="h-4 w-4 text-green-600" />
                                        {:else}
                                            <Copy class="h-4 w-4 text-gray-400" />
                                        {/if}
                                    </Button>
                                {/if}
                            </div>

                            <Separator />

                            <!-- Email -->
                            <div class="flex items-center justify-between group">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="p-2.5 bg-purple-50 rounded-lg">
                                        <Mail class="h-5 w-5 text-purple-600" />
                                    </div>
                                    <div class="flex-1">
                                        <Label class="text-sm font-medium text-gray-500 block mb-1">Email Address</Label>
                                        <p class="text-base text-gray-900 break-all">
                                            {customer.email || '—'}
                                        </p>
                                    </div>
                                </div>
                                {#if customer.email}
                                    <Button 
                                        size="sm" 
                                        variant="ghost" 
                                        class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-opacity"
                                        onclick={() => copyToClipboard(customer.email!, 'email')}
                                    >
                                        {#if copyStatus.email}
                                            <Check class="h-4 w-4 text-green-600" />
                                        {:else}
                                            <Copy class="h-4 w-4 text-gray-400" />
                                        {/if}
                                    </Button>
                                {/if}
                            </div>

                            <Separator />

                            <!-- Address -->
                            <div class="flex items-start justify-between group">
                                <div class="flex items-start gap-4 flex-1">
                                    <div class="p-2.5 bg-green-50 rounded-lg mt-1">
                                        <MapPin class="h-5 w-5 text-green-600" />
                                    </div>
                                    <div class="flex-1">
                                        <Label class="text-sm font-medium text-gray-500 block mb-1">Address</Label>
                                        <p class="text-base text-gray-900 whitespace-pre-line">
                                            {customer.address || '—'}
                                        </p>
                                    </div>
                                </div>
                                {#if customer.address}
                                    <Button 
                                        size="sm" 
                                        variant="ghost" 
                                        class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-opacity mt-1"
                                        onclick={() => copyToClipboard(customer.address!, 'address')}
                                    >
                                        {#if copyStatus.address}
                                            <Check class="h-4 w-4 text-green-600" />
                                        {:else}
                                            <Copy class="h-4 w-4 text-gray-400" />
                                        {/if}
                                    </Button>
                                {/if}
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card class="border border-gray-200 shadow-none">
                        <CardHeader class="pb-4">
                            <CardTitle class="text-lg font-semibold text-gray-900">
                                Quick Actions
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Link href={`/job-cards/create?customer_id=${customer.id}`}>
                                <Button variant="outline" 
                                    class="w-full justify-start gap-3 h-auto py-3 cursor-pointer"
                                >
                                    <div class="p-2 bg-blue-50 rounded-lg">
                                        <Briefcase class="h-4 w-4 text-blue-600" />
                                    </div>
                                    <div class="text-left flex-1">
                                        <p class="font-medium text-gray-900">Create Job Card</p>
                                        <p class="text-xs text-gray-500">Start a new job for this customer</p>
                                    </div>
                                    <ChevronRight class="h-4 w-4 text-gray-400" />
                                </Button>
                            </Link>

                            <Link href={`/invoices/create?customer_id=${customer.id}`}>
                                <Button 
                                    variant="outline" 
                                    class="w-full justify-start gap-3 h-auto py-3 mt-3 cursor-pointer"
                                >
                                    <div class="p-2 bg-green-50 rounded-lg">
                                        <Receipt class="h-4 w-4 text-green-600" />
                                    </div>
                                    <div class="text-left flex-1">
                                        <p class="font-medium text-gray-900">Create Invoice</p>
                                        <p class="text-xs text-gray-500">Generate a new invoice</p>
                                    </div>
                                    <ChevronRight class="h-4 w-4 text-gray-400" />
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column: Business Stats -->
                <div class="space-y-6">
                    <!-- Quick Stats -->
                    <Card class="border border-gray-200 shadow-none">
                        <CardHeader class="pb-4">
                            <CardTitle class="text-lg font-semibold text-gray-900">
                                Business Overview
                            </CardTitle>
                            <CardDescription>
                                Customer activity summary
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-5">
                            <!-- Job Cards -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg group hover:bg-gray-100 transition-colors cursor-pointer">
                                <Link href={`/customers/${customer.id}/job-cards`}>
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-blue-100 rounded-lg">
                                            <Briefcase class="h-4 w-4 text-blue-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Job Cards</p>
                                            <p class="text-2xl font-bold text-gray-900">{stats.job_cards_count}</p>
                                        </div>
                                    </div>
                                </Link>
                                <Button 
                                    href={`/customers/${customer.id}/job-cards`}
                                    variant="ghost" 
                                    size="sm"
                                    class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-all"
                                >
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>

                            <!-- Invoices -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg group hover:bg-gray-100 transition-colors cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-green-100 rounded-lg">
                                        <Receipt class="h-4 w-4 text-green-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Total Invoices</p>
                                        <p class="text-2xl font-bold text-gray-900">{stats?.invoices_count}</p>
                                    </div>
                                </div>
                                <Button 
                                    href={`/invoices?customer_id=${customer.id}`}
                                    variant="ghost" 
                                    size="sm"
                                    class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-all"
                                >
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>

                            <!-- Total Invoiced -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-purple-100 rounded-lg">
                                        <Receipt class="h-4 w-4 text-purple-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Total Invoiced</p>
                                        <p class="text-2xl font-bold text-gray-900">{formatCurrency(stats?.total_invoiced || 0)}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Pending Invoices -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg group hover:bg-gray-100 transition-colors cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-amber-100 rounded-lg">
                                        <FileText class="h-4 w-4 text-amber-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Pending Invoices</p>
                                        <div class="flex items-center gap-2">
                                            <p class="text-2xl font-bold text-gray-900">{stats.pending_invoices}</p>
                                            {#if (stats?.pending_invoices || 0) > 0}
                                                <Badge variant="destructive" class="text-xs">
                                                    Unpaid
                                                </Badge>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                <Button 
                                    href={`/invoices?customer_id=${customer.id}&status=pending`}
                                    variant="ghost" 
                                    size="sm"
                                    class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-all"
                                >
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>
                        </CardContent>
                    </Card>   
                    
                    <DeleteConfirmDialog
                        onConfirm={async () => router.delete(route('customers.destroy', customer.id), {
                            preserveScroll: true})
                        }
                        itemName={customer.name}
                        title="Delete Customer"
                        description={`This will permanently delete <b>${customer.name}</b> and all associated job cards. This action cannot be undone.`}
                        buttonText="Delete"
                        triggerClass="xs:w-auto justify-center xs:justify-start cursor-pointer"
                    />
                </div>
            </div>

            <!-- Bottom Actions -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <Button 
                        href="/customers" 
                        variant="outline" 
                        class="gap-2"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back to Customers
                    </Button>
                    
                    <div class="flex items-center gap-3">
                        <Button 
                            href={`/customers/${customer.id}/edit`} 
                            variant="secondary" 
                            class="gap-2"
                        >
                            <SquarePen class="h-4 w-4" />
                            Edit Customer
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AppLayout>