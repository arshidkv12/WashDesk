<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Textarea } from '@/components/ui/textarea';
    import * as  Select from '@/components/ui/select';
    import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
    import { Plus, Trash2, Calendar, Save, User, FileText } from 'lucide-svelte';
    import { format } from 'date-fns';
    import CustomerSelect from '@/components/customer/CustomerSelect.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Form, page } from '@inertiajs/svelte';
    import { type BaseFormSnippetProps } from '@/types/forms';
    import ProductSelect from '@/components/general/ProductSelect.svelte';
    import { type Service } from '@/types/services';
    import { type InvoiceItem, type Invoice, type InvoiceStatusOption } from '@/types/invoices';
    import _, { uniqueId } from 'lodash';
    import { onMount } from 'svelte';
    import { type Customer } from '@/types/customers';
    import { type User as UserType } from '@/types';

    let { customers, csrf_token, invoice, invoiceItems, invoiceStatusOptions } = $props() as {
        customers: Customer[];
        csrf_token: string;
        invoice: Invoice;
        invoiceItems: InvoiceItem[],
        invoiceStatusOptions: InvoiceStatusOption[]
    };    

    let customer_id = $state(0);
    let notes = $state('');

    const breadcrumbs = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Invoices', href: '/invoices' },
        { title: 'Create Invoice', href: '/invoices/create' },
    ];

    let status = $state('draft');

    let items = $state<InvoiceItem[]>([]);

    function calculateTotal(quantity: number, unit_price:number, tax: number = 1){
        const total = quantity * unit_price;
        const taxAmount = total * (tax / 100);
        return total + taxAmount;
    }

    function updateItemTotal(quantity: number, unit_price: number, tax: number) {
        const subtotal = quantity * unit_price;
        const tax_amount = subtotal * (tax / 100);
        return subtotal + tax_amount;
    }

    onMount(()=>{ 
        items = invoiceItems;
        customer_id = invoice.customer_id;
        notes = invoice.notes ?? '';
        status = invoice.status;
    });

    const user = $page.props.auth.user as UserType;

</script>

