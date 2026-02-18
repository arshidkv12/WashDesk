<script lang="ts">
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { page } from '@inertiajs/svelte';
    import { type User as UserType } from '@/types';

    const user = $derived($page.props.auth.user);
</script>

<div class="inline-flex items-center gap-3">
    <div class="flex aspect-square size-8 shrink-0 items-center justify-center rounded-md bg-sidebar-primary">
        <AppLogoIcon class="size-5 fill-current text-white! dark:text-black!" />
    </div>
    <div class="flex items-center">
        <span class="max-w-[150px] line-clamp-2 overflow-hidden text-ellipsis text-sm font-semibold">
            {user.company_name}
        </span>
    </div>
</div>
