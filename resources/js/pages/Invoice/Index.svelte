<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type Flash, type BreadcrumbItem, type Filters} from '@/types';
    import { Button } from '@/components/ui/button';
    import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Search, Plus, SquarePen, Eye, EllipsisVertical, Funnel, Download, User } from 'lucide-svelte';
    import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
    import { Link, page, router } from '@inertiajs/svelte';
    import PaginationUi from '@/components/general/Pagination.svelte';
    import DeleteConfirmDialog from '@/components/confirm/DeleteConfirmDialog.svelte';
    import { toast } from 'svelte-sonner';
    import SortIcon from '@/components/general/SortIcon.svelte';
    import { changeSort, getSortIcon } from '@/lib/helper/sortUtils';
    import Badge from '@/components/ui/badge/badge.svelte';
    import { type User as UserType } from '@/types';
    import { type InvoiceStatusOption } from '@/types/invoices';
    import Filter from '@/components/invoice/Filter.svelte';

    let { invoices, filters, statusOptions, sort_by, sort_dir, totalAmount, paidAmount } = $props();
    // svelte-ignore state_referenced_locally
    let localFilters = $state<Filters>({ ...filters });

    let pending_amount = $derived(Math.abs(totalAmount - paidAmount).toFixed(2));

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

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
        {
            title: 'Invoices',
            href: '/invoices',
        },
    ];

    const exportInvoices = () => {
        // Handle export logic
        // console.log('Exporting invoices...');
    };
    
    const user = $page.props.auth.user as UserType;

</script>

