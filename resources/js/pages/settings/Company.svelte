<script lang="ts">
    import HeadingSmall from '@/components/HeadingSmall.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import Textarea from '@/components/ui/textarea/textarea.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import SettingsLayout from '@/layouts/settings/Layout.svelte';
    import { type BreadcrumbItem, type User } from '@/types';
    import type { ProfileFormSnippetProps } from '@/types/forms';
    import { Form, Link, page, router } from '@inertiajs/svelte';
    import { fade } from 'svelte/transition';
    import { onMount } from 'svelte';

    interface Props {
        mustVerifyEmail: boolean;
        status?: string;
    }

    let { mustVerifyEmail, status }: Props = $props();

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Company',
            href: '/settings/company',
        },
    ];

    const user = $page.props.auth.user as User;
    
    let logoPreview: string | null = $state(null);
    let logoFile: File | null = $state(null);
    let fileInput: HTMLInputElement | null = $state(null);
    
    // Initialize logo preview with existing logo if available
    onMount(() => {
        if (user.company_logo) {
            logoPreview = `/uploads/logos/${user.company_logo}`;
        }
    });
    
    function handleLogoChange(event: Event) {
        const input = event.target as HTMLInputElement;
        if (input.files && input.files[0]) {
            logoFile = input.files[0];
            
            const reader = new FileReader();
            reader.onload = (e) => {
                logoPreview = e.target?.result as string;
            };
            reader.readAsDataURL(logoFile);
        }
    }
    
    function removeLogo() {
        logoPreview = null;
        logoFile = null;
        
        if (fileInput) {
            fileInput.value = '';
        }
        router.delete('/settings/company/remove-logo');

    }
    
    function triggerFileInput() {
        fileInput?.click();
    }
</script>

<svelte:head>
    <title>Company Settings</title>
</svelte:head>

<AppLayout breadcrumbs={breadcrumbItems}>
    <SettingsLayout>
        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Company Information" description="Update your company details and logo" />

            <Form 
                method="patch" 
                action={route('company.update')} 
                class="space-y-6"
                encType="multipart/form-data"
            >
                {#snippet children({ errors, processing, recentlySuccessful }: ProfileFormSnippetProps)}
                    
                    <!-- Company Logo Upload Section -->
                    <div class="grid gap-4">
                        <Label>Company Logo</Label>
                        <div class="flex items-start gap-6">
                            <!-- Logo Preview -->
                            <div class="flex-shrink-0">
                                {#if logoPreview}
                                    <div class="relative">
                                        <img 
                                            src={logoPreview} 
                                            alt="Company logo preview" 
                                            class="w-48 h-27 object-contain rounded-lg border border-neutral-200 bg-neutral-50 p-2"
                                        />
                                        <button
                                            type="button"
                                            onclick={removeLogo}
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                                            aria-label="Remove logo"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                {:else}
                                    <button title="Image"
                                        onclick={triggerFileInput}
                                        class="cursor-pointer w-48 h-27 rounded-lg border-2 border-dashed border-neutral-300 flex items-center justify-center bg-neutral-50 hover:bg-neutral-100 transition-colors"
                                    >       
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                {/if}
                            </div>
                            
                            <!-- Upload Controls -->
                            <div class="flex-grow">
                                <div class="flex items-center gap-3">
                                    <!-- File input with bind:this for Svelte way of referencing -->
                                    <Input
                                        type="file"
                                        id="company_logo"
                                        name="company_logo"
                                        accept="image/jpeg,image/png,image/jpg"
                                        class="hidden"
                                        onchange={handleLogoChange}
                                        bind:ref={fileInput}
                                    />
                                    
                                    <!-- Svelte way: use on:click directive with function -->
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onclick={triggerFileInput}
                                        disabled={processing}
                                    >
                                        Choose File
                                    </Button>
                                    
                                    {#if logoFile}
                                        <span class="text-sm text-neutral-600">
                                            {logoFile.name} ({(logoFile.size / 1024).toFixed(1)} KB)
                                        </span>
                                    {/if}
                                </div>
                                <p class="text-sm text-neutral-500 mt-2">
                                    Accepted formats: JPEG, PNG: 2MB.
                                </p>
                            </div>
                        </div>
                        <InputError class="mt-2" message={errors.company_logo} />
                    </div>

                    <div class="grid gap-2">
                        <Label for="company_name">Company Name</Label>
                        <Input 
                            name="company_name" 
                            id="company_name"
                            class="mt-1 block w-full" 
                            defaultValue={user.company_name ?? ''} 
                            placeholder="Company Name" 
                            disabled={processing}
                        />
                        <InputError class="mt-2" message={errors.company_name} />
                    </div>

                    <div class="grid gap-2">
                        <Label for="currency_symbol">Currency Symbol</Label>
                        <Input 
                            name="currency_symbol" 
                            class="mt-1 block w-full" 
                            id="currency_symbol"
                            defaultValue={user.currency_symbol ?? ''} 
                            placeholder="e.g., $, €, £" 
                            disabled={processing}
                        />
                        <InputError class="mt-2" message={errors.currency_symbol} />
                    </div>

                    <div class="grid gap-2">
                        <Label for="company_address">Company Details</Label>
                        <Textarea 
                            name="company_address" 
                            class="mt-1 block w-full h-32" 
                            id="company_address"
                            defaultValue={user.company_address ?? ''}
                            placeholder="Enter company address, contact information, etc."
                            disabled={processing}
                        />
                        <InputError class="mt-2" message={errors.company_address} />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            {#if processing}
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            {:else}
                                Save
                            {/if}
                        </Button>

                        {#if recentlySuccessful}
                            <p class="text-sm text-neutral-600" transition:fade={{ duration: 150 }}>Saved.</p>
                        {/if}
                    </div>
                {/snippet}
            </Form>
        </div>
    </SettingsLayout>
</AppLayout>