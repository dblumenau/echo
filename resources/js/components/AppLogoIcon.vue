<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img 
        src="/swift_danish_logo.png" 
        alt="Swift Danish Logo" 
        :class="className" 
        v-bind="$attrs"
    />
</template>
