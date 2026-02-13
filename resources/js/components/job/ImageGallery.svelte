<script lang="ts">
    import { Image } from 'lucide-svelte';
    import { Dialog, DialogContent } from '@/components/ui/dialog';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { ChevronLeft, ChevronRight, X } from 'lucide-svelte';

    let { 
        images = [],
        title = "Images",
        description = "View images related to this job card"
    } = $props<{
        images?: Array<{
            url: string;
            name?: string;
            file_name?: string;
        }>;
        title?: string;
        description?: string;
    }>();

    let showLightbox = $state(false);
    let currentIndex = $state(0);

    const currentImage = $derived(images[currentIndex]);

    function openLightbox(index: number) {
        currentIndex = index;
        showLightbox = true;
    }

    function nextImage() {
        if (currentIndex < images.length - 1) {
            currentIndex++;
        }
    }

    function previousImage() {
        if (currentIndex > 0) {
            currentIndex--;
        }
    }

    // Handle keyboard navigation
    function handleKeydown(event: KeyboardEvent) {
        if (!showLightbox) return;
        
        if (event.key === 'ArrowRight') nextImage();
        if (event.key === 'ArrowLeft') previousImage();
        if (event.key === 'Escape') showLightbox = false;
    }

    $effect(() => {
        if (showLightbox) {
            window.addEventListener('keydown', handleKeydown);
            return () => window.removeEventListener('keydown', handleKeydown);
        }
    });
</script>

<!-- Image Gallery Card -->
<Card class="border border-gray-200 shadow-sm">
    <CardHeader class="pb-3">
        <CardTitle class="flex items-center gap-2">
            <Image class="h-5 w-5 text-blue-600" />
            {title}
        </CardTitle>
        <CardDescription>
            {description}
        </CardDescription>
    </CardHeader>
    <CardContent>
        {#if images && images.length > 0}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 gap-5">
                {#each images as image, index}
                    <button
                        type="button"
                        onclick={() => openLightbox(index)}
                        onkeydown={(e) => {
                            if (e.key === 'Enter' || e.key === ' ') {
                                e.preventDefault();
                                openLightbox(index);
                            }
                        }}
                        class=" cursor-pointer relative w-full aspect-square overflow-hidden rounded-lg border border-gray-200 hover:border-blue-400 transition-all duration-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 group bg-transparent p-0 text-left"
                        aria-label={`Open image ${image.name || index + 1} in lightbox`}
                    >
                        <div class="relative w-full h-full">
                            <img
                                src={'/uploads/images/' + image.file_name}
                                alt={image.name || `Job image ${index + 1}`}
                                class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-110 group-focus:scale-110 bg-gray-50"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity duration-200"></div>
                            
                            <!-- Image overlay info -->
                            <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity duration-200">
                                {#if image.name}
                                    <p class="text-white text-sm md:text-base font-medium truncate">{image.name}</p>
                                {/if}
                            </div>
                        </div>
                    </button>
                {/each}
            </div>
        {:else}
            <div class="text-center py-16 border-2 border-dashed border-gray-300 rounded-lg">
                <Image class="h-20 w-20 text-gray-300 mx-auto mb-4" />
                <p class="text-base text-gray-500">No images available</p>
                <p class="text-sm text-gray-400 mt-1">Upload images in the edit view</p>
            </div>
        {/if}
    </CardContent>
</Card>

<!-- Image Lightbox Dialog -->
<Dialog bind:open={showLightbox}>
    <DialogContent class="max-w-5xl p-0 overflow-hidden bg-black/95 border-0">
        <div class="relative">
            <!-- Close button -->
            <Button
                size="icon"
                variant="ghost"
                class="absolute top-4 right-4 z-50 bg-black/50 hover:bg-black/70 text-white hover:text-white cursor-pointer"
                onclick={() => showLightbox = false}
            >
                <X class="h-5 w-5" />
            </Button>
            
            <!-- Main image -->
            {#if currentImage}
                <div class="flex items-center justify-center min-h-[70vh] p-4">
                    <img
                        src={'/uploads/images/' + currentImage.file_name}
                        alt={currentImage.name || 'Full view'}
                        class="max-w-full max-h-[70vh] object-contain rounded-lg"
                    />
                </div>
            {/if}
            
            <!-- Navigation arrows -->
            <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-between px-4">
                <Button
                    size="icon"
                    variant="ghost"
                    class="bg-black/50 hover:bg-black/70 text-white hover:text-white cursor-pointer"
                    onclick={previousImage}
                    disabled={currentIndex === 0}
                >
                    <ChevronLeft class="h-6 w-6" />
                </Button>
                <Button
                    size="icon"
                    variant="ghost"
                    class="bg-black/50 hover:bg-black/70 text-white hover:text-white cursor-pointer"
                    onclick={nextImage}
                    disabled={currentIndex === images.length - 1}
                >
                    <ChevronRight class="h-6 w-6" />
                </Button>
            </div>
        </div>
        
        <!-- Image info -->
        {#if images.length > 0}
            <div class="bg-white p-4">
                <div class="text-center">
                    <div class="flex items-center justify-center gap-2 text-sm text-gray-600">
                        <span class="font-medium text-gray-900">{currentIndex + 1} of {images.length}</span>
                        {#if currentImage?.name}
                            <span class="text-gray-400">•</span>
                            <p class="text-gray-900 font-medium">{currentImage.name}</p>
                        {/if}
                    </div>
                    {#if currentImage?.created_at}
                        <p class="text-xs text-gray-500 mt-1">
                            Uploaded on {new Date(currentImage.created_at).toLocaleDateString()}
                        </p>
                    {/if}
                </div>
            </div>
        {/if}
    </DialogContent>
</Dialog>

