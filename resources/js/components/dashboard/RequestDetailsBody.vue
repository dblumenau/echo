<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import JsonViewer from 'vue3-json-viewer';
import 'vue3-json-viewer/dist/index.css';
import { useClipboard } from '@/composables/useClipboard';
import { useRequestFormatters } from '@/composables/useRequestFormatters';

interface Props {
    body: string;
    headers: Record<string, any>;
    expanded: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:expanded': [value: boolean];
}>();

const { copyToClipboard } = useClipboard();
const { formatRequestBody } = useRequestFormatters();

const handleCopy = (e: Event) => {
    e.stopPropagation();
    copyToClipboard(props.body, 'Request body');
};

const formattedBody = computed(() => formatRequestBody(props.body, props.headers));
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
                        Request Body
                        <Badge variant="secondary" class="text-xs">{{ body.length }} chars</Badge>
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
                <div class="mt-3">
                    <div v-if="formattedBody?.type === 'json'" class="dashboard-json-viewer-container">
                        <JsonViewer 
                            :value="formattedBody.data"
                            theme="light"
                            :expanded="true"
                            :expand-depth="2"
                            copyable
                            boxed
                            sort
                        />
                    </div>
                    <div v-else-if="formattedBody?.type === 'form'" class="dashboard-form-data-container">
                        <div v-for="(value, key) in formattedBody.data" :key="key" class="dashboard-form-data-row">
                            <span class="dashboard-form-data-key">{{ key }}:</span>
                            <span class="dashboard-form-data-value">{{ value }}</span>
                        </div>
                    </div>
                    <pre v-else class="dashboard-plain-text-body">{{ body }}</pre>
                </div>
            </CollapsibleContent>
        </Collapsible>
    </div>
</template>