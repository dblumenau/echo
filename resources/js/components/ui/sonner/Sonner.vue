<script lang="ts" setup>
import { Toaster as Sonner, type ToasterProps } from 'vue-sonner'

const props = defineProps<ToasterProps>()

// Configure toast styling for different types
const toastOptions = {
  unstyled: true,
  classes: {
    toast: 'bg-background border border-border rounded-lg shadow-lg p-4 flex items-center gap-3',
    success: 'border-l-4 border-l-green-500 bg-background text-foreground',
    error: 'border-l-4 border-l-red-500 bg-background text-foreground',
    warning: 'border-l-4 border-l-amber-500 bg-background text-foreground',
    info: 'border-l-4 border-l-blue-500 bg-background text-foreground',
  }
}
</script>

<template>
  <Sonner
    class="toaster group"
    v-bind="props"
    :toast-options="toastOptions"
    :style="{
      '--normal-bg': 'var(--popover)',
      '--normal-text': 'var(--popover-foreground)',
      '--normal-border': 'var(--border)',

    }"
  />
</template>
