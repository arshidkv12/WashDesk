export interface JobCard{
    id: number;
    job_no: string;
    item: string;
    phone: string;
    status: string;
    delivery_date: string;
    created_at_formatted: string;
    delivery_date_formatted: string;
    customer: {
        id: number;
        name: string;
        phone: string;
    };
}

export interface  JobCardsPagination{
    data: JobCard[];
    current_page: number;
    last_page: number;
    links: any[];
    total: number;
    from: number;
    to: number;
}

export interface JobStatusOption{
    value: string;
    label: string;
    icon: string;
    color: string;
}
