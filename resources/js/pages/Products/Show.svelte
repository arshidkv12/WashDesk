<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Badge } from '@/components/ui/badge';
    import { Separator } from '@/components/ui/separator';
    import {
        ArrowLeft,
        SquarePen,
        Package,
        Percent,
        Hash,
        Barcode,
        CircleCheckBig,
        CircleX,
        CircleAlert,
        ExternalLink,
        ChevronRight,
        FileText,
        CreditCard,
        Box
    } from 'lucide-svelte';
    import { type Flash, type BreadcrumbItem } from '@/types';
    import { page, router } from '@inertiajs/svelte';
    import { toast } from 'svelte-sonner';
    import DeleteConfirmDialog from '@/components/confirm/DeleteConfirmDialog.svelte';

    interface Product {
        id: number;
        name: string;
        sku?: string;
        description?: string;
        price: string | number;
        tax: string | number;
        quantity: string | number;
        barcode?: string;
        status: string | boolean;
        image?: string;
        created_at: string;
        updated_at: string;
    }

    let { product, currencySymbol = '$' }: { product: Product; currencySymbol?: string } = $props();

    const breadcrumbs: BreadcrumbItem[] = $derived([
        {
            title: 'Products',
            href: '/products',
        },
        {
            title: `ID - ${product.id}`,
            href: `/products/${product.id}`,
        },
    ]);

    // Formatting function for generic currency
    function formatCurrency(amount: number | string): string {
        const num = typeof amount === 'string' ? parseFloat(amount) : Number(amount);
        if (isNaN(num)) return `${currencySymbol}0.00`;
        return `${currencySymbol}${num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}`;
    }

    // Calculate price with tax
    const priceWithTax = $derived.by(() => {
        const price = Number(product.price) || 0;
        const tax = Number(product.tax) || 0;
        return price + (price * tax) / 100;
    });


    const stockStatus = $derived.by(() => {
        const quantity = Number(product.quantity) || 0;
        if (quantity <= 0) return { text: 'Out of Stock', variant: 'destructive' as const, icon: CircleX };
        if (quantity <= 10) return { text: 'Low Stock', variant: 'outline' as const, icon: CircleAlert };
        return { text: 'In Stock', variant: 'secondary' as const, icon: CircleCheckBig };
    });

    $effect(()=>{
         const flash = $page.flash as Flash;
        if (flash?.message) {  
            if (flash.type === 'success') {
                toast.success(flash.message);
            } else if (flash.type === 'error') {
                toast.error(flash.message);
            }
        }
    })

</script>

