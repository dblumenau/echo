<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css' // vue-sonner v2 requires this import
import { usePage } from '@inertiajs/vue3';
import { SharedData } from '@/types';
interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage<SharedData>().props.sidebarOpen;
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
    </SidebarProvider>
    <Toaster class="pointer-events-auto" />
</template>
