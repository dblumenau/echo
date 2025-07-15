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
        src="/images/logo.png" 
        alt="Echo Server Logo" 
        :class="[className, 'rounded-md']" 
        v-bind="$attrs"
    />
</template>
