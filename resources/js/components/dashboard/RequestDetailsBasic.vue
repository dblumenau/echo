<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import type { EchoRequest } from '@/types/echo';

interface Props {
    request: EchoRequest;
    expanded: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    'update:expanded': [value: boolean];
}>();
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
                        Basic Information
                        <span class="text-xs text-muted-foreground">{{ expanded ? '▼' : '▶' }}</span>
                    </span>
                </CollapsibleTrigger>
            </div>
            <CollapsibleContent>
                <div class="mt-3 space-y-1">
                    <div class="dashboard-basic-info-row">
                        <span class="dashboard-basic-info-label">Status:</span>
                        <Badge variant="outline">{{ request.response_status }}</Badge>
                    </div>
                    <div class="dashboard-basic-info-row">
                        <span class="dashboard-basic-info-label">IP Address:</span>
                        <span class="font-mono text-sm">{{ request.ip_address }}</span>
                    </div>
                    <div class="dashboard-basic-info-row">
                        <span class="dashboard-basic-info-label">Time:</span>
                        <span class="text-sm">{{ new Date(request.created_at).toLocaleString() }}</span>
                    </div>
                    <div class="dashboard-basic-info-row">
                        <span class="dashboard-basic-info-label">User Agent:</span>
                        <span class="font-mono text-xs break-all">{{ request.user_agent || 'None' }}</span>
                    </div>
                </div>
            </CollapsibleContent>
        </Collapsible>
    </div>
</template>