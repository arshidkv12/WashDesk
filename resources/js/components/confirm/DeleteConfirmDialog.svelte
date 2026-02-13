<script lang="ts">
  import { Button, buttonVariants } from "@/components/ui/button";
  import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogCancel,
    AlertDialogAction,
    AlertDialogTrigger
  } from "@/components/ui/alert-dialog";
  import { Trash2 } from "lucide-svelte";
    import { cn } from "@/lib/utils";
  
  export let onConfirm: () => Promise<void>;
  export let title = "Confirm Deletion";
  export let description: string;
  export let itemName: string;
  export let buttonText = "Delete";
  export let triggerClass = "";
  export let disabled = false;
  export let btnSize: "sm" | "default" | "lg" | "icon" | "icon-sm" | "icon-lg" = 'sm';
  export let buttonVariant: "destructive" | "default" | "outline" | "secondary" | "ghost" | "link" | undefined  = "outline";

  let isDeleting = false;
  let isOpen = false;
  
  async function handleConfirm() {
    isDeleting = true;
    try {
      await onConfirm();
    } finally {
      isDeleting = false;
      isOpen = false;
    }
  }
</script>

<AlertDialog bind:open={isOpen}>
  <AlertDialogTrigger 
    type="button"
    class={cn(buttonVariants({ variant: buttonVariant, size: btnSize }),
      `w-full justify-start text-red-600 border-red-200 hover:bg-red-50 hover:text-red-700 text-sm cursor-pointer`,
      triggerClass 
    )} 
    {disabled}
    >
      <Trash2 class="h-4 w-4" />
      {buttonText}
  </AlertDialogTrigger>
  <AlertDialogContent class="sm:max-w-md">
    <AlertDialogHeader>
      <AlertDialogTitle>{title}</AlertDialogTitle>
      <AlertDialogDescription>
        {@html description || `Are you sure you want to delete ${itemName ? `"${itemName}"` : 'this item'}?`}
      </AlertDialogDescription>
    </AlertDialogHeader>
    <AlertDialogFooter>
      <AlertDialogCancel disabled={isDeleting} class="cursor-pointer">Cancel</AlertDialogCancel>
      <AlertDialogAction
        onclick={handleConfirm}
        disabled={isDeleting}
        class="bg-red-600 hover:bg-red-700 text-white cursor-pointer"
      >
        {#if isDeleting}
          <div class="flex items-center justify-center gap-2">
            <div class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent">
            </div>
            Deleting...
          </div>
        {:else}
          Delete
        {/if}
      </AlertDialogAction>
    </AlertDialogFooter>
  </AlertDialogContent>
</AlertDialog>