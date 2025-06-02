import { ref, computed, watch, Ref } from 'vue';
import type { EchoRequest, DashboardFilters, MethodFilter, TimeFilter } from '@/types/echo';

export function useEchoRequests(requests: Ref<EchoRequest[]>) {
    // State
    const searchQuery = ref('');
    const methodFilter = ref<MethodFilter>('all');
    const timeFilter = ref<TimeFilter>('all');

    // Load saved filters from localStorage
    const loadFilters = () => {
        const savedFilters = localStorage.getItem('echoServerFilters');
        if (savedFilters) {
            const filters: DashboardFilters = JSON.parse(savedFilters);
            searchQuery.value = filters.searchQuery || '';
            methodFilter.value = filters.methodFilter || 'all';
            timeFilter.value = filters.timeFilter || 'all';
        }
    };

    // Save filters to localStorage when they change
    watch([searchQuery, methodFilter, timeFilter], () => {
        localStorage.setItem('echoServerFilters', JSON.stringify({
            searchQuery: searchQuery.value,
            methodFilter: methodFilter.value,
            timeFilter: timeFilter.value
        }));
    });

    // Computed filtered requests
    const filteredRequests = computed(() => {
        let filtered = requests.value;

        // Search filter
        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase();
            filtered = filtered.filter(req => 
                req.url.toLowerCase().includes(query) ||
                req.body?.toLowerCase().includes(query) ||
                req.ip_address.includes(query)
            );
        }

        // Method filter
        if (methodFilter.value !== 'all') {
            filtered = filtered.filter(req => req.method === methodFilter.value);
        }

        // Time filter
        if (timeFilter.value !== 'all') {
            const now = new Date();
            const filterTime = new Date();
            
            switch(timeFilter.value) {
                case 'hour':
                    filterTime.setHours(now.getHours() - 1);
                    break;
                case 'day':
                    filterTime.setDate(now.getDate() - 1);
                    break;
                case 'week':
                    filterTime.setDate(now.getDate() - 7);
                    break;
            }
            
            filtered = filtered.filter(req => new Date(req.created_at) >= filterTime);
        }

        return filtered;
    });

    // Clear all filters
    const clearFilters = () => {
        searchQuery.value = '';
        methodFilter.value = 'all';
        timeFilter.value = 'all';
    };

    // Check if any filters are active
    const hasActiveFilters = computed(() => 
        searchQuery.value !== '' || 
        methodFilter.value !== 'all' || 
        timeFilter.value !== 'all'
    );

    return {
        searchQuery,
        methodFilter,
        timeFilter,
        filteredRequests,
        clearFilters,
        hasActiveFilters,
        loadFilters
    };
}