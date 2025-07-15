<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    from: number;
    to: number;
    total: number;
    links: PaginationLink[];
}

defineProps<Props>();

const goToPage = (url: string | null) => {
    if (url) {
        router.visit(url, {
            preserveState: true,
            preserveScroll: false
        });
    }
};
</script>

<template>
    <div class="mt-6 flex items-center justify-between">
        <p class="text-sm text-muted-foreground">
            Showing {{ from }} to {{ to }} of {{ total }} requests
        </p>
        <div class="flex gap-2">
            <Button
                v-for="link in links"
                :key="link.label"
                :variant="link.active ? 'default' : 'outline'"
                size="sm"
                :disabled="!link.url"
                @click="goToPage(link.url)"
                v-html="link.label"
            />
        </div>
    </div>
</template>