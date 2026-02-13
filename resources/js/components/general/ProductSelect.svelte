<script lang="ts">
    import CheckIcon from "@lucide/svelte/icons/check";
    import Input from "../ui/input/input.svelte";
    import { type Product } from "@/types/products";
    import { cn } from "@/lib/utils";
    import { onMount } from "svelte";
    import { XIcon } from "lucide-svelte";

    let { onSelect } = $props<{ 
        onSelect: (product: Product) => void;
    }>();

    let placeholder = "Search product by name or barcode...";
    let showResults = $state(false);
    let loading = $state(false);
    let searchQuery = $state("");
    let products = $state<Product[]>([]);
    let containerRef: HTMLDivElement | null = $state(null);

    async function searchproducts(enterPress = false) {
        if (!searchQuery.trim()) {
            products = [];
            return;
        }

        loading = true;
        products = [];
        
        try {
            const res = await fetch(
                `/products/search?q=${encodeURIComponent(searchQuery)}`,
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
                products = Array.isArray(data) ? data : [];
                if(enterPress && products.length > 0){
                    handleSelectProduct(products[0]);
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
            searchproducts();
        }, 300);
    }

    function handleSelectProduct(product: Product) {
        onSelect(product);
        searchQuery = ""; 
        products = [];
        showResults = false;
    }

    function clearSearch() {
        searchQuery = "";
        products = [];
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

<div class="product-search-container relative" bind:this={containerRef}>
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
                    searchproducts(true);
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
                {:else if products.length === 0 && searchQuery.trim()}
                    <div class="p-4 text-sm text-gray-500 text-center">
                        No products found for "{searchQuery}"
                    </div>
                {:else if products.length > 0}
                    <div class="py-1">
                        {#each products as product (product.id)}
                            <button
                                type="button"
                                onclick={() => handleSelectProduct(product)}
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
                                    <div class="font-medium truncate">{product.name}</div>
                                    <div class="text-xs text-gray-500 flex flex-col sm:flex-row sm:gap-2">
                                        {#if product.sku}
                                            <span>SKU: {product.sku}</span>
                                        {/if}
                                        {#if product.price}
                                            <span>Price: {product.price}</span>
                                        {/if}
                                        {#if product.quantity}
                                            <span>Stock: {product.quantity}</span>
                                        {/if}
                                    </div>
                                </div>
                            </button>
                        {/each}
                    </div>
                {:else}
                    <div class="p-4 text-sm text-gray-500 text-center">
                        Type to search products
                    </div>
                {/if}
            </div>
        </div>
    {/if}
</div>