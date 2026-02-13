<script lang="ts">
    import { type Filters } from '@/types';
    import { router } from '@inertiajs/svelte';
    import { onMount } from 'svelte';

    interface Link {
        url: string | null;
        label: string;
        active: boolean;
    }

    interface Props {
        currentPage: number;
        lastPage: number;
        links: Link[];
        filters: Filters,
        sort_by:string;
        sort_dir:string;
    }

    let { currentPage, lastPage, links, filters = $bindable(), sort_by, sort_dir } : Props = $props();
    $effect(()=>{
        filters['sort_by'] = sort_by;
        filters['sort_dir'] = sort_dir;
    });
</script>
{#if lastPage > 1}
<div class="flex flex-col sm:flex-row items-center justify-between pt-4 gap-4">
    <!-- Info -->
    <div class="text-sm text-gray-600">
    Page {currentPage} of {lastPage}
    </div>

    <!-- Links -->
    <div class="flex flex-wrap gap-1 justify-center">
    {#each links as link}
        <button
        disabled={!link.url}
        onclick={() =>
            link.url &&
            router.get(
                link.url,
                filters,
                {
                    preserveState: true,
                    preserveScroll: true,  
                    replace: true
                }
            )
        }
        class="
            cursor-pointer
            px-3 py-1 text-sm border rounded
            {link.active
            ? 'bg-black text-white border-black'
            : 'text-gray-700 hover:bg-gray-100'}
            disabled:opacity-50
            disabled:cursor-not-allowed
        "
        >
        {@html link.label}
        </button>
    {/each}
    </div>
</div>
{/if}
