<script lang="ts">
  import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-svelte';

  const directionIcons = new Map<string, any>([
    ['none', ArrowUpDown],
    ['asc', ArrowUp],
    ['desc', ArrowDown],  
  ] as [string, any][]);

 
  let { 
    direction, 
    className = 'h-3 w-3' 
  } = $props<{
    direction: string;
    className?: string;
  }>();

  const IconComponent = $derived(directionIcons.get(direction));
</script>

{#if IconComponent}
  <IconComponent class={className} />
{/if}