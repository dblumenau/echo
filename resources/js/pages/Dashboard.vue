<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Separator } from '@/components/ui/separator';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { formatDistanceToNow } from 'date-fns';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { toast } from 'vue-sonner';
import JsonViewer from 'vue3-json-viewer';
import 'vue3-json-viewer/dist/index.css';

interface EchoRequest {
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

interface Props {
    requests: {
        data: EchoRequest[];
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
    };
}

const props = defineProps<Props>();

// State
const selectedRequest = ref<EchoRequest | null>(null);
const sheetOpen = ref(false);
const searchQuery = ref('');
const methodFilter = ref('all');
const timeFilter = ref('all');
const expandedSections = ref({
    basic: true,
    query: true,
    headers: true,
    body: true
});

// Load saved filters from localStorage
onMounted(() => {
    const savedFilters = localStorage.getItem('echoServerFilters');
    if (savedFilters) {
        const filters = JSON.parse(savedFilters);
        searchQuery.value = filters.searchQuery || '';
        methodFilter.value = filters.methodFilter || 'all';
        timeFilter.value = filters.timeFilter || 'all';
    }

    // Set up polling for real-time updates
    startPolling();
});

// Save filters to localStorage when they change
watch([searchQuery, methodFilter, timeFilter], () => {
    localStorage.setItem('echoServerFilters', JSON.stringify({
        searchQuery: searchQuery.value,
        methodFilter: methodFilter.value,
        timeFilter: timeFilter.value
    }));
});

// Polling for real-time updates
let pollingInterval: number | null = null;

const startPolling = () => {
    pollingInterval = window.setInterval(() => {
        router.reload({ 
            preserveState: true,
            preserveScroll: true,
            only: ['requests']
        });
    }, 5000); // Poll every 5 seconds
};

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});

