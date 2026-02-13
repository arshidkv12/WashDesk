<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Textarea } from '@/components/ui/textarea';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Label } from '@/components/ui/label';
    import { Alert, AlertDescription } from '@/components/ui/alert';
    import { Save, ArrowLeft, Upload, X, DollarSign, Percent, Hash, Barcode } from 'lucide-svelte';
    import { type BreadcrumbItem } from '@/types';
    import { Form, Link, page, router } from '@inertiajs/svelte';
    import SingleSelect from '@/components/general/SingleSelect.svelte';
    import { type BaseFormSnippetProps } from '@/types/forms';
    import { type User as UserType } from '@/types';

    let form = $state({
        name: '',
        description: '',
        image: null as File | null,
        barcode: '',
        price: '',
        tax: '0',
        quantity: '0',
        status: '1',
    });

    let imagePreview = $state('');
    let fileInput: HTMLInputElement;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
        {
            title: 'Services',
            href: '/services',
        },
        {
            title: 'Create Service',
            href: '/services/create',
        },
    ];

    const statusOptions = [
        { value: '1', label: 'Active' },
        { value: '0', label: 'Inactive' },
    ];


    const handleImageUpload = (event: Event) => {
        const target = event.target as HTMLInputElement;
        if (target.files && target.files[0]) {
            const file = target.files[0];
            form.image = file;
            
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview = e.target?.result as string;
            };
            reader.readAsDataURL(file);
        }
    };

    const removeImage = () => {
        form.image = null;
        imagePreview = '';
    };

    let taxVal = $derived.by(() => {
        const price = Number(form.price) || 0;
        const tax = Number(form.tax) || 0;
        return ((price * tax) / 100)?.toFixed(2);
    });

    let finalPrice = $derived.by(() => {
        const price = Number(form.price) || 0;
        const tax = Number(form.tax) || 0;

        return (price + (price * tax) / 100)?.toFixed(2);
    });

    const generateBarcode = () => {
        const prefix = 'PRD';
        const random = Math.floor(100000 + Math.random() * 900000);
        form.barcode = `${prefix}${random}`;
    };

    const user = $page.props.auth.user as UserType;
</script>

