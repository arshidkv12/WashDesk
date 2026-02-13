<script lang="ts">
  import { Link } from '@inertiajs/svelte';

  import {
    User,
    Phone,
    Mail,
    ChevronRight,
  } from 'lucide-svelte';

  export let customers: {
    data: Array<{
      id: number;
      name: string;
      phone?: string | null;
      email?: string | null;
      created_at_formatted: string;
    }>;
  };
</script>

<div class="space-y-3 sm:hidden">
  {#each customers.data as customer}
    <Link
      href={`/customers/${customer.id}`}
      class="block border border-gray-200 rounded-xl bg-white p-4 hover:bg-gray-50 transition-colors"
    >
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-blue-50 rounded-lg">
            <User class="h-4 w-4 text-blue-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">
              {customer.name}
            </h3>
            <p class="text-xs text-gray-500">
              ID: #{customer.id.toString().padStart(4, '0')}
            </p>
          </div>
        </div>

        <ChevronRight class="h-5 w-5 text-gray-400" />
      </div>

      <!-- Contact Info -->
      <div class="grid grid-cols-1 gap-3 mb-4">
        <div class="space-y-1">
          <div class="flex items-center gap-1.5 text-xs text-gray-500">
            <Phone class="h-3.5 w-3.5" />
            Phone
          </div>
          <p class="text-sm text-gray-900 truncate">
            {customer.phone || '—'}
          </p>
        </div>

        <div class="space-y-1">
          <div class="flex items-center gap-1.5 text-xs text-gray-500">
            <Mail class="h-3.5 w-3.5" />
            Email
          </div>
          <p class="text-sm text-gray-900 truncate">
            {customer.email || '—'}
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-between pt-3 border-t border-gray-100">
        <span class="text-xs text-gray-500">
          Since {customer.created_at_formatted}
        </span>
      </div>
    </Link>
  {/each}
</div>
