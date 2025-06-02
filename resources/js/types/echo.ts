export interface EchoRequest {
    id: number;
    method: string;
    url: string;
    headers: Record<string, any>;
    query_params: Record<string, any> | null;
    body: string | null;
    ip_address: string;
    user_agent: string;
    response_status: number;
    created_at: string;
    updated_at: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

export type MethodFilter = 'all' | 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
export type TimeFilter = 'all' | 'hour' | 'day' | 'week';

export interface DashboardFilters {
    searchQuery: string;
    methodFilter: MethodFilter;
    timeFilter: TimeFilter;
}

export interface ExpandedSections {
    basic: boolean;
    query: boolean;
    headers: boolean;
    body: boolean;
}