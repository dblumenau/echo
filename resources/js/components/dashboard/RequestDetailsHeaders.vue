<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { useClipboard } from '@/composables/useClipboard';
import { useRequestFormatters } from '@/composables/useRequestFormatters';

interface Props {
    headers: Record<string, any>;
    expanded: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:expanded': [value: boolean];
}>();

const { copyToClipboard } = useClipboard();
const { formatJson, getHeaderClass } = useRequestFormatters();

const handleCopy = (e: Event) => {
    e.stopPropagation();
    copyToClipboard(formatJson(props.headers), 'Headers');
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
                        Headers
                        <Badge variant="secondary" class="text-xs">{{ Object.keys(headers).length }}</Badge>
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
                <div class="mt-3 dashboard-form-data-container">
                    <div class="dashboard-headers-list">
                        <div 
                            v-for="(value, key) in headers" 
                            :key="key"
                            class="dashboard-header-item"
                        >
                            <span :class="['dashboard-header-key', getHeaderClass(String(key))]">
                                {{ key }}:
                            </span>
                            <span class="dashboard-header-value">
                                {{ Array.isArray(value) ? value.join(', ') : value }}
                            </span>
                        </div>
                    </div>
                </div>
            </CollapsibleContent>
        </Collapsible>
    </div>
</template>