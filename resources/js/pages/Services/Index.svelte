<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type Flash, type BreadcrumbItem, type Filters} from '@/types';
    import { Button } from '@/components/ui/button';
    import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Search, Plus, SquarePen, Eye, EllipsisVertical, Funnel, Download } from 'lucide-svelte';
    import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
    import { Link, page, router } from '@inertiajs/svelte';
    import PaginationUi from '@/components/general/Pagination.svelte';
    import DeleteConfirmDialog from '@/components/confirm/DeleteConfirmDialog.svelte';
    import Filter from '@/components/general/Filter.svelte';
    import { toast } from 'svelte-sonner';
    import SortIcon from '@/components/general/SortIcon.svelte';
    import { changeSort, getSortIcon } from '@/lib/helper/sortUtils';
    import { type User as UserType } from '@/types';

    let { services, filters, statusOptions, sort_by, sort_dir } = $props();
    // svelte-ignore state_referenced_locally
    let localFilters = $state<Filters>({ ...filters });

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
            title: 'Services',
            href: '/services',
        },
    ];

    const user = $page.props.auth.user as UserType;


</script>

<AppLayout {breadcrumbs}>
    <div class="space-y-6">
        
        <!-- Header -->
        <div class="p-4 md:p-6 md:pb-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Services</h1>
                <p class="text-muted-foreground mt-1">
                    Manage your services and inventory
                </p>
            </div>
            <Link href="/services/create">
                <Button class="gap-2 cursor-pointer bg-blue-600 hover:bg-blue-700">
                    <Plus class="h-4 w-4" />
                    Add Service
                </Button>
            </Link>
        </div>

        <!-- Filters Card -->
        <Card class="shadow-none border-none py-0">
            <CardContent>
                <Filter 
                routePath='services.index' 
                bind:filters={localFilters} 
                {statusOptions} 
            />
            </CardContent>
        </Card>

        <!-- Services Table -->
        <Card class="shadow-none border-none  md:pt-0">
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle>Service List</CardTitle>
                    <CardDescription>
                        Showing {services.from} to {services.to} of {services.total} services
                    </CardDescription>
                </div>
                <!-- <Button variant="outline" size="sm" onclick={exportServices} class="gap-2">
                    <Download class="h-4 w-4" />
                    Exportx
                </Button> -->
            </CardHeader>
            <CardContent>
                <div class="rounded-md border overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="bg-gray-50">
                                    <TableHead class="pl-4 min-w-[120px]">
                                        <Button 
                                            variant="ghost" 
                                            class="cursor-pointer"
                                            onclick={()=>changeSort( filters, 'created_at', sort_dir, 'services.index')}
                                        >
                                            Created
                                            <SortIcon direction={getSortIcon('created_at', sort_by, sort_dir)} />
                                        </Button>
                                    </TableHead>
                                    <TableHead class="min-w-[320px]">Service Name</TableHead>
                                    <TableHead class="min-w-[100px]">Price ({user.currency_symbol})</TableHead>
                                    <TableHead class="min-w-[100px]">Status</TableHead>
                                    <TableHead class="w-20 text-center">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {#each services.data as service (service.id)}
                                    <TableRow class="hover:bg-muted/50">
                                        <TableCell class="pl-4">
                                            <Link>
                                                <div class="text-muted-foreground">
                                                    {service.created_at_formatted}
                                                </div>
                                            </Link>
                                        </TableCell>
                                        <TableCell>
                                            <Link>
                                                <div class="max-w-[320px] whitespace-normal">
                                                    <p class="font-medium line-clamp-2" title={service.name}>
                                                        {service.name}
                                                    </p>
                                                </div>
                                            </Link>
                                        </TableCell>
                                        <TableCell>
                                            <Link>
                                                <div class="font-medium">
                                                    <span class="md:hidden text-sm">Price: </span>
                                                    {service.price || '-'}
                                                </div>
                                            </Link>
                                        </TableCell>
                                        <TableCell>
                                            <Link>
                                                {service.status ? 'Active' : 'Inactive'}
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
                                                    <Link>
                                                        <DropdownMenuItem>
                                                            <Eye class="mr-2 h-4 w-4" />
                                                            View
                                                        </DropdownMenuItem>
                                                    </Link>
                                                    <Link href={`/services/${service.id}/edit`}>
                                                        <DropdownMenuItem class="mb-1">
                                                            <SquarePen class="mr-2 h-4 w-4" />
                                                            Edit
                                                        </DropdownMenuItem>
                                                    </Link>
                                                    <DeleteConfirmDialog
                                                        onConfirm={async () => router.delete(
                                                            route('services.destroy', service.id), {
                                                                preserveScroll: true,
                                                                preserveState: true
                                                            })
                                                        }
                                                        itemName={service.name}
                                                        btnSize={'default'}
                                                        title="Delete Service"
                                                        description={`This will permanently delete <b>${service.name}</b>. This action cannot be undone.`}
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
                                                    <p class="font-medium">No services found</p>
                                                    <p class="text-muted-foreground text-sm mt-1">
                                                        {filters.search ? 'Try adjusting your search or filters' : 'Get started by adding your first service'}
                                                    </p>
                                                </div>
                                                {#if !filters.search}
                                                    <Link href="/services/create">
                                                        <Button size="sm">
                                                            <Plus class="mr-2 h-4 w-4" />
                                                            Add Service
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
                    links={services.links}  
                    currentPage={services.current_page} 
                    lastPage={services.last_page}
                    bind:filters={localFilters} 
                    {sort_by}
                    {sort_dir}
                />

            </CardContent>
        </Card>
    </div>
</AppLayout>