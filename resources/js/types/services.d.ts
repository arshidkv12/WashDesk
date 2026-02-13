export interface Service {
    id: number;
    name: string;
    description?: string;
    sku?: string;
    price: number;
    tax: number;
    quantity: number;
    status: 'active' | 'inactive' | 'draft';
    image?: string;
    created_at: string;
    updated_at: string;
    created_at_formatted: string;
}

export interface Pagination {
    data: any[];
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