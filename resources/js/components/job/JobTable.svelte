<script lang="ts">
  import { Link, router } from '@inertiajs/svelte';
  import User from 'lucide-svelte/icons/user';
  import Phone from 'lucide-svelte/icons/phone';
  import SquarePen from 'lucide-svelte/icons/square-pen';
  import { getJobStatusClasses, type JobStatus } from '@/lib/helper/status';
  import StatusIcon from './StatusIcon.svelte';
  import { Button } from '../ui/button';
  import { ChevronRight } from 'lucide-svelte';
  import { type JobCardsPagination } from '@/types/job-card';
  import { type Filters } from '@/types';
  import SortIcon from '../general/SortIcon.svelte';
  import { changeSort, getSortIcon } from '@/lib/helper/sortUtils';
  import { onMount } from 'svelte';

  let { jobCards, filters = $bindable(), sort_by, sort_dir, routePath, routePathArgs } = $props<{ 
    jobCards: JobCardsPagination; 
    filters: Filters,
    sort_by: string;
    sort_dir: string;
    routePath: string;
    routePathArgs?: string;
  }>();

  let routePathArgsVal = $state('');

  onMount(()=>{
    routePathArgsVal = routePathArgs;
  });

</script>

<div class="overflow-x-auto border rounded-xl shadow-md">
  <table class="w-full text-sm">
    <thead class="bg-gray-50">
      <tr class="border-b">
        <th class="p-4 text-left font-medium text-gray-700">
          <Button 
            variant="ghost" 
            class="cursor-pointer"
            onclick={()=>changeSort(filters, 'job_no', sort_dir, routePath, routePathArgsVal)}
          >
              Job No
            <SortIcon direction={getSortIcon('job_no', sort_by, sort_dir)} />
          </Button>
        </th>
        <th class="p-4 text-left font-medium text-gray-700">
          <Button 
            variant="ghost" 
            class="cursor-pointer"
            onclick={()=>changeSort(filters, 'created_at', sort_dir, routePath, routePathArgsVal)}
          >
            Date
            <SortIcon direction={getSortIcon('created_at', sort_by, sort_dir)} />
          </Button>
        </th>
        <th class="p-4 text-left font-medium text-gray-700">Item</th>
        <th class="p-4 text-left font-medium text-gray-700">Customer</th>
        <th class="p-4 text-left font-medium text-gray-700">Phone</th>
        <th class="p-4 text-left font-medium text-gray-700">Status</th>
        <th class="p-4 text-left font-medium text-gray-700">
          <Button 
            variant="ghost" 
            class="cursor-pointer"
            onclick={()=>changeSort( filters, 'delivery_date', sort_dir, routePath, routePathArgsVal)}
          >
            Delivery
            <SortIcon direction={getSortIcon('delivery_date', sort_by, sort_dir)} />
          </Button>
        </th>
        <th class="p-4 text-right font-medium text-gray-700">Action</th>
      </tr>
    </thead>

    <tbody>
      {#if jobCards.data.length === 0}
        <tr>
          <td colspan="7" class="p-4 text-center text-gray-500">
            No job cards found
          </td>
        </tr>
      {/if}

      {#each jobCards.data as job}
        <tr class="group hover:bg-gray-50 border-b last:border-b-0 transition-colors">
          <!-- Job No -->
          <td class="p-4">
            <Link href={`/job-cards/${job.id}`} class="block -m-4 p-4">
              <div class="font-medium text-gray-900 text-sm min-w-[100px]">
                {job.job_no}
              </div>
            </Link>
          </td>

          <!-- Date -->
          <td class="p-4 text-sm text-gray-700 max-w-[100px]">
              <Link href={`/job-cards/${job.id}`} class="block -m-4 p-4">
              {job.created_at_formatted}
            </Link>
          </td>

          <!-- Item -->
          <td class="p-4 text-sm text-gray-700">
            <Link href={`/job-cards/${job.id}`} class="block -m-4 p-4">
              <div class="max-w-[150px] line-clamp-2">
                {job.item} 
              </div>
            </Link>
          </td>

          <!-- Customer -->
          <td class="p-4">
            <Link href={`/job-cards/${job.id}`} class="block -m-4 p-4">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 rounded-lg">
                  <User class="h-3.5 w-3.5 text-blue-600" />
                </div>
                <div class="min-w-0">
                  <div class="font-medium text-gray-900 max-w-[150px] truncate text-sm">
                    {job.customer.name}
                  </div>
                  <div class="text-xs text-gray-500">
                    ID: #{job.customer.id.toString().padStart(4, '0')}
                  </div>
                </div>
              </div>
            </Link>
          </td>

          <!-- Phone -->
          <td class="p-4">
            <Link href={`/job-cards/${job.id}`} class="block -m-4 p-4">
              <div class="flex items-center gap-2">
                <Phone class="h-4 w-4 text-gray-400" />
                <span class="text-sm text-gray-700">
                  {job.customer.phone || '—'}
                </span>
              </div>
            </Link>
          </td>

          <!-- Status -->
          <td class="p-4">
            <Link href={`/job-cards/${job.id}`} class="block -m-4 p-4">
              <span
                class={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold ${getJobStatusClasses(job.status)}`}
              >
                <StatusIcon
                  status={job.status as JobStatus}
                  className="h-4 w-4"
                />
                {job.status.replaceAll('_', ' ')}
              </span>
            </Link>
          </td>

          <!-- Delivery -->
          <td class="p-4 text-sm text-gray-700">
            <Link href={`/job-cards/${job.id}`} class="block -m-4 p-4">
              {job.delivery_date_formatted || '—'}
            </Link>
          </td>

          <!-- Actions -->
          <td class="p-4">
            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
              <Link
                href={`/job-cards/${job.id}/edit`}>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-8 w-8 p-0 cursor-pointer"
                >
                  <SquarePen class="h-3.5 w-3.5 text-gray-600" />
                </Button>
              </Link>

               <Link
                href={`/job-cards/${job.id}`}>
                <Button
                  variant="ghost"
                  size="sm"
                  class="h-8 w-8 p-0 cursor-pointer"
                >
                  <ChevronRight class="h-3.5 w-3.5 text-gray-600" />
                </Button>
              </Link>
            </div>
          </td>

        </tr>
      {/each}
    </tbody>
  </table>
</div>