<AppLayout {breadcrumbs}>
    <Form  action={route('services.store')} method="post">
        {#snippet children({ errors, processing }: BaseFormSnippetProps)}
        <div class="space-y-6 p-4 md:p-6">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">Create Service</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Add a new service to your inventory
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link href="/services">
                        <Button variant="outline" class="gap-2 cursor-pointer">
                            <ArrowLeft class="h-4 w-4" />
                            Cancel
                        </Button>
                    </Link>
                    <Button 
                        type="submit"
                        class="gap-2 bg-blue-600 hover:bg-blue-700 cursor-pointer"
                        disabled={processing}
                    >
                        <Save class="h-4 w-4" />
                        {processing ? 'Saving...' : 'Save Service'}
                    </Button>
                </div>
            </div>

            {#if Object.keys(errors).length > 0}
                <Alert variant="destructive" class="border-red-200 bg-red-50">
                    <AlertDescription class="text-red-800">
                        Please fix the errors below before submitting.
                    </AlertDescription>
                </Alert>
            {/if}

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Service Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <Card class="shadow-none border">
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">
                                Service Information
                            </CardTitle>
                            <CardDescription>
                                Enter basic details about your service
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name" class="required">Service Name*</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    placeholder="Enter service name"
                                    class={errors.name ? 'border-red-500' : ''}
                                />
                                {#if errors.name}
                                    <p class="text-sm text-red-600">{errors.name}</p>
                                {/if}
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Textarea
                                    id="description"
                                    name="description"
                                    bind:value={form.description}
                                    placeholder="Describe your service..."
                                    rows={3}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Pricing & Inventory -->
                    <Card class="shadow-none border">
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">
                                Pricing & Inventory
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="price" class="required flex items-center gap-2">
                                        Price*
                                    </Label>
                                    <Input
                                        id="price"
                                        name="price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        bind:value={form.price}
                                        class={errors.price ? 'border-red-500' : ''}
                                    />
                                    {#if errors.price}
                                        <p class="text-sm text-red-600">{errors.price}</p>
                                    {/if}
                                </div>

                                <div class="space-y-2">
                                    <Label class="flex items-center gap-2">
                                        <Percent class="h-4 w-4" />
                                        Tax Rate
                                    </Label>
                                    <Input
                                        name="tax"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0"
                                        bind:value={form.tax}
                                        class={errors.tax ? 'border-red-500' : ''}
                                    />
                                </div>
                            </div>


                            <!-- Price Calculation Preview -->
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Price (excl. tax):</span>
                                    <span class="font-medium">{user.currency_symbol}{parseFloat(form.price) || 0}</span>
                                </div>
                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-sm text-gray-600">Tax ({form.tax}%):</span>
                                    <span class="font-medium">{user.currency_symbol}{taxVal}</span>
                                </div>
                                <div class="flex justify-between items-center mt-2 pt-2 border-t">
                                    <span class="font-medium">Final Price:</span>
                                    <span class="text-lg font-bold text-blue-600">
                                        {user.currency_symbol}{finalPrice}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column - Image & Status -->
                <div class="space-y-6">
                    <!-- Service Image -->
                    <!-- <Card class="shadow-none border">
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">
                                Service Image
                            </CardTitle>
                            <CardDescription>
                                Upload a service image
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            {#if imagePreview}
                                <div class="relative">
                                    <img 
                                        src={imagePreview} 
                                        alt="Service preview" 
                                        class="w-full h-48 object-cover rounded-lg"
                                    />
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        class="absolute top-2 right-2 h-8 w-8 p-0"
                                        onclick={removeImage}
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            {:else}
                                <label
                                    for="image-upload"
                                    class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 cursor-pointer block"
                                >
                                    <Upload class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                                    <p class="text-sm text-gray-600">Click to upload image</p>
                                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF up to 5MB</p>
                                </label>
                            {/if}
                            
                            <input
                                id="image-upload"
                                bind:this={fileInput}
                                type="file"
                                accept="image/png,image/jpeg,image/jpg"
                                class="hidden"
                                onchange={handleImageUpload}
                            />
                            
                            <Button
                                type="button"
                                variant="outline"
                                class="w-full"
                                onclick={() => fileInput?.click()}
                            >
                                {imagePreview ? 'Change Image' : 'Browse Files'}
                            </Button>
                        </CardContent>
                    </Card> -->

                    <!-- Service Status -->
                    <Card class="shadow-none border">
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">
                                Service Status
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label>Status</Label>
                                    <SingleSelect
                                        items={statusOptions}
                                        label="Status"
                                        placeholder="Select Status"
                                        name="status"
                                        width="w-full"
                                        bind:value={form.status}
                                    />
                                    {#if errors.status}
                                        <p class="text-sm text-red-600">{errors.status}</p>
                                    {/if}
                                </div>

                                <div class="p-4 bg-blue-50 rounded-lg">
                                    <h4 class="font-medium text-blue-900 mb-2">Status Guide</h4>
                                    <ul class="text-sm text-blue-800 space-y-1">
                                        <li>• <span class="font-medium">Active:</span> Available for sale</li>
                                        <li>• <span class="font-medium">Inactive:</span> Not Available for sale</li>
                                    </ul>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <!-- Quick Actions -->
                    <div class="sticky top-6">
                        <Card class="shadow-none border">
                            <CardHeader>
                                <CardTitle class="text-lg font-semibold">
                                    Quick Actions
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <Button 
                                    type="submit"
                                    class="w-full gap-2 bg-blue-600 cursor-pointer hover:bg-blue-700"
                                    disabled={processing}
                                >
                                    <Save class="h-4 w-4" />
                                    {processing ? 'Saving Service...' : 'Save Service'}
                                </Button>
                                
                                <Link href="/services" class="block">
                                    <Button variant="outline" class="w-full gap-2 cursor-pointer">
                                        <ArrowLeft class="h-4 w-4" />
                                        Cancel & Return
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>

                </div>
            </div>
        </div>
        {/snippet}
    </Form>
</AppLayout>