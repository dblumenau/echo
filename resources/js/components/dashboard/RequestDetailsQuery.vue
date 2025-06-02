<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import JsonViewer from 'vue3-json-viewer';
import 'vue3-json-viewer/dist/index.css';
import { useClipboard } from '@/composables/useClipboard';
import { useRequestFormatters } from '@/composables/useRequestFormatters';

interface Props {
    queryParams: Record<string, any>;
    expanded: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:expanded': [value: boolean];
}>();

const { copyToClipboard } = useClipboard();
const { formatJson } = useRequestFormatters();

const handleCopy = (e: Event) => {
    e.stopPropagation();
    copyToClipboard(formatJson(props.queryParams), 'Query parameters');
};
</script>

<template>
    <div class="dashboard-collapsible-section">
        <Collapsible 
            :open="expanded"
            @update:open="emit('update:expanded', $event)"
        >
            <div class="dashboard-collapsible-header">
                <CollapsibleTrigger class="flex items-center justify-between w-full">
                    <span class="text-sm font-medium flex items-center gap-2">
                        Query Parameters
                        <Badge variant="secondary" class="text-xs">{{ Object.keys(queryParams).length }}</Badge>
                        <span class="text-xs text-muted-foreground">{{ expanded ? '▼' : '▶' }}</span>
                    </span>
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="handleCopy"
                    >
                        Copy
                    </Button>
                </CollapsibleTrigger>
            </div>
            <CollapsibleContent>
                <div class="mt-3 dashboard-json-viewer-container">
                    <JsonViewer 
                        :value="queryParams"
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
</template>