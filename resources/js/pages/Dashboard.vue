<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref, onMounted, toRef } from 'vue';

// Dashboard components
import RequestFilters from '@/components/dashboard/RequestFilters.vue';
import RequestsTable from '@/components/dashboard/RequestsTable.vue';
import RequestsPagination from '@/components/dashboard/RequestsPagination.vue';
import RequestDetailsSheet from '@/components/dashboard/RequestDetailsSheet.vue';

// Types
import type { EchoRequest, PaginatedResponse } from '@/types/echo';

// Composables
import { useEchoRequests } from '@/composables/useEchoRequests';
import { useRequestPolling } from '@/composables/useRequestPolling';

interface Props {
    requests: PaginatedResponse<EchoRequest>;
}

const props = defineProps<Props>();

// Convert requests data to a ref for composables
const requestsData = toRef(() => props.requests.data);

// Composables
const {
    searchQuery,
    methodFilter,
    timeFilter,
    filteredRequests,
    clearFilters,
    hasActiveFilters,
    loadFilters
} = useEchoRequests(requestsData);

// Auto-polling is handled by the composable
useRequestPolling(5000);

// Component state
const selectedRequest = ref<EchoRequest | null>(null);
const sheetOpen = ref(false);

// Load filters on mount
onMounted(() => {
    loadFilters();
});

// Methods
const handleSelectRequest = (request: EchoRequest) => {
    selectedRequest.value = request;
    sheetOpen.value = true;
};
</script>

<template>

    <Head title="Echo Server Dashboard" />

    <AppLayout>
        <div class="container mx-auto py-8">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <span>Echo Server Requests</span>
                        <Badge variant="secondary" class="ml-auto">
                            {{ requests.total }} total
                        </Badge>
                    </CardTitle>
                    <CardDescription>
                        All incoming requests to the echo endpoints are displayed below
                    </CardDescription>

                    <!-- Filters -->
                    <RequestFilters v-model:search-query="searchQuery" v-model:method-filter="methodFilter"
                        v-model:time-filter="timeFilter" :has-active-filters="hasActiveFilters"
                        @clear-filters="clearFilters" />
                </CardHeader>
                <CardContent>
                    <div v-if="filteredRequests.length === 0" class="text-center py-8 text-muted-foreground">
                        <p v-if="requests.data.length === 0">
                            No requests have been received yet. Send a request to /echo/*, /webhook/*, or /api/echo/* to
                            see it here.
                        </p>
                        <p v-else>
                            No requests match your filters. Try adjusting your search criteria.
                        </p>
                    </div>
                    <div v-else>
                        <!-- Requests Table -->
                        <RequestsTable :requests="filteredRequests" @select-request="handleSelectRequest" />

                        <!-- Pagination -->
                        <RequestsPagination v-if="requests.last_page > 1" :from="requests.from" :to="requests.to"
                            :total="requests.total" :links="requests.links" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Request Details Sheet -->
        <RequestDetailsSheet v-model:open="sheetOpen" :request="selectedRequest" />
    </AppLayout>
</template>
