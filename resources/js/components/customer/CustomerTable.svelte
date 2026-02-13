<script lang="ts">
  import { Link } from '@inertiajs/svelte';

  import {
    CalendarIcon,
    UserIcon,
    PhoneIcon,
    MailIcon,
    SquarePen,
    ChevronRight,
  } from 'lucide-svelte';
    import Button from '../ui/button/button.svelte';

  export let customers: {
    data: Array<{
      id: number;
      name: string;
      phone: string;
      email?: string | null;
      created_at_formatted: string;
    }>;
  };
</script>

<div class="overflow-x-auto border rounded-xl shadow-md">
  <table class="w-full text-sm">
    <thead class="bg-gray-50">
      <tr class="border-b">
        <th class="p-4 text-left font-medium text-gray-700">Date</th>
        <th class="p-4 text-left font-medium text-gray-700">Name</th>
        <th class="p-4 text-left font-medium text-gray-700">Phone</th>
        <th class="p-4 text-left font-medium text-gray-700">Email</th>
        <th class="p-4 text-right font-medium text-gray-700">Action</th>
      </tr>
    </thead>

    <tbody>
      {#if customers.data.length === 0}
        <tr>
          <td colspan="5" class="p-4 text-center text-gray-500">
            No customers found
          </td>
        </tr>
      {/if}

      {#each customers.data as customer}
        <tr class="border-b last:border-b-0 hover:bg-gray-50 group">
          <!-- Date -->
          <td class="p-4">
            <Link href={`/customers/${customer.id}`} class="block -m-4 p-4">
              <div class="flex items-center gap-2 text-gray-700">
                <div class="p-1.5 bg-gray-100 rounded">
                  <CalendarIcon class="h-3 w-3 text-gray-500" />
                </div>
                {customer.created_at_formatted}
              </div>
            </Link>
          </td>

          <!-- Name -->
          <td class="p-4">
            <Link href={`/customers/${customer.id}`} class="block -m-4 p-4">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 rounded">
                  <UserIcon class="h-3.5 w-3.5 text-blue-600" />
                </div>
                <div>
                  <p class="font-medium text-gray-900">{customer.name}</p>
                  <p class="text-xs text-gray-500">
                    ID: #{customer.id.toString().padStart(4, '0')}
                  </p>
                </div>
              </div>
            </Link>
          </td>

          <!-- Phone -->
          <td class="p-4">
            <Link href={`/customers/${customer.id}`} class="block -m-4 p-4">
              <div class="flex items-center gap-2">
                <PhoneIcon class="h-3.5 w-3.5 text-gray-400" />
                <span class="font-medium text-gray-700">
                  {customer.phone}
                </span>
              </div>
            </Link>
          </td>

          <!-- Email -->
          <td class="p-4">
            <Link href={`/customers/${customer.id}`} class="block -m-4 p-4">
              {#if customer.email}
                <div class="flex items-center gap-2">
                  <MailIcon class="h-3.5 w-3.5 text-gray-400" />
                  <span class="font-medium text-gray-700 truncate max-w-[180px]">
                    {customer.email}
                  </span>
                </div>
              {:else}
                <span class="text-gray-400 italic">—</span>
              {/if}
            </Link>
          </td>

          <!-- Actions -->
          <td class="p-4">
            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
              <Button
                href={`/customers/${customer.id}/edit`}
                variant="ghost"
                size="sm"
                class="h-8 w-8 p-0"
              >
                <SquarePen class="h-3.5 w-3.5 text-gray-600" />
              </Button>

              <Button
                href={`/customers/${customer.id}`}
                variant="ghost"
                size="sm"
                class="h-8 w-8 p-0"
              >
                <ChevronRight class="h-3.5 w-3.5 text-gray-600" />
              </Button>
            </div>
          </td>
        </tr>
      {/each}
    </tbody>
  </table>
</div>
