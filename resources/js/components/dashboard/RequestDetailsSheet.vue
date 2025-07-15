<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import RequestDetailsBasic from './RequestDetailsBasic.vue';
import RequestDetailsQuery from './RequestDetailsQuery.vue';
import RequestDetailsHeaders from './RequestDetailsHeaders.vue';
import RequestDetailsBody from './RequestDetailsBody.vue';
import type { EchoRequest, ExpandedSections } from '@/types/echo';
import { useRequestFormatters } from '@/composables/useRequestFormatters';
import { useClipboard } from '@/composables/useClipboard';

interface Props {
    open: boolean;
    request: EchoRequest | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const { getMethodColor, getMethodIcon } = useRequestFormatters();
const { copyToClipboard } = useClipboard();

const expandedSections = ref<ExpandedSections>({
    basic: true,
    query: true,
    headers: true,
    body: true
});

// Reset expanded sections when a new request is selected
watch(() => props.request, () => {
    expandedSections.value = {
        basic: true,
        query: true,
        headers: true,
        body: true
    };
});

const toggleAllSections = (expand: boolean) => {
    Object.keys(expandedSections.value).forEach(key => {
        expandedSections.value[key as keyof ExpandedSections] = expand;
    });
};

const hasAnySectionExpanded = computed(() => 
    Object.values(expandedSections.value).some(v => v)
);
</script>

<template>
    <Sheet 
        :open="open"
        @update:open="emit('update:open', $event)"
    >
        <SheetContent class="w-[90vw] max-w-[1400px] sm:max-w-none h-[95vh] flex flex-col">
            <SheetHeader class="flex-shrink-0">
                <SheetTitle class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        Request Details
                        <Badge 
                            v-if="request"
                            :class="getMethodColor(request.method)" 
                            class="text-white font-mono"
                        >
                            <span class="mr-1">{{ getMethodIcon(request.method) }}</span>
                            {{ request.method }}
                        </Badge>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="toggleAllSections(!hasAnySectionExpanded)"
                    >
                        {{ hasAnySectionExpanded ? 'Collapse All' : 'Expand All' }}
                    </Button>
                </SheetTitle>
                <SheetDescription class="flex items-center justify-between">
                    <span class="truncate pr-2">{{ request?.url }}</span>
                    <Button
                        v-if="request"
                        variant="ghost"
                        size="sm"
                        @click="copyToClipboard(request.url, 'URL')"
                    >
                        Copy URL
                    </Button>
                </SheetDescription>
            </SheetHeader>
            
            <div class="flex-1 overflow-y-auto mt-6 pr-4">
                <div v-if="request" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <!-- Basic Info -->
                        <RequestDetailsBasic
                            :request="request"
                            v-model:expanded="expandedSections.basic"
                        />

                        <!-- Headers -->
                        <RequestDetailsHeaders
                            :headers="request.headers"
                            v-model:expanded="expandedSections.headers"
                        />

                        <!-- Query Parameters -->
                        <RequestDetailsQuery
                            v-if="request.query_params && Object.keys(request.query_params).length > 0"
                            :query-params="request.query_params"
                            v-model:expanded="expandedSections.query"
                        />
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <!-- Request Body -->
                        <RequestDetailsBody
                            v-if="request.body"
                            :body="request.body"
                            :headers="request.headers"
                            v-model:expanded="expandedSections.body"
                        />
                    </div>
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>