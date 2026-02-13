<script lang="ts">
    import CheckIcon from "@lucide/svelte/icons/check";
    import ChevronsUpDownIcon from "@lucide/svelte/icons/chevrons-up-down";
    import { onMount, tick } from "svelte";

    import * as Command from "@/components/ui/command";
    import * as Popover from "@/components/ui/popover";
    import { Button, buttonVariants } from "@/components/ui/button";
    import { cn } from "@/lib/utils";
    import Input from "../ui/input/input.svelte";

    let { modelValue = $bindable(), initCustomers = $bindable() } = $props();

    type Customer = {
        value: number;
        label: string;
    };

    let open = $state(false);
    let triggerRef: HTMLButtonElement | null = $state(null);
    let loading = $state(false);

    const value = $derived(Number(modelValue) ?? null);
    
    let customers = $derived(initCustomers);

    const selectedLabel = $derived(
        customers.find((c: Customer) => c.value === value)?.label || ""
    );


    function close() {
        open = false;
        tick().then(() => triggerRef?.focus());
    }

    function onSelectCustomer(customerValue: number) {
        modelValue = customerValue;
        close();
    }

    let timeout: ReturnType<typeof setTimeout>;

    async function searchCustomers(query: string) {
        if (!query.trim()) {
            customers = [];
            return;
        }

        loading = true;
        
        try {
            const res = await fetch(
                `/customers/search?q=${encodeURIComponent(query)}`,
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                }
            );

            if (res.ok) {
                customers = await res.json();
            } else {
                console.error('Search failed:', res.status);
                customers = [];
            }
        } catch (e) {
            console.error('Search error:', e);
            customers = [];
        } finally {
            loading = false;
        }
    }

    function onSearchInput(e: Event) {
        const q = (e.currentTarget as HTMLInputElement).value;
        
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            searchCustomers(q);
        }, 300);
    }
</script>

<Popover.Root bind:open>
  <Popover.Trigger class={cn(buttonVariants({ variant: "outline"}), "w-full justify-between")}>
      <span>{selectedLabel || "Select a customer..."}</span>
      <ChevronsUpDownIcon class="ml-2 h-4 w-4 shrink-0 opacity-50" />
    <Input type='hidden' name='customer_id'  bind:value={modelValue}/>
  </Popover.Trigger>

  <Popover.Content class="w-[320px] p-0" align="start">
    <Command.Root>
      <Command.Input
        placeholder="Search customer..."
        oninput={onSearchInput}
      />

      <Command.List>
        {#if loading}
          <Command.Loading class="p-4">Searching...</Command.Loading>
        {/if}

        <Command.Empty>
          {loading ? "Searching..." : ""}
          {!loading && customers.length < 1 ? "No customer found." : ""}
        </Command.Empty>

        <Command.Group>
          {#each customers as customer (customer.value)}
            <Command.Item
              value={customer.label}
              onSelect={() => onSelectCustomer(customer.value)}
            >
              <CheckIcon
                class={cn(
                  "mr-2 h-4 w-4",
                  value === customer.value ? "opacity-100" : "opacity-0"
                )}
              />
              {customer.label}
            </Command.Item>
          {/each}
        </Command.Group>
      </Command.List>
    </Command.Root>
  </Popover.Content>
</Popover.Root>