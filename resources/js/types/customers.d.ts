export interface Customer {
    id: number;
    name: string;
    email: string;
    phone?: string;
    address?: string;
    created_at: string;
    created_at_formatted: string;
}

export interface CustomerPagination {
    data: Customer[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
}