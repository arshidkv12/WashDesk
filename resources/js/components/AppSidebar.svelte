<script lang="ts">
    import NavFooter from '@/components/NavFooter.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
    import { type NavItem } from '@/types';
    import { Link } from '@inertiajs/svelte';
    import { BookOpen, Folder, LayoutGrid, Clipboard, UsersIcon, Package, ReceiptText } from 'lucide-svelte';
    import AppLogo from './AppLogo.svelte';

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Customers',
            href: '/customers',
            icon: UsersIcon,
        },
        // {
        //     title: 'Job Cards',
        //     href: '/job-cards',
        //     icon: Clipboard,
        // },
        {
            title: 'Products',
            href: '/products',
            icon: Package,
        },
        {
            title: 'Invoices',
            href: '/invoices',
            icon: ReceiptText,
        },
    ];

    const footerNavItems: NavItem[] = [
        {
            title: 'Repository',
            href: 'https://github.com/oseughu/svelte-starter-kit',
            icon: Folder,
        },
        {
            title: 'Documentation',
            href: 'https://laravel.com/docs/starter-kits',
            icon: BookOpen,
        },
    ];
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg">
                    <Link href={route('dashboard')}>
                        <AppLogo />
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={mainNavItems} />
    </SidebarContent>

    <SidebarFooter>
        <!-- <NavFooter items={footerNavItems} class="mt-auto" /> -->
        <NavUser />
    </SidebarFooter>
</Sidebar>
