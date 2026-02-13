<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type Flash, type BreadcrumbItem } from '@/types';
    import { Link, page, router } from '@inertiajs/svelte';
    import { toast } from 'svelte-sonner';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
    import { Separator } from '@/components/ui/separator';
    import { Badge } from '@/components/ui/badge';

    import { 
        ArrowLeft,
        Printer,
        User,
        Calendar,
        Briefcase,
        DollarSign,
        FileText,
        MessageSquare,
        Phone,
        Mail,
        MapPin,
        Tag,
        Package,
        SquarePen,
    } from 'lucide-svelte';
    import DeleteConfirmDialog from '@/components/confirm/DeleteConfirmDialog.svelte';
    import ImageGallery from '@/components/job/ImageGallery.svelte';

    type JobStatusOption = {
        value: string;
        label: string;
        icon: string;
        color: string;
    };

    let { jobCard, customer, jobStatusOptions, deliveryDiffDays} = $props<{
        jobCard: {
            id: number;
            customer_id: number;
            item: string;
            problem: string;
            estimated_cost: string | null;
            delivery_date: string | null;
            notes: string | null;
            status: string;
            created_at: string;
            updated_at: string;
        };
        customer: {
            id: number;
            name: string;
            email: string | null;
            phone: string | null;
            address: string | null;
        };
        jobStatusOptions: JobStatusOption[],
        deliveryDiffDays: number
    }>();

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

    const breadcrumbs: BreadcrumbItem[] = $derived([
        { title: 'Job Cards', href: '/job-cards' },
        { title: `ID - ${jobCard.id}`, href: `/job-cards/${jobCard.id}` },
    ]);

    let getStatusInfo = $derived(jobStatusOptions.find((s:JobStatusOption)=> s.value === jobCard.status) || jobStatusOptions[0]);
    

    function formatCurrency(value: string | null): string {
        if (!value) return '0.00';
        const num = parseFloat(value);
        if (isNaN(num)) return '0.00';
        return value;
    }

    function formatDate(dateString: string | null): string {
        if (!dateString) return 'Not set';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function formatDateTime(dateString: string): string {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }


    function getDaysUntilDelivery(): string {
        if (!jobCard.delivery_date) return 'No date set';
        
        const diffDays = deliveryDiffDays;
        
        if (diffDays === 0) return 'Today';
        if (diffDays === 1) return 'Tomorrow';
        if (diffDays === -1) return 'Yesterday';
        if (diffDays < 0) return `${Math.abs(diffDays)} days ago`;
        return `in ${diffDays} days`;
    }

    function getDeliveryStatus(): { text: string; color: string } {
        if (!jobCard.delivery_date) return { text: 'Not scheduled', color: 'text-gray-600' };
  
        const diffDays = deliveryDiffDays;
        
        if(jobCard.status === 'delivered'){
            return { text: '', color: 'text-orange-600' }
        }
        if (diffDays < 0 && jobCard.status !== 'delivered') {
            return { text: 'Overdue', color: 'text-red-600' };
        } else if (diffDays === 0) {
            return { text: 'Due today', color: 'text-orange-600' };
        } else if (diffDays <= 3) {
            return { text: 'Due soon', color: 'text-yellow-600' };
        } else {
            return { text: 'On schedule', color: 'text-green-600' };
        }
    }
</script>

<svelte:head>
    <title>Job Card: {jobCard.item}</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="min-h-screen bg-gray-50">
        <div class="p-4 md:p-6 mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <Button 
                                onclick={(e) => {e.preventDefault(); history.back()}}
                                variant="ghost" 
                                size="sm"
                                class="p-0 h-auto cursor-pointer"
                            >
                                <ArrowLeft class="h-4 w-4 mr-2" />
                                Back
                            </Button>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h1 class="text-lg font-bold text-gray-900">
                                    Job Card #{jobCard.job_no}
                                </h1>
                                <p class="text-sm text-gray-500">
                                    Created on {formatDate(jobCard.created_at)}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge class={`px-3 py-1.5 ${getStatusInfo.color} font-medium`}>
                                    {getStatusInfo.label}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Job Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Job Details Card -->
                    <Card class="border border-gray-200 shadow-sm">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Briefcase class="h-5 w-5 text-blue-600" />
                                Job Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Item & Status -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <Package class="h-4 w-4" />
                                        <span>Item</span>
                                    </div>
                                    <p class="font-medium text-gray-900">{jobCard.item}</p>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <Tag class="h-4 w-4" />
                                        <span>Status</span>
                                    </div>
                                    <p class="font-medium text-gray-900">{getStatusInfo.label}</p>
                                </div>
                            </div>

                            <!-- Problem Description -->
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <MessageSquare class="h-4 w-4" />
                                    <span>Problem Description</span>
                                </div>
                                <div class="prose prose-sm max-w-none bg-gray-50 p-3 rounded-md border">
                                    <p class="text-gray-700 whitespace-pre-line">{jobCard.problem}</p>
                                </div>
                            </div>
                            <Separator />
                            <!-- Financial & Timeline -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Estimated Cost -->
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <DollarSign class="h-4 w-4" />
                                        <span>Estimated Cost</span>
                                    </div>
                                    <p class="font-medium text-gray-900 text-lg">
                                        {formatCurrency(jobCard.estimated_cost)}
                                    </p>
                                </div>

                                <!-- Delivery Date -->
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <Calendar class="h-4 w-4" />
                                        <span>Delivery Date</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {formatDate(jobCard.delivery_date) || 'Not set'}
                                        </p>
                                        {#if jobCard.delivery_date}
                                            <div class="flex items-center gap-2 mt-1">
                                                <p class="text-sm text-gray-500">
                                                    {getDaysUntilDelivery()}
                                                </p>
                                                <span class="text-sm font-medium {getDeliveryStatus().color}">
                                                    • {getDeliveryStatus().text}
                                                </span>
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                            </div>

                            <!-- Internal Notes -->
                            {#if jobCard.notes}
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <FileText class="h-4 w-4" />
                                        <span>Internal Notes</span>
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded-md border">
                                        <p class="text-gray-700 whitespace-pre-line text-sm">{jobCard.notes}</p>
                                    </div>
                                </div>
                            {/if}
                        </CardContent>
                    </Card>

                    <!-- Customer Information -->
                    <Card class="border border-gray-200 shadow-sm">
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle class="flex items-center gap-2">
                                    <User class="h-5 w-5 text-blue-600" />
                                    Customer Information
                                </CardTitle>
                                <Link href={`/customers/${customer.id}`}>
                                    <Button variant="ghost" size="sm" class="gap-1 cursor-pointer">
                                        <User class="h-4 w-4" />
                                        View Profile
                                    </Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <!-- Customer Header -->
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{customer.name}</h3>
                                        <p class="text-sm text-gray-500">ID: #{customer.id}</p>
                                    </div>
                                    <Link href={`/customers/${customer.id}/edit`}>
                                        <Button variant="outline" size="sm" class="gap-2 cursor-pointer">
                                            <SquarePen class="h-4 w-4" />
                                            Edit
                                        </Button>
                                    </Link>
                                </div>

                                <Separator />

                                <!-- Contact Information Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Left Column: Contact Details -->
                                    <div class="space-y-4">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-3">Contact Details</h4>
                                            <div class="space-y-3">
                                                {#if customer.phone}
                                                    <div class="flex items-center gap-3">
                                                        <div class="p-2 bg-blue-50 rounded-lg">
                                                            <Phone class="h-4 w-4 text-blue-600" />
                                                        </div>
                                                        <div>
                                                            <p class="text-sm text-gray-500">Phone</p>
                                                            <p class="font-medium text-gray-900">{customer.phone}</p>
                                                        </div>
                                                    </div>
                                                {/if}

                                                {#if customer.email}
                                                    <div class="flex items-center gap-3">
                                                        <div class="p-2 bg-blue-50 rounded-lg">
                                                            <Mail class="h-4 w-4 text-blue-600" />
                                                        </div>
                                                        <div>
                                                            <p class="text-sm text-gray-500">Email</p>
                                                            <p class="font-medium text-gray-900 break-all">{customer.email}</p>
                                                        </div>
                                                    </div>
                                                {/if}
                                            </div>
                                        </div>

                                        <!-- Quick Stats (if you have them) -->
                                        <!-- <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-3">Job History</h4>
                                            <div class="flex items-center gap-4">
                                                <div class="text-center">
                                                    <p class="text-2xl font-bold text-gray-900">5</p>
                                                    <p class="text-xs text-gray-500">Total Jobs</p>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-2xl font-bold text-green-600">3</p>
                                                    <p class="text-xs text-gray-500">Completed</p>
                                                </div>
                                            </div>
                                        </div> -->
                                    </div>

                                    <!-- Right Column: Address & Notes -->
                                    <div class="space-y-4">
                                        {#if customer.address}
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-700 mb-3">Address</h4>
                                                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                                    <MapPin class="h-4 w-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                                    <p class="text-sm text-gray-700 whitespace-pre-line">{customer.address}</p>
                                                </div>
                                            </div>
                                        {/if}

                                        <!-- Customer Notes (if you have this field) -->
                                        {#if customer.notes}
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-700 mb-3">Notes</h4>
                                                <div class="p-3 bg-amber-50 border border-amber-100 rounded-lg">
                                                    <p class="text-sm text-gray-700">{customer.notes}</p>
                                                </div>
                                            </div>
                                        {/if}
                                    </div>
                                </div>

                                <!-- Actions Row -->
                                <div class="pt-4 border-t">
                                    <div class="flex flex-wrap gap-2">
                                        {#if customer.phone}
                                            <a href={`tel:${customer.phone}`}>
                                                <Button variant="outline" size="sm" class="gap-2 cursor-pointer">
                                                    <Phone class="h-4 w-4" />
                                                    Call
                                                </Button>
                                            </a>
                                        {/if}
                                        {#if customer.email}
                                            <a href={`mailto:${customer.email}`}>
                                                <Button variant="outline" size="sm" class="gap-2 cursor-pointer">
                                                    <Mail class="h-4 w-4" />
                                                    Email
                                                </Button>
                                            </a>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <ImageGallery
                        images={jobCard.files || []}
                    />

                </div>

                <!-- Right Column - Actions & Timeline -->
                <div class="space-y-6">
                    <!-- Actions Card -->
                    <Card class="border border-gray-200 shadow-sm">
                        <CardHeader>
                            <CardTitle>Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Link href={`/job-cards/${jobCard.id}/edit`}>
                                <Button class="w-full gap-3 justify-start bg-blue-600 hover:bg-blue-700 text-white mb-4 cursor-pointer">
                                    <SquarePen class="h-4 w-4 " />
                                    Edit Job Card
                                </Button>
                            </Link>

                            <Button 
                                onclick={(e) => {e.preventDefault(); history.back()}}
                                variant="outline" class="w-full gap-3 justify-start cursor-pointer">
                                <ArrowLeft class="h-4 w-4" />
                                Back
                            </Button>
                            <DeleteConfirmDialog
                                onConfirm={async () => router.delete(route('job-cards.destroy', jobCard.id), {
                                    preserveScroll: true})
                                }
                                itemName={jobCard.item}
                                btnSize={'default'}
                                title="Delete Job Card"
                                description={`This will permanently delete <b>#${jobCard.job_no}</b>. This action cannot be undone.`}
                                buttonText="Delete"
                                triggerClass="cursor-pointer"
                            />
                        </CardContent>
                    </Card>

                    <!-- Job Information Card -->
                    <Card class="border border-gray-200 shadow-sm">
                        <CardHeader>
                            <CardTitle>Job Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Job ID:</span>
                                    <span class="font-medium text-gray-900">#{jobCard.id}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Status:</span>
                                    <span class="font-medium text-gray-900">{getStatusInfo.label}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Created:</span>
                                    <span class="font-medium text-gray-900">{formatDateTime(jobCard.created_at)}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Last Updated:</span>
                                    <span class="font-medium text-gray-900">{formatDateTime(jobCard.updated_at)}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Customer:</span>
                                    <span class="font-medium text-gray-900 text-right">{customer.name}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Estimated Cost:</span>
                                    <span class="font-medium text-gray-900">{formatCurrency(jobCard.estimated_cost)}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Delivery Date:</span>
                                    <span class="font-medium text-gray-900">
                                        {formatDate(jobCard.delivery_date) || 'Not set'}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Timeline Card -->
                    <Card class="border border-gray-200 shadow-sm">
                        <CardHeader>
                            <CardTitle>Timeline</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <div class="flex items-start gap-3">
                                        <div class="relative">
                                            <div class="h-2 w-2 bg-green-500 rounded-full mt-1.5"></div>
                                            <div class="absolute left-0.5 top-4 bottom-0 w-px bg-gray-200"></div>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Job Created</p>
                                            <p class="text-xs text-gray-500">{formatDateTime(jobCard.created_at)}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="relative">
                                            <div class="h-2 w-2 bg-blue-500 rounded-full mt-1.5"></div>
                                            <div class="absolute left-0.5 top-4 bottom-0 w-px bg-gray-200"></div>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Last Updated</p>
                                            <p class="text-xs text-gray-500">{formatDateTime(jobCard.updated_at)}</p>
                                        </div>
                                    </div>
                                    {#if jobCard.delivery_date}
                                        <div class="flex items-start gap-3">
                                            <div class="relative">
                                                <div class="h-2 w-2 bg-purple-500 rounded-full mt-1.5"></div>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Delivery Scheduled</p>
                                                <p class="text-xs text-gray-500">
                                                    {formatDate(jobCard.delivery_date)} ({getDaysUntilDelivery()})
                                                </p>
                                            </div>
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        </CardContent>

                    </Card>

                    <!-- Print Footer (only visible when printing) -->
                    <div class="hidden mt-8 pt-4 border-t">
                        <div class="text-center text-sm text-gray-500">
                            <p>Job Card #{jobCard.id} • Generated on {formatDate(new Date().toISOString())}</p>
                            <p class="mt-1">{window.location.origin}/job-cards/{jobCard.id}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AppLayout>
