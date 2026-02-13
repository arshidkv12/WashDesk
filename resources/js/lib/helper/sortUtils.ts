import { type Filters } from '@/types';
import { router } from '@inertiajs/svelte';

export const getSortIcon = (field:string, sort_by:string, sort_dir: string): string => {
    if (sort_by !== field) return 'none';
    return sort_dir;
};

export function changeSort(
    filters: Filters, 
    sort_by:string, 
    sort_dir: string, 
    routePath: string,
    routePathArgsVal: string = ''
) { 
    
    Object.entries(filters).forEach(([key, value]) => {
        if (value !== '' && value !== null && value !== undefined) {
            filters[key] = value;
        }
    });

    filters['sort_by'] = sort_by;
    filters['sort_dir'] = sort_dir === 'asc' ? 'desc': 'asc';
    router.get(route(routePath, routePathArgsVal), filters, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
}