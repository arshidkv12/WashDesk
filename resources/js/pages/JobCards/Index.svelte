<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { type Flash, type BreadcrumbItem, type Filters } from '@/types';
    import { onMount } from 'svelte';
    import { Link, page } from '@inertiajs/svelte';
    import JobTable from '@/components/job/JobTable.svelte';
    import MobileJobTable from '@/components/job/MobileJobTable.svelte';
    import { toast } from 'svelte-sonner';
    import Pagination from '@/components/general/Pagination.svelte';
    import Card from '@/components/ui/card/card.svelte';
    import CardContent from '@/components/ui/card/card-content.svelte';
    import Filter from '@/components/general/Filter.svelte';
    import { type JobCardsPagination, type JobStatusOption } from '@/types/job-card';
    import Button from '@/components/ui/button/button.svelte';
    import { Plus } from 'lucide-svelte';

    let { jobCards, jobStatusOptions, filters, sort_by, sort_dir } = $props<{
        jobCards: JobCardsPagination;
        jobStatusOptions: JobStatusOption[];
        filters?: Filters,
        sort_by:string;
        sort_dir: string;
    }>();

  let isMobile = $state(false);
  // svelte-ignore state_referenced_locally
  let localFilters = $state<Filters>({ ...filters });

  $effect(() => { 
    localFilters = { ...filters };  
    const flash = $page.flash as Flash;
    if (flash?.message) {  
        if (flash.type === 'success') {
            toast.success(flash.message);
        } else if (flash.type === 'error') {
            toast.error(flash.message);
        }
    }
  });


  onMount(() => {
    isMobile = window.innerWidth < 768;  
    
    const handleResize = () => {
      isMobile = window.innerWidth < 768;
    };
    
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  });


  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Dashboard',
      href: '/dashboard',
    },
    {
      title: 'Job Cards',
      href: '/job-cards',
    },
  ];

</script>

<svelte:head>
  <title>Job Cards</title>
</svelte:head>

<AppLayout {breadcrumbs}>

  <!-- Header -->
  <div class="p-4 md:p-6 md:pb-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
          <h1 class="text-3xl font-bold tracking-tight">Job Cards</h1>
          <p class="text-muted-foreground mt-1">
              Manage your job cards
          </p>
      </div>
      <Link href="/job-cards/create">
          <Button class="gap-2 cursor-pointer bg-blue-600 hover:bg-blue-700">
              <Plus class="h-4 w-4" />
              Add Job Card
          </Button>
      </Link>
  </div>
  
  <!-- Filters Card -->
  <Card class="shadow-none border-none pb-0">
      <CardContent>
          <Filter 
            routePath='job-cards.index' 
            filters={localFilters}
            statusOptions={jobStatusOptions} 
          />
      </CardContent>
  </Card>

  <div class="p-4 md:p-6 space-y-6">
    <!-- Results Count -->
    {#if jobCards.total > 0}
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-gray-500">
                Showing <span class="font-medium">{jobCards.from}</span> to <span class="font-medium">{jobCards.to}</span> of{' '}
                <span class="font-medium">{jobCards.total}</span> job cards
            </p>
        </div>
    {/if}

    <!-- Conditional rendering based on screen size -->
    {#if !isMobile}
      <!-- Desktop Table -->
      <JobTable 
        routePath={'job-cards.index'}
        {sort_by} 
        {sort_dir} 
        jobCards={jobCards} 
        bind:filters={localFilters}
      />
    {:else}
      <!-- Mobile Cards -->
      <MobileJobTable jobCards={jobCards}/>
    {/if}

    <!-- Pagination -->
    <Pagination 
      links={jobCards.links}  
      currentPage={jobCards.current_page} 
      lastPage={jobCards.last_page}
      bind:filters={localFilters}
      {sort_by} 
      {sort_dir} 
    />
    
  </div>
</AppLayout>