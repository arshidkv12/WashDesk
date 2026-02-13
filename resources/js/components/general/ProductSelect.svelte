<script lang="ts">
    import CheckIcon from "@lucide/svelte/icons/check";
    import Input from "../ui/input/input.svelte";
    import { type Service } from "@/types/services";
    import { cn } from "@/lib/utils";
    import { onMount } from "svelte";
    import { XIcon } from "lucide-svelte";

    let { onSelect } = $props<{ 
        onSelect: (service: Service) => void;
    }>();

    let placeholder = "Search service by name or barcode...";
    let showResults = $state(false);
    let loading = $state(false);
    let searchQuery = $state("");
    let services = $state<Service[]>([]);
    let containerRef: HTMLDivElement | null = $state(null);

    async function searchservices(enterPress = false) {
        if (!searchQuery.trim()) {
            services = [];
            return;
        }

        loading = true;
        services = [];
        
        try {
            const res = await fetch(
                `/services/search?q=${encodeURIComponent(searchQuery)}`,
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                }
            );

            if (res.ok) {
                const data = await res.json();
                services = Array.isArray(data) ? data : [];
                if(enterPress && services.length > 0){
                    handleSelectService(services[0]);
                }
            }
        } catch (e) {
            console.error('Search error:', e);
        } finally {
            loading = false;
        }
    }

    let timeout: ReturnType<typeof setTimeout>;
    
    function onSearchInput(e: Event) {
        showResults = true;
        const q = (e.currentTarget as HTMLInputElement).value;
        searchQuery = q;
        
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            searchservices();
        }, 300);
    }

    function handleSelectService(service: Service) {
        onSelect(service);
        searchQuery = ""; 
        services = [];
        showResults = false;
    }

    function clearSearch() {
        searchQuery = "";
        services = [];
        showResults = false;
    }

    // Close results when clicking outside
    function handleClickOutside(e: MouseEvent) {
        if (containerRef && !containerRef.contains(e.target as Node)) {
            showResults = false;
        }
    }

    // Handle keyboard events
    function handleKeyDown(e: KeyboardEvent) {
        if (e.key === 'Escape') {
            showResults = false;
        }
    }

    onMount(() => {
        document.addEventListener('mousedown', handleClickOutside);
        document.addEventListener('keydown', handleKeyDown);
        
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
            document.removeEventListener('keydown', handleKeyDown);
            clearTimeout(timeout);
        };
    });
</script>

<div class="service-search-container relative" bind:this={containerRef}>
    <!-- Search Input -->
    <div class="relative">
        <Input
            type="text"
            placeholder={placeholder}
            bind:value={searchQuery}
            oninput={onSearchInput}
            onkeydown={(e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchservices(true);
                }}
            }
            class="pr-10"
            autocomplete="off"
        />
        
        <!-- Clear button -->
        {#if searchQuery}
            <button
                type="button"
                onclick={clearSearch}
                class={cn(
                    "absolute right-2 top-1/2 transform -translate-y-1/2",
                    "text-gray-400 hover:text-gray-600 transition-colors",
                    "h-5 w-5 flex items-center justify-center"
                )}
                title="Clear search"
            >
                <XIcon class="h-4 w-4" />
            </button>
        {/if}
    </div>

    <!-- Results Dropdown -->
    {#if showResults}
        <div class="absolute top-full left-0 right-0 mt-1 z-50">
            <div class="border border-gray-200 rounded-md shadow-lg bg-white max-h-60 overflow-y-auto">
                {#if loading}
                    <div class="p-4 text-sm text-gray-500 text-center">
                        Searching...
                    </div>
                {:else if services.length === 0 && searchQuery.trim()}
                    <div class="p-4 text-sm text-gray-500 text-center">
                        No services found for "{searchQuery}"
                    </div>
                {:else if services.length > 0}
                    <div class="py-1">
                        {#each services as service (service.id)}
                            <button
                                type="button"
                                onclick={() => handleSelectService(service)}
                                class={cn(
                                    "w-full text-left px-3 py-2 hover:bg-gray-100",
                                    "flex items-center gap-2 transition-colors"
                                )}
                            >
                                <CheckIcon
                                    class={cn(
                                        "h-4 w-4 shrink-0 opacity-0"
                                    )}
                                />
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium truncate">{service.name}</div>
                                    <div class="text-xs text-gray-500 flex flex-col sm:flex-row sm:gap-2">
                                        {#if service.sku}
                                            <span>SKU: {service.sku}</span>
                                        {/if}
                                        {#if service.price}
                                            <span>Price: {service.price}</span>
                                        {/if}
                                        {#if service.quantity}
                                            <span>Stock: {service.quantity}</span>
                                        {/if}
                                    </div>
                                </div>
                            </button>
                        {/each}
                    </div>
                {:else}
                    <div class="p-4 text-sm text-gray-500 text-center">
                        Type to search services
                    </div>
                {/if}
            </div>
        </div>
    {/if}
</div>