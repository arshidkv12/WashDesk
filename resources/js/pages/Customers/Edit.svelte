<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type BreadcrumbItem } from '@/types';

    import HeadingSmall from '@/components/HeadingSmall.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Textarea } from '@/components/ui/textarea';
    import { Label } from '@/components/ui/label';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Separator } from '@/components/ui/separator';
    import type { BaseFormSnippetProps } from '@/types/forms';
    import { Form, Link } from '@inertiajs/svelte';
    import { ArrowLeft, User, Phone, Mail, MapPin } from 'lucide-svelte';

    interface Props {
        customer: {
            id: number;
            name: string;
            phone: string;
            email?: string;
            address?: string;
        }
    }

    let { customer }: Props = $props();
    let customerName = $derived(customer.name);

    const breadcrumbs: BreadcrumbItem[] = $derived([
        {
            title: 'Customers',
            href: '/customers',
        },
        {
            title: 'Customer',
            href: `/customers/${customer.id}`,
        },
        {
            title: 'Edit Customer',
            href: `/customers/${customer.id}/edit`,
        },
    ]);


</script>

<svelte:head>
    <title>Edit {customer.name} - Customers</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <!-- Pure white background -->
    <div class="min-h-screen bg-white">
        <!-- Main Content -->
        <div class="p-4 md:p-6 lg:p-8 max-w-2xl">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Edit Customer</h1>
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-gray-100 rounded">
                                <User class="h-4 w-4 text-gray-600" />
                            </div>
                            <p class="text-sm text-gray-500">
                                ID: <span class="font-medium">#{customer.id.toString().padStart(4, '0')}</span>
                            </p>
                        </div>
                    </div>
                    
                    <Button 
                        onclick={()=>history.back()}
                        variant="outline" 
                        size="sm"
                        class="gap-2 cursor-pointer"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Cancel
                    </Button>
                </div>
                <Separator />
            </div>

            <!-- Edit Form -->
            <Card class="border-0 border-gray-200 shadow-none max-w-lg">  
                <Form 
                    method="put" 
                    action={route('customers.update', customer.id)} 
                    class="space-y-6"
                >
                    {#snippet children({ errors, processing }: BaseFormSnippetProps)}
                    
                    <!-- Name Field -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="p-2 bg-gray-100 rounded-lg">
                                <User class="h-4 w-4 text-gray-600" />
                            </div>
                            <Label for="name" class="text-base">Full Name</Label>
                        </div>
                        <Input 
                            name="name" 
                            id="name" 
                            defaultValue={customer.name}
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            autocomplete="name" 
                            placeholder="Enter customer full name"
                        />
                        <InputError class="mt-1" message={errors.name} />
                    </div>

                    <!-- Phone Field -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="p-2 bg-blue-50 rounded-lg">
                                <Phone class="h-4 w-4 text-blue-600" />
                            </div>
                            <Label for="phone" class="text-base">Mobile Number</Label>
                        </div>
                        <Input
                            id="phone"
                            name="phone"
                            defaultValue={customer.phone || ''}
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            type="tel"
                            placeholder="Enter mobile number"
                        />
                        <InputError class="mt-1" message={errors.phone} />
                    </div>

                    <!-- Email Field -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="p-2 bg-purple-50 rounded-lg">
                                <Mail class="h-4 w-4 text-purple-600" />
                            </div>
                            <Label for="email" class="text-base">Email Address</Label>
                        </div>
                        <Input
                            id="email"
                            name="email"
                            defaultValue={customer.email || ''}
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            type="email"
                            placeholder="Enter email address"
                        />
                        <InputError class="mt-1" message={errors.email} />
                    </div>

                    <!-- Address Field -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="p-2 bg-green-50 rounded-lg">
                                <MapPin class="h-4 w-4 text-green-600" />
                            </div>
                            <Label for="address" class="text-base">Address</Label>
                        </div>
                        <Textarea
                            id="address"
                            name="address"
                            defaultValue={customer.address || ''}
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 min-h-[100px]"
                            placeholder="Enter customer address"
                        />
                        <InputError class="mt-1" message={errors.address} />
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-between pt-6 ">
                        <Button 
                            onclick={()=>history.back()}
                            variant="outline" 
                            type="button"
                            class="gap-2 cursor-pointer"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            Cancel
                        </Button>
                        
                        <div class="flex items-center gap-3">
                            <!-- <Button 
                                type="button"
                                variant="outline"
                                class="border-red-300 text-red-600 hover:bg-red-50 hover:text-red-700"
                                onclick={() => {
                                    if (confirm('Are you sure you want to delete this customer?')) {
                                        // $page.post(route('customers.destroy', customer.id), {
                                        //     _method: 'delete'
                                        // });
                                    }
                                }}
                            >
                                Delete Customer
                            </Button> -->
                            
                            <Button 
                                type="submit" 
                                disabled={processing}
                                class="gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6"
                            >
                                {processing ? 'Saving...' : 'Save Changes'}
                            </Button>
                        </div>
                    </div>
                    {/snippet}
                </Form>
            </Card>
        </div>
    </div>
</AppLayout>