<AppLayout {breadcrumbs}>
    <div class="space-y-6">
        
        <!-- Header -->
        <div class="p-4 md:p-6 md:pb-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Orders</h1>
                <p class="text-muted-foreground mt-1">
                    Manage your orders
                </p>
            </div>
            <Link href="/invoices/create">
                <Button class="gap-2 cursor-pointer bg-blue-600 hover:bg-blue-700">
                    <Plus class="h-4 w-4" />
                    Add Invoice
                </Button>
            </Link>
        </div>

        <!-- Filters Card -->
        <Card class="shadow-none border-none py-0">
            <CardContent>
                <Filter
                routePath='invoices.index' 
                bind:filters={localFilters} 
                {statusOptions} 
            />
            </CardContent>
        </Card>

        <!-- Invoices Table -->
        <Card class="shadow-none border-none  md:pt-0">
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <!-- <CardTitle>Invoice List</CardTitle> -->
                    <CardDescription>
                        Showing {invoices.from} to {invoices.to} of {invoices.total} invoices
                        <div class="mt-2 flex items-center gap-3">
                            <Badge variant="outline" class="bg-background">
                                Total: {user.currency_symbol}{totalAmount}
                            </Badge>
                            <Badge variant="outline" class="bg-green-50 text-green-700 border-green-200">
                                Paid: {user.currency_symbol}{paidAmount}
                            </Badge>
                            <Badge variant="outline" class="bg-amber-50 text-amber-700 border-amber-200">
                                Pending: {user.currency_symbol}{pending_amount}
                            </Badge>
                        </div>
                    </CardDescription>
                </div>
                <!-- <Button variant="outline" size="sm" onclick={exportInvoices} class="gap-2">
                    <Download class="h-4 w-4" />
                    Export
                </Button> -->
            </CardHeader>
            <CardContent>
                <div class="rounded-md border overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="bg-gray-50">
                                    <TableHead class="pl-4 min-w-[60px]">Invoice No</TableHead>
                                    <TableHead class="pl-4 min-w-[120px]">Created</TableHead>
                                    <TableHead class="min-w-[180px]">Customer</TableHead>
                                    <TableHead class="min-w-[100px]">Total ({user.currency_symbol})</TableHead>
                                    <TableHead class="min-w-[100px]">Paid ({user.currency_symbol})</TableHead>
                                    <TableHead class="min-w-[100px]">Status</TableHead>
                                    <TableHead class="w-20 text-center">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {#each invoices.data as invoice (invoice.id)}
                                    <TableRow class="hover:bg-muted/50">
                                        <TableCell class="pl-4">
                                            <Link href={`/invoices/${invoice.id}`}>
                                                <div class="text-muted-foreground">
                                                    {invoice.invoice_no}
                                                </div>
                                            </Link>
                                        </TableCell>
                                        <TableCell class="pl-4">
                                            <Link href={`/invoices/${invoice.id}`}>
                                                <div class="text-muted-foreground">
                                                    {invoice.created_at_formatted}
                                                </div>
                                            </Link>
                                        </TableCell>
                                        <TableCell>
                                            <Link href={`/invoices/${invoice.id}`}>
                                                  <div class="flex items-center gap-3">
                                                        <div class="p-2 bg-blue-50 rounded-lg">
                                                                <User class="h-3.5 w-3.5 text-blue-600" />
                                                        </div>
                                                        <div class="min-w-0">
                                                            <div class="font-medium text-gray-900 max-w-[180px] truncate text-sm">
                                                                {invoice.customer.name}
                                                            </div>
                                                            <div class="text-xs text-gray-500">
                                                                Mob: {invoice.customer.phone}
                                                            </div>
                                                        </div>
                                                    </div>
                                            </Link>
                                        </TableCell>
                                        <TableCell>
                                            <Link href={`/invoices/${invoice.id}`}>
                                                <div class="font-medium">
                                                    {invoice.total_amount || '-'}
                                                </div>
                                            </Link>
                                        </TableCell>
                                        <TableCell>
                                            <Link href={`/invoices/${invoice.id}`}>
                                                <div class="font-medium">
                                                    {invoice.paid_amount || '-'}
                                                </div>
                                            </Link>
                                        </TableCell>
                                        <TableCell>
                                            <Link href={`/invoices/${invoice.id}`}>
                                            {statusOptions.find((s: InvoiceStatusOption) => s.value === invoice.status)?.label}
                                            </Link>
                                        </TableCell>
                                        <TableCell class="text-center">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger >
                                                    <Button variant="ghost" size="sm" class="cursor-pointer">
                                                        <EllipsisVertical class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <Link href={`/invoices/${invoice.id}`}>
                                                        <DropdownMenuItem>
                                                            <Eye class="mr-2 h-4 w-4" />
                                                            View
                                                        </DropdownMenuItem>
                                                    </Link>
                                                    <Link href={`/invoices/${invoice.id}/edit`}>
                                                        <DropdownMenuItem class="mb-1">
                                                            <SquarePen class="mr-2 h-4 w-4" />
                                                            Edit
                                                        </DropdownMenuItem>
                                                    </Link>
                                                    <DeleteConfirmDialog
                                                        onConfirm={async () => router.delete(
                                                            route('invoices.destroy', invoice.id), {
                                                                preserveScroll: true,
                                                                preserveState: true
                                                            })
                                                        }
                                                        itemName={invoice.invoice_no}
                                                        btnSize={'default'}
                                                        title="Delete Invoice"
                                                        description={`This will permanently delete <b>#${invoice.invoice_no}</b>. This action cannot be undone.`}
                                                        buttonText="Delete"
                                                        buttonVariant='outline'
                                                    />
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                {:else}
                                    <TableRow>
                                        <TableCell colspan={6} class="text-center py-8 px-4">
                                            <div class="flex flex-col items-center gap-3 max-w-md mx-auto">
                                                <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center">
                                                    <Search class="h-6 w-6 text-muted-foreground" />
                                                </div>
                                                <div class="text-center">
                                                    <p class="font-medium">No invoices found</p>
                                                    <p class="text-muted-foreground text-sm mt-1">
                                                        {filters.search ? 'Try adjusting your search or filters' : 'Get started by adding your first invoice'}
                                                    </p>
                                                </div>
                                                {#if !filters.search}
                                                    <Link href="/invoices/create">
                                                        <Button size="sm">
                                                            <Plus class="mr-2 h-4 w-4" />
                                                            Add Invoice
                                                        </Button>
                                                    </Link>
                                                {/if}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                {/each}
                            </TableBody>
                        </Table>
                    </div>
                </div>

                <!-- Pagination -->
                <PaginationUi 
                    links={invoices.links}  
                    currentPage={invoices.current_page} 
                    lastPage={invoices.last_page}
                    bind:filters={localFilters} 
                    {sort_by}
                    {sort_dir}
                />

            </CardContent>
        </Card>
    </div>
</AppLayout>