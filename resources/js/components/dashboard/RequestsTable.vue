<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { formatDistanceToNow } from 'date-fns';
import type { EchoRequest } from '@/types/echo';
import { useRequestFormatters } from '@/composables/useRequestFormatters';

interface Props {
    requests: EchoRequest[];
}

defineProps<Props>();

const emit = defineEmits<{
    selectRequest: [request: EchoRequest];
}>();

const {
    getMethodColor,
    getMethodIcon,
    truncate,
    getContentTypeIcon
} = useRequestFormatters();

const handleRowClick = (request: EchoRequest) => {
    emit('selectRequest', request);
};
</script>

<template>
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
                        v-for="request in requests" 
                        :key="request.id"
                        class="cursor-pointer hover:bg-muted/50 transition-colors"
                        @click="handleRowClick(request)"
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
</template>