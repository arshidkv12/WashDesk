import '@inertiajs/svelte';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: any;
    isActive?: boolean;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    [key: string]: unknown;
    ziggy: Config & { location: string };
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    company_name?: string,
    currency_symbol?: string,
    company_logo?: string,
    company_address?: string,
    email_verified_at: string | null;
    two_factor_confirmed_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;


export type Flash = {
    message?: string;
    type?: 'success' | 'error' | 'info';
};

export interface Filters {
    search?: string;
    date_from?: string;
    date_to?: string;
    status?: string;
    sort_by?: string;
    sort_dir?: string;
    [key: string]: string | number | undefined;
}