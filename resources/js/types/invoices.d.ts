export interface Invoice {
    id: number;                       
    user_id: number;                 
    invoice_no: number;                
    customer_id: number;               

    subtotal: number;                
    tax_amount: number;                
    discount_amount: number;          
    total_amount: number;             

    status: 'draft' | string;         

    notes?: string | null;             
    created_at: string;            
    updated_at: string;

    created_at_formatted?: string;
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

export interface InvoiceStatusOption {
    value: string;
    label: string;
};

export interface InvoiceItem {
    id: string;                     
    
    service_id?: number | null;     
    name?: string | null;           

    quantity: number;                
    unit_price: number;             
    unit?: string;                    
    tax_rate: number;                
    discount_percentage?: number;     

    line_total: number;             
    line_tax?: number;               
    line_discount?: number;           
    net_amount?: number;             

    sort_order?: number;             
    
    created_at_formatted?: string;  
}