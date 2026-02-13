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
    import { Form, Link, page } from '@inertiajs/svelte';
    import { fade } from 'svelte/transition';

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
</script>

<svelte:head>
    <title>Company Settings</title>
</svelte:head>

<AppLayout breadcrumbs={breadcrumbItems}>
    <SettingsLayout>
        <div class="flex flex-col space-y-6">
            <HeadingSmall title="Company Information" description="Update your name and email address" />

            <Form method="patch" action={route('company.update')} class="space-y-6">
                {#snippet children({ errors, processing, recentlySuccessful }: ProfileFormSnippetProps)}
                    <div class="grid gap-2">
                        <Label for="company_name">Company Name</Label>
                        <Input name="company_name" 
                            id="company_name"
                            class="mt-1 block w-full" 
                            defaultValue={user.company_name ?? ''} 
                            placeholder="Company Name" 
                        />
                        <InputError class="mt-2" message={errors.company_name} />
                    </div>

                    <div class="grid gap-2">
                        <Label for="currency_symbol">Currency Symbol</Label>
                        <Input name="currency_symbol" class="mt-1 block w-full" 
                            id="currency_symbol"
                            defaultValue={user.currency_symbol ?? ''} 
                            placeholder="Company Name" 
                        />
                        <InputError class="mt-2" message={errors.currency_symbol} />
                    </div>


                    <div class="grid gap-2">
                        <Label for="company_address">Company Details</Label>
                        <Textarea name="company_address" 
                            class="mt-1 block w-full h-32" 
                            id="company_address"
                            defaultValue={user.company_address ?? ''}
                        />
                        <InputError class="mt-2" message={errors.company_address} />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>Save</Button>

                        {#if recentlySuccessful}
                            <p class="text-sm text-neutral-600" transition:fade={{ duration: 150 }}>Saved.</p>
                        {/if}
                    </div>
                {/snippet}
            </Form>
        </div>

    </SettingsLayout>
</AppLayout>
