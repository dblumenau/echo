<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import type { MethodFilter, TimeFilter } from '@/types/echo';

interface Props {
    searchQuery: string;
    methodFilter: MethodFilter;
    timeFilter: TimeFilter;
    hasActiveFilters: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    'update:searchQuery': [value: string];
    'update:methodFilter': [value: MethodFilter];
    'update:timeFilter': [value: TimeFilter];
    clearFilters: [];
}>();
</script>

<template>
    <div class="mt-4 flex flex-wrap gap-3">
        <Input
            :model-value="searchQuery"
            @update:model-value="emit('update:searchQuery', $event)"
            placeholder="Search by URL, body, or IP..."
            class="max-w-xs"
        />
        
        <Select 
            :model-value="methodFilter"
            @update:model-value="emit('update:methodFilter', $event as MethodFilter)"
        >
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
        
        <Select 
            :model-value="timeFilter"
            @update:model-value="emit('update:timeFilter', $event as TimeFilter)"
        >
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
            v-if="hasActiveFilters"
            variant="outline" 
            size="sm"
            @click="emit('clearFilters')"
        >
            Clear filters
        </Button>
    </div>
</template>