<AppLayout {breadcrumbs}>
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
        <!-- Header -->
        <div class="bg-white border-b">
            <div class="p-6 max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <Button
                                onclick={() => history.back()}
                                variant="ghost"
                                size="sm"
                                class="gap-2"
                            >
                                <ArrowLeft class="h-4 w-4" />
                                Back
                            </Button>
                        </div>
                        
                        <div class="space-y-2">
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{product.name}</h1>
                            
                            <div class="flex items-center gap-3 flex-wrap">
                                {#if product.sku}
                                    <Badge variant="outline" class="gap-1">
                                        <Package class="h-3 w-3" />
                                        SKU: {product.sku}
                                    </Badge>
                                {/if}
                                
                                {#if product.barcode && product.barcode.toString().trim() !== ''}
                                    <Badge variant="outline" class="gap-1">
                                        <Barcode class="h-3 w-3" />
                                        Barcode: {product.barcode}
                                    </Badge>
                                {/if}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <Button
                            href={`/products/${product.id}/edit`}
                            class="gap-2 bg-blue-600 hover:bg-blue-700"
                        >
                            <SquarePen class="h-4 w-4" />
                            Edit Product
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="p-4 md:p-6 lg:p-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Product Details -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Product Information Card -->
                    <Card class="border border-gray-200 shadow-sm">
                        <CardHeader class="bg-gradient-to-r from-blue-50 to-blue-100/50">
                            <CardTitle class="flex items-center gap-3">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <Package class="h-5 w-5 text-blue-600" />
                                </div>
                                <span>Product Information</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-6 space-y-6">
                            <!-- Product Name -->
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Product Name</p>
                                <p class="text-xl font-semibold text-gray-900">{product.name}</p>
                            </div>

                            <!-- SKU Details -->
                            {#if product.sku}
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <Package class="h-5 w-5 text-gray-500" />
                                        <p class="text-sm font-medium text-gray-500">SKU Details</p>
                                    </div>
                                    <div class="bg-gray-100 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <code class="font-mono text-lg font-semibold">{product.sku}</code>
                                                <p class="text-xs text-gray-500 mt-1">Stock Keeping Unit</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/if}

                            <!-- Barcode Details -->
                            {#if product.barcode && product.barcode.toString().trim() !== ''}
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <Barcode class="h-5 w-5 text-gray-500" />
                                        <p class="text-sm font-medium text-gray-500">Barcode Details</p>
                                    </div>
                                    <div class="bg-gray-100 p-4 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <code class="font-mono text-lg font-semibold">{product.barcode}</code>
                                                <p class="text-xs text-gray-500 mt-1">Scan this barcode</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/if}

                            <!-- Description Section - More Prominent -->
                            {#if product.description && product.description.trim() !== ''}
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <FileText class="h-5 w-5 text-gray-500" />
                                        <p class="text-sm font-medium text-gray-500">Description</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 border">
                                        <p class="text-gray-800 whitespace-pre-line leading-relaxed">
                                            {product.description}
                                        </p>
                                    </div>
                                </div>
                            {/if}
                        </CardContent>
                    </Card>

                    <!-- Pricing & Inventory Details -->
                    <Card class="border border-gray-200 shadow-sm">
                        <CardHeader class="bg-gradient-to-r from-green-50 to-green-100/50">
                            <CardTitle class="flex items-center gap-3">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <CreditCard class="h-5 w-5 text-green-600" />
                                </div>
                                <span>Pricing & Inventory</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Pricing Section -->
                                <div class="space-y-6">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                        <CreditCard class="h-5 w-5 text-green-600" />
                                        Pricing Details
                                    </h3>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Base Price</span>
                                            <span class="font-medium text-gray-900">{formatCurrency(product.price)}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600 flex items-center gap-2">
                                                <Percent class="h-4 w-4" />
                                                Tax Rate
                                            </span>
                                            <span class="font-medium text-gray-900">{product.tax}%</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Tax Amount</span>
                                            <span class="font-medium text-gray-900">
                                                {formatCurrency((Number(product.price) * Number(product.tax)) / 100)}
                                            </span>
                                        </div>
                                        <Separator />
                                        <div class="flex justify-between items-center pt-2">
                                            <span class="text-lg font-semibold text-gray-900">Final Price</span>
                                            <span class="text-2xl font-bold text-blue-600">
                                                {formatCurrency(priceWithTax)}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inventory Section -->
                                <div class="space-y-6">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                        <Box class="h-5 w-5 text-blue-600" />
                                        Inventory Details
                                    </h3>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600 flex items-center gap-2">
                                                <Hash class="h-4 w-4" />
                                                Current Stock
                                            </span>
                                            <div class="flex items-center gap-2">
                                                <span class="text-2xl font-bold text-gray-900">{product.quantity}</span>
                                                <Badge variant={stockStatus.variant}>
                                                    {stockStatus.text}
                                                </Badge>
                                            </div>
                                        </div>
                                        
                                        <!-- Stock Status Indicator -->
                                        <div class="mt-4">
                                            <div class="flex items-center justify-between text-sm mb-2">
                                                <span class="text-gray-600">Stock Level</span>
                                                <span class="font-medium">{product.quantity} units</span>
                                            </div>
                                            {#if Number(product.quantity) <= 10}
                                                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                                    <div class="flex items-center gap-2">
                                                        <CircleAlert class="h-4 w-4 text-red-500" />
                                                        <span class="text-sm text-red-700 font-medium">
                                                            Low stock warning
                                                        </span>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Product Image Section -->
                    {#if product.image && product.image.trim() !== ''}
                        <Card class="border border-gray-200 shadow-sm">
                            <CardHeader class="bg-gradient-to-r from-purple-50 to-purple-100/50">
                                <CardTitle class="flex items-center gap-3">
                                    <div class="p-2 bg-purple-100 rounded-lg">
                                        <Box class="h-5 w-5 text-purple-600" />
                                    </div>
                                    <span>Product Image</span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="p-6">
                                <div class="flex justify-center">
                                    <div class="relative max-w-md w-full">
                                        <img 
                                            src={product.image} 
                                            alt={product.name}
                                            class="w-full h-auto max-h-96 object-contain rounded-lg border shadow-sm"
                                            loading="lazy"
                                        />
                                        <div class="absolute top-4 right-4 flex gap-2">
                                            <Button
                                                variant="secondary"
                                                size="sm"
                                                class="bg-white/90 backdrop-blur-sm"
                                                onclick={() => window.open(product.image!, '_blank')}
                                            >
                                                <ExternalLink class="h-4 w-4 mr-2" />
                                                Open Full Size
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    {/if}
                </div>

                <!-- Right Column - Summary & Actions -->
                <div class="space-y-8">
                    <!-- Quick Stats Card -->
                    <Card class="border border-gray-200 shadow-sm">
                        <CardHeader>
                            <CardTitle>Product Summary</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Status</span>
                                    <Badge variant={product.status === '1' || product.status === true ? 'secondary' : 'destructive'}>
                                        {product.status === '1' || product.status === true ? 'Active' : 'Inactive'}
                                    </Badge>
                                </div>
                                <Separator />
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Stock Status</span>
                                    <Badge variant={stockStatus.variant}>
                                        {stockStatus.text}
                                    </Badge>
                                </div>
                                <Separator />
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Current Price</span>
                                    <span class="font-medium text-gray-900">
                                        {formatCurrency(priceWithTax)}
                                    </span>
                                </div>
                                <Separator />
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Available Stock</span>
                                    <span class="font-medium text-gray-900">
                                        {product.quantity} units
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions Card -->
                    <Card class="border border-gray-200 shadow-sm">
                        <CardHeader>
                            <CardTitle>Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Button
                                href={`/products/${product.id}/edit`}
                                class="w-full gap-3 justify-start bg-blue-600 hover:bg-blue-700"
                            >
                                <SquarePen class="h-4 w-4" />
                                Edit Product
                            </Button>
                            
                            
                            <DeleteConfirmDialog
                                onConfirm={async () => router.delete(
                                    route('products.destroy', product.id), {
                                        preserveScroll: true,
                                        preserveState: true
                                    })
                                }
                                itemName={product.name}
                                btnSize={'default'}
                                title="Delete Product"
                                description={`This will permanently delete <b>${product.name}</b>. This action cannot be undone.`}
                                buttonText="Delete"
                                buttonVariant='outline'
                            />
                        </CardContent>
                    </Card>

                    <!-- Quick Links -->
                    <Card class="border border-gray-200 shadow-sm">
                        <CardHeader>
                            <CardTitle>Quick Links</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button
                                variant="ghost"
                                class="w-full justify-between"
                                href="/products"
                            >
                                <div class="flex items-center gap-3">
                                    <Package class="h-4 w-4" />
                                    <span>All Products</span>
                                </div>
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                class="w-full justify-between"
                                href="/products/create"
                            >
                                <div class="flex items-center gap-3">
                                    <Package class="h-4 w-4" />
                                    <span>Create New Product</span>
                                </div>
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</AppLayout>