<AppLayout {breadcrumbs}>
    <div class="container mx-auto p-4">
        <Form
            method="put" 
            action={route('invoices.update', invoice.id)} 
            class="space-y-6">
            {#snippet children({ errors, processing }: BaseFormSnippetProps)}
        
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Create Invoice</h1>
                <p class="text-gray-600">Fill in the details below to create a new invoice</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Left Column - Invoice Details -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Client Information Card -->
                    <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                        <User class="h-5 w-5" />
                        Client Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Select Customer *</Label>
                                <CustomerSelect initCustomers={customers}  bind:modelValue={customer_id} />
                                <InputError class="mt-1" message={errors.customer_id} />
                            </div>
                        </div>
                    </CardContent>
                    </Card>

                    <!-- Invoice Items Card -->
                    <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" />
                            Invoice Items
                        </CardTitle>
                        <Button type="button" size="sm" onclick={() => {
                            items = [...items, { 
                                id: uniqueId('p-'), 
                                name: '', 
                                quantity: 1, 
                                unit_price: 0, 
                                tax_rate: 0, 
                                line_tax: 0,
                                line_total: 0 
                            }];
                        }}>
                        <Plus class="h-4 w-4 mr-2" />
                        Add Item
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <ProductSelect onSelect={(service:Service)=>{
                            const existingIndex = items.findIndex(item => item.id === String(service.id));
                            if (existingIndex >= 0) {
                                items[existingIndex].quantity += 1;
                                items[existingIndex].line_total = calculateTotal(
                                    items[existingIndex].quantity, 
                                    items[existingIndex].unit_price, 
                                    items[existingIndex].tax_rate,
                                );
                            } else {
                                items = [...items, { 
                                    id: String(service.id), 
                                    name: service.name, 
                                    quantity: 1, 
                                    unit_price: service.price, 
                                    tax_rate: service.tax, 
                                    service_id: service.id, 
                                    line_total: calculateTotal(1, service.price, service.tax)
                                }];
                            }
                        }} />
                        <Table>
                        <TableHeader>
                            <TableRow>
                            <TableHead>Description</TableHead>
                            <TableHead class="w-24">Quantity</TableHead>
                            <TableHead class="w-32">Unit Price</TableHead>
                            <TableHead class="w-24">Tax %</TableHead>
                            <TableHead class="w-32">Total</TableHead>
                            <TableHead class="w-12"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {#each items as item (item.id)}
                            <TableRow>
                                <TableCell>
                                <Input
                                    name={`items[${item.id}].name`}
                                    bind:value={item.name}
                                    placeholder="Item description"
                                    class={errors?.[`items.${item.id}.name`] ? 'border-red-500 focus:border-red-500' : ''}
                                />
                                </TableCell>
                                <TableCell>
                                <Input
                                    name={`items[${item.id}].quantity`}
                                    type="number"
                                    min="1"
                                    bind:value={item.quantity}
                                    oninput={(e) => {
                                        item.line_total = updateItemTotal(item.quantity, item.unit_price, item.tax_rate);
                                    }}
                                    class={errors?.[`items.${item.id}.quantity`] ? 'border-red-500 focus:border-red-500' : ''}
                                />
                                </TableCell>
                                <TableCell>
                                <Input
                                    name={`items[${item.id}].unit_price`}
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    bind:value={item.unit_price}
                                    oninput={(e) => {
                                        item.line_total = updateItemTotal(item.quantity, item.unit_price, item.tax_rate);
                                    }}
                                    class={errors?.[`items.${item.id}.unit_price`] ? 'border-red-500 focus:border-red-500' : ''}
                                />
                                </TableCell>
                                <TableCell>
                                    <Input
                                        name={`items[${item.id}].tax_rate`}
                                        type="number"
                                        min="0"
                                        bind:value={item.tax_rate}
                                        oninput={(e) => {
                                            item.line_total = updateItemTotal(item.quantity, item.unit_price, item.tax_rate);
                                        }}
                                    />
                                    <input type="hidden" name={`items[${item.id}].item_type`}  value="service"/>
                                </TableCell>
                                <TableCell class="font-medium">
                                {Number(item.line_total).toFixed(2)}
                                </TableCell>
                                <TableCell>
                                    <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    onclick={() => {
                                        items = items.filter(i => i.id !== item.id);
                                    }}
                                    >
                                    <Trash2 class="h-4 w-4 text-red-500" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                            {/each}
                        </TableBody>
                        </Table>
                        <InputError class="mt-1" message={errors.items} />
                    </CardContent>
                    </Card>

                    <!-- Notes Card -->
                    <Card>
                    <CardHeader>
                        <CardTitle>Notes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Textarea
                        name="notes"
                        bind:value={notes}
                        placeholder="Additional notes or terms..."
                        rows={3}
                        />
                    </CardContent>
                    </Card>
                </div>

                <!-- Right Column - Summary -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Status Card -->
                    <Card>
                    <CardHeader>
                        <CardTitle>Invoice Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Select.Root type="single" bind:value={status} name="status">
                            <Select.Trigger class="w-full">
                                {invoiceStatusOptions.find((s: InvoiceStatusOption) => s.value === status)?.label 
                                || 'Select Status'
                                }
                            </Select.Trigger>
                            <Select.Content>
                                {#each invoiceStatusOptions as option}
                                <Select.Item value={option.value}>{option.label}</Select.Item>
                                {/each}
                            </Select.Content>
                            </Select.Root>
                    </CardContent>
                    </Card>

                    <!-- Summary Card -->
                    <Card>
                    <CardHeader>
                        <CardTitle>Summary</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">
                            {user.currency_symbol}{Number(items.reduce((sum, item) => sum + (item.unit_price * item.quantity ), 0)).toFixed(2)}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">GST</span>
                            <span class="font-medium">
                            {user.currency_symbol}{Number(items.reduce((sum, item) => sum + (item.tax_rate/100 * item.unit_price * item.quantity ), 0)).toFixed(2)}
                            </span>
                        </div>
                        <div class="flex justify-between pt-2 border-t">
                            <span class="text-lg font-semibold">Total</span>
                            <span class="text-lg font-bold">
                            {user.currency_symbol}{Number(items.reduce((sum, item) => sum + Number(item.line_total), 0)).toFixed(2)}
                            </span>
                        </div>
                        </div>
                    </CardContent>
                    </Card>

                    <!-- Actions Card -->
                    <Card>
                    <CardContent class="pt-6">
                        <div class="space-y-3">
                        <Button class="w-full" type="submit" disabled={processing}>
                            <Save class="h-4 w-4 mr-2" />
                             {#if processing}
                                <div class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                                Saving...
                            {:else}
                                <Save class="h-4 w-4" />
                                Save Invoice
                            {/if}
                        </Button>
                        
                        </div>
                    </CardContent>
                    </Card>
                </div>
            </div>
            {/snippet}
        </Form>
  </div>
</AppLayout>