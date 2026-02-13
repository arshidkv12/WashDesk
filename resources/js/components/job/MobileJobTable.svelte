<script lang="ts">
    import { Link } from '@inertiajs/svelte';

    export let jobCards: {
    data: Array<{
        id: number;
        job_no: string;
        status: string;
        created_at_formatted: string;
        delivery_date_formatted?: string | null;
        item:string;
        customer: {
          name: string;
          phone?: string | null;
        };
    }>;
    };

    import { getJobStatusClasses } from '@/lib/helper/status';

</script>

<div class="space-y-4">
  {#if jobCards.data.length === 0}
    <div class="p-4 text-center text-gray-500 border rounded-lg">
      No job cards found
    </div>
  {/if}

  {#each jobCards.data as job}
    <div class="border rounded-xl shadow-sm hover:shadow-md transition-shadow bg-white">
      <div class="p-4 space-y-3">
        <!-- Header -->
        <div class="flex justify-between items-start">
          <div>
            <div class="font-semibold text-gray-900 text-lg">
              {job.job_no}
            </div>
            <div class="text-sm text-gray-500 mt-1">
              {job.created_at_formatted}
            </div>
          </div>

          <span
            class={`px-3 py-1 rounded-full text-xs font-semibold ${getJobStatusClasses(job.status)}`}
          >
            {job.status.replaceAll('_', ' ')}
          </span>
        </div>

        <div class="space-y-2 font-medium text-gray-900">
          {job.item}
        </div>

        <!-- Customer -->
        <div class="space-y-2">
          <div class="flex items-center gap-2">
            <svg
              class="w-4 h-4 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
              />
            </svg>
            <span class="text-gray-900">
              {job.customer.name}
            </span>
          </div>

          <div class="flex items-center gap-2">
            <svg
              class="w-4 h-4 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
              />
            </svg>
            <span class="text-gray-700">
              {job.customer.phone || '—'}
            </span>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-between items-center pt-2 border-t">
          <div class="text-sm">
            <div class="text-gray-500">Delivery Date</div>
            <div class="font-medium text-gray-900">
              {job.delivery_date_formatted || '—'}
            </div>
          </div>

          <Link
            href={`/job-cards/${job.id}`}
            class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition text-sm"
          >
            View Details
          </Link>
        </div>
      </div>
    </div>
  {/each}
</div>
