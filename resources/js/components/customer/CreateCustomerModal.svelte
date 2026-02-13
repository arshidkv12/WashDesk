<script lang="ts">
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Textarea } from '@/components/ui/textarea';
    import { Label } from '@/components/ui/label';
    import type { BaseFormSnippetProps } from '@/types/forms';
    import { Form } from '@inertiajs/svelte';
    import { ArrowLeft, User, Phone, Mail, MapPin, X } from 'lucide-svelte';
    import {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogHeader,
        DialogTitle,
        DialogFooter,
        DialogClose
    } from '@/components/ui/dialog';

    let {
        open = $bindable(false),
        pageUrl = '',
        onSuccess = () => {},
        onError = () => {}
    }: {
        open: boolean,
        pageUrl: string,
        onSuccess?: () => void
        onError?: (error: any) => void
    } = $props()

</script>

<Dialog bind:open>
    <DialogContent class="sm:max-w-lg md:max-w-xl p-0 bg-white max-h-[99vh] overflow-y-auto">
        <DialogHeader class="px-6 py-4 border-b">
            <div class="flex items-center justify-between">
                <div>
                    <DialogTitle class="text-lg md:text-xl font-semibold text-gray-900">
                        Add New Customer
                    </DialogTitle>
                    <DialogDescription class="text-gray-500 mt-1">
                        Enter customer details below
                    </DialogDescription>
                </div>
            </div>
        </DialogHeader>

        <!-- Edit Form -->
        <div class="px-6 py-4">
            <Form 
                method="post" 
                action={route('customers.store')} 
                preserveScroll
                class="space-y-6"
                onSuccess={()=>open=false}
                transform={data => ({ ...data, pageUrl: pageUrl })}
            >
                {#snippet children({ errors, processing }: BaseFormSnippetProps)}
                
                <!-- Name Field -->
                <div class="space-y-2">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <User class="h-4 w-4 text-gray-600" />
                        </div>
                        <Label for="name" class="text-sm font-medium text-gray-700">Full Name</Label>
                    </div>
                    <Input 
                        name="name" 
                        id="name" 
                        class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
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
                        <Label for="phone" class="text-sm font-medium text-gray-700">Mobile Number</Label>
                    </div>
                    <Input
                        id="phone"
                        name="phone"
                        class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
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
                        <Label for="email" class="text-sm font-medium text-gray-700">Email Address</Label>
                    </div>
                    <Input
                        id="email"
                        name="email"
                        class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
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
                        <Label for="address" class="text-sm font-medium text-gray-700">Address</Label>
                    </div>
                    <Textarea
                        id="address"
                        name="address"
                        class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm min-h-[80px]"
                        placeholder="Enter customer address"
                        rows={3}
                    />
                    <InputError class="mt-1" message={errors.address} />
                </div>

                <!-- Form Actions -->
                <DialogFooter class="px-0 py-0 border-t-0">
                    <div class="flex items-center justify-between w-full pt-4">
                        <Button 
                            variant="outline" 
                            type="button"
                            class="gap-2 text-sm cursor-pointer"
                            onclick={() => open = false}
                        >
                            Cancel
                        </Button>
                        
                        <div class="flex items-center gap-3">
                            <Button 
                                type="submit" 
                                disabled={processing}
                                class="gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 text-sm cursor-pointer"
                            >
                                {processing ? 'Saving...' : 'Add Customer'}
                            </Button>
                        </div>
                    </div>
                </DialogFooter>
                {/snippet}
            </Form>
        </div>
    </DialogContent>
</Dialog>