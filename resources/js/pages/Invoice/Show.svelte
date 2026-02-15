<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
    import { Eye, Printer, Download, Mail, Edit, Calendar, User, FileText, DollarSign, Hash, MapPin, Phone, Mail as MailIcon, Globe } from 'lucide-svelte';
    import { format } from 'date-fns';
    import { type Invoice, type InvoiceItem, type InvoiceStatusOption } from '@/types/invoices';
    import { type Customer } from '@/types/customers';
    import Badge from '@/components/ui/badge/badge.svelte';
    import { page } from '@inertiajs/svelte';
    import { type Flash } from '@/types';
    import { toast } from 'svelte-sonner';
    import { Link } from '@/components/ui/breadcrumb';
    import { type User as UserType } from '@/types';

    let { invoice, customer, invoiceItems, invoiceStatusOptions } = $props() as {
        invoice: Invoice;
        customer: Customer;
        invoiceItems: InvoiceItem[];
        invoiceStatusOptions: InvoiceStatusOption[];
    };

    const breadcrumbs = $derived([
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Invoices', href: '/invoices' },
        { title: `Invoice #${invoice.invoice_no}`, href: `/invoices/${invoice.id}` },
    ]);

    function getStatusColor(status: string): string {
        const colors: Record<string, string> = {
            draft: 'bg-gray-100 text-gray-800',
            sent: 'bg-blue-100 text-blue-800',
            paid: 'bg-green-100 text-green-800',
            overdue: 'bg-red-100 text-red-800',
            cancelled: 'bg-gray-100 text-gray-800',
            partial: 'bg-yellow-100 text-yellow-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }

    function calculateItemTotal(item: InvoiceItem): number {
        return (item.quantity * item.unit_price) * (1 + (item.tax_rate / 100));
    }

    const subtotal = $derived(invoiceItems.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0));
    const taxTotal = $derived(invoiceItems.reduce((sum, item) => sum + ((item.quantity * item.unit_price) * (item.tax_rate / 100)), 0));
    const total_before_discount = $derived(subtotal + taxTotal);
    const total = $derived(Math.max(total_before_discount - invoice.discount_amount, 0));

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

    const user = $page.props.auth.user as UserType;

</script>

<AppLayout {breadcrumbs}>
    <div class="container mx-auto p-4">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Invoice #{invoice.invoice_no}</h1>
                    <p class="text-gray-600">
                        {format(new Date(invoice.created_at), 'MMMM dd, yyyy')}
                        <span class="mx-2">•</span>
                        <!-- Due: {format(new Date(invoice.due_date), 'MMMM dd, yyyy')} -->
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Badge class={`${getStatusColor(invoice.status)} px-3 py-1 font-medium`}>
                        {invoiceStatusOptions.find(s => s.value === invoice.status)?.label || invoice.status}
                    </Badge>
                    <div class="flex gap-2">
                        <Button variant="outline" size="sm" onclick={() => window.print()}>
                            <Printer class="h-4 w-4 mr-2" />
                            Print
                        </Button>
                        <Button variant="outline" size="sm">
                            <Download class="h-4 w-4 mr-2" />
                            Download PDF
                        </Button>
                        <Button variant="outline" size="sm">
                            <Mail class="h-4 w-4 mr-2" />
                            Send
                        </Button>
                        <Button size="sm" onclick={() => window.location.href = `/invoices/${invoice.id}/edit`}>
                            <Edit class="h-4 w-4 mr-2" />
                            Edit
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Invoice Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Invoice Items Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" />
                            Invoice Items
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader class="bg-gray-50">
                                    <TableRow>
                                        <TableHead class="font-semibold w-2/3">Description</TableHead>
                                        <TableHead class="font-semibold text-center w-16">Qty</TableHead>
                                        <TableHead class="font-semibold text-center w-32">Unit Price</TableHead>
                                        <TableHead class="font-semibold text-center w-24">Tax</TableHead>
                                        <TableHead class="font-semibold text-right w-32">Total</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {#each invoiceItems as item}
                                        <TableRow class="hover:bg-gray-50">
                                            <TableCell class="font-medium">
                                                <div class="pr-4">
                                                    <div class="font-medium text-gray-900">{item.name}</div>
                                                </div>
                                            </TableCell>
                                            <TableCell class="text-center align-top">{item.quantity}</TableCell>
                                            <TableCell class="text-center align-top">{Number(item.unit_price).toFixed(2)}</TableCell>
                                            <TableCell class="text-center align-top">{item.tax_rate}%</TableCell>
                                            <TableCell class="text-right align-top font-semibold">
                                                {Number(calculateItemTotal(item)).toFixed(2)}
                                            </TableCell>
                                        </TableRow>
                                    {/each}
                                    <!-- Summary Row -->
                                    <TableRow class="bg-gray-50 border-t-2">
                                        <TableCell colspan={3} class="text-right font-semibold">Subtotal</TableCell>
                                        <TableCell colspan={2} class="text-right font-semibold">
                                            {user.currency_symbol}{subtotal}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow class="bg-gray-50">
                                        <TableCell colspan={3} class="text-right font-semibold">Tax</TableCell>
                                        <TableCell colspan={2} class="text-right font-semibold">
                                            {user.currency_symbol}{taxTotal}
                                        </TableCell>
                                    </TableRow>
                                    {#if invoice.discount_amount > 0}
                                    <TableRow class="bg-gray-50">
                                        <TableCell colspan={3} class="text-right font-semibold">Discount</TableCell>
                                        <TableCell colspan={2} class="text-right font-semibold">
                                            {user.currency_symbol}{invoice.discount_amount}
                                        </TableCell>
                                    </TableRow>
                                    {/if}
                                    <TableRow class="bg-gray-100 border-t-2">
                                        <TableCell colspan={3} class="text-right text-lg font-bold">Total</TableCell>
                                        <TableCell colspan={2} class="text-right text-lg font-bold text-primary">
                                            {user.currency_symbol}{Number(total).toFixed(2)}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>

                <!-- Notes & Terms Card -->
                {#if invoice.notes}
                    <Card>
                        <CardHeader>
                            <CardTitle>Notes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="prose max-w-none">
                                <div class="whitespace-pre-line text-gray-700">{invoice.notes}</div>
                            </div>
                        </CardContent>
                    </Card>
                {/if}
            </div>

            <!-- Right Column - Information & Actions -->
            <div class="space-y-6">

                <!-- Customer Information Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <User class="h-5 w-5" />
                            Bill To
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        {#if customer}
                            <div class="font-semibold text-lg">{customer.name}</div>
                            {#if customer.address}
                                <div class="flex items-start gap-2 text-gray-600">
                                    <MapPin class="h-4 w-4 mt-0.5 flex-shrink-0" />
                                    <div class="whitespace-pre-line">{customer.address}</div>
                                </div>
                            {/if}
                            {#if customer.phone}
                                <div class="flex items-center gap-2 text-gray-600">
                                    <Phone class="h-4 w-4" />
                                    <div>{customer.phone}</div>
                                </div>
                            {/if}
                            {#if customer.email}
                                <div class="flex items-center gap-2 text-gray-600">
                                    <MailIcon class="h-4 w-4" />
                                    <div>{customer.email}</div>
                                </div>
                            {/if}
                              <div class="pt-3 mt-2 border-t">
                                <Link href={`/customers/${customer.id}`}>
                                    <Button 
                                        variant="outline" 
                                        size="sm" 
                                        class="w-full cursor-pointer"
                                    >
                                        <User class="h-4 w-4 mr-2" />
                                        View Customer Profile
                                    </Button>
                                </Link>
                            </div>
                        {:else}
                            <div class="text-gray-500 italic">No customer information available</div>
                        {/if}
                    </CardContent>
                </Card>

                <!-- Invoice Actions Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Actions</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <Button class="w-full" onclick={() => window.location.href = `/invoices/${invoice.id}/edit`}>
                            <Edit class="h-4 w-4 mr-2" />
                            Edit Invoice
                        </Button>
                        <Button variant="outline" class="w-full" onclick={() => window.print()}>
                            <Printer class="h-4 w-4 mr-2" />
                            Print Invoice
                        </Button>
                        <Button variant="outline" class="w-full">
                            <Download class="h-4 w-4 mr-2" />
                            Download PDF
                        </Button>
                        <Button variant="outline" class="w-full">
                            <Mail class="h-4 w-4 mr-2" />
                            Send to Customer
                        </Button>
                        {#if invoice.status === 'sent' || invoice.status === 'partial'}
                            <Button variant="outline" class="w-full bg-green-50 text-green-700 hover:bg-green-100">
                                <DollarSign class="h-4 w-4 mr-2" />
                                Record Payment
                            </Button>
                        {/if}
                    </CardContent>
                </Card>

                <!-- Timeline Card (Optional) -->
                <Card>
                    <CardHeader>
                        <CardTitle>Invoice History</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="h-2 w-2 mt-2 rounded-full bg-green-500"></div>
                                <div>
                                    <div class="font-medium">Created</div>
                                    <div class="text-sm text-gray-500">
                                        {format(new Date(invoice.created_at), 'MMM dd, yyyy h:mm a')}
                                    </div>
                                </div>
                            </div>
                            {#if invoice.updated_at !== invoice.created_at}
                                <div class="flex items-start gap-3">
                                    <div class="h-2 w-2 mt-2 rounded-full bg-blue-500"></div>
                                    <div>
                                        <div class="font-medium">Last Updated</div>
                                        <div class="text-sm text-gray-500">
                                            {format(new Date(invoice.updated_at), 'MMM dd, yyyy h:mm a')}
                                        </div>
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</AppLayout>