// Computed
const filteredRequests = computed(() => {
    let filtered = props.requests.data;

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

// Methods
const getMethodColor = (method: string) => {
    const colors: Record<string, string> = {
        GET: 'bg-emerald-500 hover:bg-emerald-600',
        POST: 'bg-blue-500 hover:bg-blue-600',
        PUT: 'bg-amber-500 hover:bg-amber-600',
        PATCH: 'bg-orange-500 hover:bg-orange-600',
        DELETE: 'bg-red-500 hover:bg-red-600',
    };
    return colors[method] || 'bg-gray-500 hover:bg-gray-600';
};

const getMethodIcon = (method: string) => {
    const icons: Record<string, string> = {
        GET: '↓',
        POST: '↑',
        PUT: '↻',
        PATCH: '⟳',
        DELETE: '×',
    };
    return icons[method] || '•';
};

const truncate = (str: string | null, length: number = 50) => {
    if (!str) return '';
    return str.length > length ? str.slice(0, length) + '...' : str;
};

const openRequestDetails = (request: EchoRequest) => {
    selectedRequest.value = request;
    sheetOpen.value = true;
};

const copyToClipboard = async (text: string, label: string) => {
    try {
        await navigator.clipboard.writeText(text);
        toast.success(`${label} copied to clipboard!`);
    } catch (err) {
        toast.error('Failed to copy to clipboard');
    }
};

const formatJson = (data: any) => {
    try {
        if (typeof data === 'string') {
            const parsed = JSON.parse(data);
            return JSON.stringify(parsed, null, 2);
        }
        return JSON.stringify(data, null, 2);
    } catch {
        return data;
    }
};

const isJsonContent = (headers: Record<string, any>) => {
    const contentType = headers['content-type']?.[0] || '';
    return contentType.includes('application/json');
};

const getContentTypeIcon = (headers: Record<string, any>) => {
    const contentType = headers['content-type']?.[0] || '';
    if (contentType.includes('application/json')) return '{ }';
    if (contentType.includes('application/x-www-form-urlencoded')) return '≡';
    if (contentType.includes('multipart/form-data')) return '📎';
    if (contentType.includes('text/')) return '📄';
    return '📦';
};

const formatRequestBody = (body: string | null, headers: Record<string, any>) => {
    if (!body) return null;
    
    const contentType = headers['content-type']?.[0] || '';
    
    // JSON
    if (contentType.includes('application/json')) {
        try {
            return { type: 'json', data: JSON.parse(body) };
        } catch {
            return { type: 'text', data: body };
        }
    }
    
    // Form data
    if (contentType.includes('application/x-www-form-urlencoded')) {
        try {
            const params = new URLSearchParams(body);
            const data: Record<string, string> = {};
            params.forEach((value, key) => {
                data[key] = value;
            });
            return { type: 'form', data };
        } catch {
            return { type: 'text', data: body };
        }
    }
    
    return { type: 'text', data: body };
};

const goToPage = (url: string | null) => {
    if (url) {
        router.visit(url, {
            preserveState: true,
            preserveScroll: false
        });
    }
};

const clearFilters = () => {
    searchQuery.value = '';
    methodFilter.value = 'all';
    timeFilter.value = 'all';
};

const toggleAllSections = (expand: boolean) => {
    Object.keys(expandedSections.value).forEach(key => {
        expandedSections.value[key as keyof typeof expandedSections.value] = expand;
    });
};

const getHeaderClass = (headerName: string): string => {
    const name = headerName.toLowerCase();
    
    // Authentication headers
    if (name.includes('authorization') || name.includes('x-api-key') || name.includes('x-auth-token') || name.includes('token')) {
        return 'header-auth';
    }
    
    // Content headers
    if (name.includes('content-') || name === 'content') {
        return 'header-content';
    }
    
    // Request headers
    if (name.includes('accept') || name === 'user-agent' || name.includes('x-requested-with')) {
        return 'header-request';
    }
    
    // CORS headers
    if (name.includes('origin') || name.includes('access-control') || name.includes('cors')) {
        return 'header-cors';
    }
    
    // Host headers
    if (name === 'host' || name === 'referer' || name === 'referrer') {
        return 'header-host';
    }
    
    return 'header-default';
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
                    <div class="mt-4 flex flex-wrap gap-3">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by URL, body, or IP..."
                            class="max-w-xs"
                        />
                        
                        <Select v-model="methodFilter">
                            <SelectTrigger class="w-[140px]">
                                <SelectValue placeholder="All methods" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All methods</SelectItem>
                                <SelectItem value="GET">GET</SelectItem>
                                <SelectItem value="POST">POST</SelectItem>
                                <SelectItem value="PUT">PUT</SelectItem>
                                <SelectItem value="PATCH">PATCH</SelectItem>
                                <SelectItem value="DELETE">DELETE</SelectItem>
                            </SelectContent>
                        </Select>
                        
                        <Select v-model="timeFilter">
                            <SelectTrigger class="w-[140px]">
                                <SelectValue placeholder="All time" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All time</SelectItem>
                                <SelectItem value="hour">Last hour</SelectItem>
                                <SelectItem value="day">Last 24 hours</SelectItem>
                                <SelectItem value="week">Last 7 days</SelectItem>
                            </SelectContent>
                        </Select>
                        
                        <Button 
                            v-if="searchQuery || methodFilter !== 'all' || timeFilter !== 'all'"
                            variant="outline" 
                            size="sm"
                            @click="clearFilters"
                        >
                            Clear filters
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="filteredRequests.length === 0" class="text-center py-8 text-muted-foreground">
                        <p v-if="requests.data.length === 0">
                            No requests have been received yet. Send a request to /echo/*, /webhook/*, or /api/echo/* to see it here.
                        </p>
                        <p v-else>
                            No requests match your filters. Try adjusting your search criteria.
                        </p>
                    </div>
                    <div v-else>
                        <TooltipProvider>
                            <div class="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead class="w-[100px]">Method</TableHead>
                                            <TableHead class="w-[40px]">Type</TableHead>
                                            <TableHead>URL</TableHead>
                                            <TableHead>Body Preview</TableHead>
                                            <TableHead>IP Address</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead>Time</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow 
                                            v-for="request in filteredRequests" 
                                            :key="request.id"
                                            class="cursor-pointer hover:bg-muted/50 transition-colors"
                                            @click="openRequestDetails(request)"
                                        >
                                            <TableCell>
                                                <Badge :class="getMethodColor(request.method)" class="text-white font-mono">
                                                    <span class="mr-1">{{ getMethodIcon(request.method) }}</span>
                                                    {{ request.method }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-center text-muted-foreground">
                                                {{ getContentTypeIcon(request.headers) }}
                                            </TableCell>
                                            <TableCell>
                                                <Tooltip>
                                                    <TooltipTrigger class="font-mono text-sm truncate block max-w-[300px]">
                                                        {{ request.url }}
                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        <p class="max-w-[500px] break-all">{{ request.url }}</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TableCell>
                                            <TableCell>
                                                <span v-if="!request.body" class="text-muted-foreground">-</span>
                                                <Tooltip v-else>
                                                    <TooltipTrigger class="font-mono text-sm text-muted-foreground truncate block max-w-[200px]">
                                                        {{ truncate(request.body, 40) }}
                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        <p class="max-w-[500px] break-all">{{ truncate(request.body, 200) }}</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TableCell>
                                            <TableCell class="font-mono text-sm">{{ request.ip_address }}</TableCell>
                                            <TableCell>
                                                <Badge variant="outline">{{ request.response_status }}</Badge>
                                            </TableCell>
                                            <TableCell class="text-muted-foreground text-sm">
                                                <Tooltip>
                                                    <TooltipTrigger>
                                                        {{ formatDistanceToNow(new Date(request.created_at), { addSuffix: true }) }}
                                                    </TooltipTrigger>
                                                    <TooltipContent>
                                                        <p>{{ new Date(request.created_at).toLocaleString() }}</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </TooltipProvider>
                        
                        <!-- Pagination -->
                        <div v-if="requests.last_page > 1" class="mt-6 flex items-center justify-between">
                            <p class="text-sm text-muted-foreground">
                                Showing {{ requests.from }} to {{ requests.to }} of {{ requests.total }} requests
                            </p>
                            <div class="flex gap-2">
                                <Button
                                    v-for="link in requests.links"
                                    :key="link.label"
                                    :variant="link.active ? 'default' : 'outline'"
                                    size="sm"
                                    :disabled="!link.url"
                                    @click="goToPage(link.url)"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Request Details Sheet -->
        <Sheet v-model:open="sheetOpen">
            <SheetContent class="w-[700px] sm:w-[800px] sm:max-w-none">
                <SheetHeader>
                    <SheetTitle class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            Request Details
                            <Badge :class="getMethodColor(selectedRequest?.method || '')" class="text-white font-mono">
                                <span class="mr-1">{{ getMethodIcon(selectedRequest?.method || '') }}</span>
                                {{ selectedRequest?.method }}
                            </Badge>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="toggleAllSections(!Object.values(expandedSections).some(v => v))"
                        >
                            {{ Object.values(expandedSections).some(v => v) ? 'Collapse All' : 'Expand All' }}
                        </Button>
                    </SheetTitle>
                    <SheetDescription class="flex items-center justify-between">
                        <span class="truncate pr-2">{{ selectedRequest?.url }}</span>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="copyToClipboard(selectedRequest?.url || '', 'URL')"
                        >
                            Copy URL
                        </Button>
                    </SheetDescription>
                </SheetHeader>
                
                <div class="h-[calc(100vh-120px)] mt-6 overflow-y-auto pr-4">
                    <div v-if="selectedRequest" class="space-y-4">
                        <!-- Basic Info -->
                        <div class="collapsible-section">
                            <Collapsible v-model:open="expandedSections.basic">
                                <div class="collapsible-header">
                                    <CollapsibleTrigger class="flex items-center justify-between w-full">
                                        <span class="text-sm font-medium flex items-center gap-2">
                                            Basic Information
                                            <span class="text-xs text-muted-foreground">{{ expandedSections.basic ? '▼' : '▶' }}</span>
                                        </span>
                                    </CollapsibleTrigger>
                                </div>
                            <CollapsibleContent>
                                <div class="mt-3 space-y-1">
                                    <div class="basic-info-row">
                                        <span class="basic-info-label">Status:</span>
                                        <Badge variant="outline">{{ selectedRequest.response_status }}</Badge>
                                    </div>
                                    <div class="basic-info-row">
                                        <span class="basic-info-label">IP Address:</span>
                                        <span class="font-mono text-sm">{{ selectedRequest.ip_address }}</span>
                                    </div>
                                    <div class="basic-info-row">
                                        <span class="basic-info-label">Time:</span>
                                        <span class="text-sm">{{ new Date(selectedRequest.created_at).toLocaleString() }}</span>
                                    </div>
                                    <div class="basic-info-row">
                                        <span class="basic-info-label">User Agent:</span>
                                        <span class="font-mono text-xs break-all">{{ selectedRequest.user_agent || 'None' }}</span>
                                    </div>
                                </div>
                            </CollapsibleContent>
                            </Collapsible>
                        </div>

                        <!-- Query Parameters -->
                        <div v-if="selectedRequest.query_params && Object.keys(selectedRequest.query_params).length > 0" class="collapsible-section">
                            <Collapsible v-model:open="expandedSections.query">
                                <div class="collapsible-header">
                                    <CollapsibleTrigger class="flex items-center justify-between w-full">
                                        <span class="text-sm font-medium flex items-center gap-2">
                                            Query Parameters
                                            <Badge variant="secondary" class="text-xs">{{ Object.keys(selectedRequest.query_params).length }}</Badge>
                                            <span class="text-xs text-muted-foreground">{{ expandedSections.query ? '▼' : '▶' }}</span>
                                        </span>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click.stop="copyToClipboard(formatJson(selectedRequest.query_params), 'Query parameters')"
                                        >
                                            Copy
                                        </Button>
                                    </CollapsibleTrigger>
                                </div>
                                <CollapsibleContent>
                                    <div class="mt-3 json-viewer-container">
                                        <JsonViewer 
                                            :value="selectedRequest.query_params"
                                            :theme="'jv-light'"
                                            :expanded="true"
                                            :expandDepth="2"
                                            copyable
                                            boxed
                                            sort
                                        />
                                    </div>
                                </CollapsibleContent>
                            </Collapsible>
                        </div>

                        <!-- Headers -->
                        <div class="collapsible-section">
                            <Collapsible v-model:open="expandedSections.headers">
                                <div class="collapsible-header">
                                    <CollapsibleTrigger class="flex items-center justify-between w-full">
                                        <span class="text-sm font-medium flex items-center gap-2">
                                            Headers
                                            <Badge variant="secondary" class="text-xs">{{ Object.keys(selectedRequest.headers).length }}</Badge>
                                            <span class="text-xs text-muted-foreground">{{ expandedSections.headers ? '▼' : '▶' }}</span>
                                        </span>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click.stop="copyToClipboard(formatJson(selectedRequest.headers), 'Headers')"
                                        >
                                            Copy
                                        </Button>
                                    </CollapsibleTrigger>
                                </div>
                                <CollapsibleContent>
                                    <div class="mt-3 form-data-container">
                                        <div class="headers-list">
                                            <div 
                                                v-for="(value, key) in selectedRequest.headers" 
                                                :key="key"
                                                class="header-item"
                                            >
                                                <span :class="['header-key', getHeaderClass(String(key))]">
                                                    {{ key }}:
                                                </span>
                                                <span class="header-value">
                                                    {{ Array.isArray(value) ? value.join(', ') : value }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </CollapsibleContent>
                            </Collapsible>
                        </div>

                        <!-- Request Body -->
                        <div v-if="selectedRequest.body" class="collapsible-section">
                            <Collapsible v-model:open="expandedSections.body">
                                <div class="collapsible-header">
                                    <CollapsibleTrigger class="flex items-center justify-between w-full">
                                        <span class="text-sm font-medium flex items-center gap-2">
                                            Request Body
                                            <Badge variant="secondary" class="text-xs">{{ selectedRequest.body.length }} chars</Badge>
                                            <span class="text-xs text-muted-foreground">{{ expandedSections.body ? '▼' : '▶' }}</span>
                                        </span>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click.stop="copyToClipboard(selectedRequest.body || '', 'Request body')"
                                        >
                                            Copy
                                        </Button>
                                    </CollapsibleTrigger>
                                </div>
                                <CollapsibleContent>
                                    <div class="mt-3">
                                        <div v-if="formatRequestBody(selectedRequest.body, selectedRequest.headers)?.type === 'json'" class="json-viewer-container">
                                            <JsonViewer 
                                                :value="formatRequestBody(selectedRequest.body, selectedRequest.headers)!.data"
                                                :theme="'jv-light'"
                                                :expanded="true"
                                                :expandDepth="3"
                                                copyable
                                                boxed
                                                sort
                                            />
                                        </div>
                                        <div v-else-if="formatRequestBody(selectedRequest.body, selectedRequest.headers)?.type === 'form'" class="form-data-container">
                                            <div v-for="(value, key) in formatRequestBody(selectedRequest.body, selectedRequest.headers)!.data" :key="key" class="form-data-row">
                                                <span class="form-data-key">{{ key }}:</span>
                                                <span class="form-data-value">{{ value }}</span>
                                            </div>
                                        </div>
                                        <pre v-else class="plain-text-body">{{ selectedRequest.body }}</pre>
                                    </div>
                                </CollapsibleContent>
                            </Collapsible>
                        </div>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>

<style>
/* Enhanced JSON viewer container styling */
.json-viewer-container {
    border-radius: var(--radius-lg);
    border: 1px solid #e1e4e8;
    background-color: #f6f8fa;
    padding: 1rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.dark .json-viewer-container {
    background-color: #161b22;
    border-color: #30363d;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

/* Collapsible section styling */
.collapsible-section {
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border);
    background-color: color-mix(in oklab, var(--color-card) 50%, transparent);
    padding: 1rem;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s;
}

.collapsible-header {
    margin: -1rem;
    margin-bottom: 0;
    padding: 1rem;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    background-color: color-mix(in oklab, var(--color-muted) 20%, transparent);
    border-bottom: 1px solid var(--color-border);
}

.dark .collapsible-header {
    background-color: color-mix(in oklab, var(--color-muted) 10%, transparent);
}

/* Form data styling */
.form-data-container {
    border-radius: var(--radius-lg);
    background-color: #f6f8fa;
    padding: 1rem;
    border: 1px solid #e1e4e8;
}

.dark .form-data-container {
    background-color: #161b22;
    border-color: #30363d;
}

.form-data-container > * + * {
    margin-top: 0.75rem;
}

.form-data-row {
    display: flex;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: var(--radius-md);
    transition: background-color 0.2s;
}

.form-data-row:hover {
    background-color: #f3f4f6;
}

.dark .form-data-row:hover {
    background-color: #1c2128;
}

.form-data-key {
    font-family: var(--font-family-mono);
    font-weight: 600;
    color: #0969da;
    min-width: 150px;
    font-size: 0.875rem;
}

.dark .form-data-key {
    color: #58a6ff;
}

.form-data-value {
    font-family: var(--font-family-mono);
    word-break: break-all;
    font-size: 0.875rem;
    color: #0a3069;
}

.dark .form-data-value {
    color: #79c0ff;
}

/* Plain text body styling */
.plain-text-body {
    background-color: color-mix(in oklab, var(--color-muted) 30%, transparent);
    padding: 1rem;
    border-radius: var(--radius-lg);
    overflow-x: auto;
    font-size: 0.75rem;
    font-family: var(--font-family-mono);
    white-space: pre-wrap;
    word-break: break-all;
}

/* Override vue3-json-viewer styles for GitHub theme */
.jv-light {
    background: transparent !important;
    color: #24292e !important;
    font-size: 0.875rem !important;
    line-height: 1.6 !important;
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace !important;
}

.jv-light .jv-ellipsis {
    color: #586069 !important;
    background: #dfe2e5 !important;
    padding: 2px 6px !important;
    border-radius: 3px !important;
    font-size: 0.75rem !important;
}

.jv-light .jv-button {
    color: #0969da !important;
    font-weight: 500 !important;
}

.jv-light .jv-button:hover {
    color: #0860ca !important;
    text-decoration: underline !important;
}

.jv-light .jv-key {
    color: #0550ae !important;
    font-weight: 600 !important;
}

.jv-light .jv-item.jv-string {
    color: #0a3069 !important;
}

.jv-light .jv-item.jv-number {
    color: #0550ae !important;
    font-weight: 500 !important;
}

.jv-light .jv-item.jv-boolean {
    color: #0550ae !important;
    font-weight: 600 !important;
}

.jv-light .jv-item.jv-null {
    color: #6e7781 !important;
    font-weight: 600 !important;
}

.jv-light .jv-item.jv-undefined {
    color: #6e7781 !important;
    font-weight: 600 !important;
}

.jv-light .jv-item.jv-object,
.jv-light .jv-item.jv-array {
    color: #24292e !important;
}

.jv-light .jv-push {
    margin-left: 1.25rem !important;
}

/* Add syntax highlighting for brackets and punctuation */
.jv-light .jv-node:before,
.jv-light .jv-node:after {
    color: #24292e !important;
    font-weight: 400 !important;
}

/* Dark mode styles */
.dark .jv-light {
    color: #e6edf3 !important;
}

.dark .jv-light .jv-ellipsis {
    color: #8b949e !important;
    background: #30363d !important;
}

.dark .jv-light .jv-button {
    color: #58a6ff !important;
}

.dark .jv-light .jv-button:hover {
    color: #79c0ff !important;
    text-decoration: underline !important;
}

.dark .jv-light .jv-key {
    color: #7ee787 !important;
    font-weight: 600 !important;
}

.dark .jv-light .jv-item.jv-string {
    color: #a5d6ff !important;
}

.dark .jv-light .jv-item.jv-number {
    color: #79c0ff !important;
    font-weight: 500 !important;
}

.dark .jv-light .jv-item.jv-boolean {
    color: #ff7b72 !important;
    font-weight: 600 !important;
}

.dark .jv-light .jv-item.jv-null {
    color: #8b949e !important;
    font-weight: 600 !important;
}

.dark .jv-light .jv-item.jv-undefined {
    color: #8b949e !important;
    font-weight: 600 !important;
}

.dark .jv-light .jv-item.jv-object,
.dark .jv-light .jv-item.jv-array {
    color: #e6edf3 !important;
}

/* Dark mode syntax highlighting for brackets */
.dark .jv-light .jv-node:before,
.dark .jv-light .jv-node:after {
    color: #e6edf3 !important;
    font-weight: 400 !important;
}

/* Copy button styling in JSON viewer */
.jv-light .jv-copy {
    opacity: 0.6 !important;
    transition: opacity 0.2s;
}

.jv-light .jv-copy:hover {
    opacity: 1 !important;
}

/* Headers list styling */
.headers-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.header-item {
    display: flex;
    align-items: start;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: var(--radius-md);
    transition: background-color 0.2s;
    font-family: var(--font-family-mono);
    font-size: 0.875rem;
}

.header-item:hover {
    background-color: #f3f4f6;
}

.dark .header-item:hover {
    background-color: #1c2128;
}

.header-key {
    font-weight: 600;
    min-width: 180px;
    word-break: break-all;
}

.header-value {
    color: #24292e;
    word-break: break-all;
    flex: 1;
}

.dark .header-value {
    color: #e6edf3;
}

/* Header type color coding */
.header-auth {
    color: #cf222e !important;
}

.dark .header-auth {
    color: #f85149 !important;
}

.header-content {
    color: #1f883d !important;
}

.dark .header-content {
    color: #3fb950 !important;
}

.header-request {
    color: #8250df !important;
}

.dark .header-request {
    color: #a371f7 !important;
}

.header-cors {
    color: #fb8500 !important;
}

.dark .header-cors {
    color: #fb8f3d !important;
}

.header-host {
    color: #0969da !important;
}

.dark .header-host {
    color: #58a6ff !important;
}

.header-default {
    color: #0550ae !important;
}

.dark .header-default {
    color: #79c0ff !important;
}

/* Basic info section styling */
.basic-info-row {
    display: flex;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid color-mix(in oklab, var(--color-border) 50%, transparent);
}

.basic-info-row:last-child {
    border-bottom: none;
}

.basic-info-label {
    color: var(--color-muted-foreground);
    width: 8rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.basic-info-value {
    font-size: 0.875rem;
}
</style>