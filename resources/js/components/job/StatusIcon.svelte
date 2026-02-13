<script lang="ts">
  import Clock from 'lucide-svelte/icons/clock';
  import Search from 'lucide-svelte/icons/search';
  import Wrench from 'lucide-svelte/icons/wrench';
  import CircleCheckBig from 'lucide-svelte/icons/circle-check-big';
  import CircleX from 'lucide-svelte/icons/circle-x';
  import CircleAlert from 'lucide-svelte/icons/circle-alert';
  import { type JobStatus } from '@/lib/helper/status'; 

  const statusIcons = new Map<JobStatus, any>([
    ['pending', Clock],
    ['in_progress', Wrench],
    ['waiting_parts', CircleAlert],
    ['completed', CircleCheckBig],
    ['delivered', CircleCheckBig],
    ['cancelled', CircleX],
    ['on_hold', CircleAlert],   
  ] as [JobStatus, any][]);

 
  let { 
    status, 
    className = 'h-3 w-3' 
  } = $props<{
    status: JobStatus;
    className?: string;
  }>();

  const IconComponent = $derived(statusIcons.get(status));
</script>

{#if IconComponent}
  <IconComponent class={className} />
{/if}