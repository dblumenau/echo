import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

export function useRequestPolling(pollInterval: number = 5000) {
    const pollingInterval = ref<number | null>(null);
    const isPolling = ref(false);

    const startPolling = () => {
        if (pollingInterval.value) return; // Already polling
        
        isPolling.value = true;
        pollingInterval.value = window.setInterval(() => {
            router.reload({ 
                preserveState: true,
                preserveScroll: true,
                only: ['requests']
            });
        }, pollInterval);
    };

    const stopPolling = () => {
        if (pollingInterval.value) {
            clearInterval(pollingInterval.value);
            pollingInterval.value = null;
            isPolling.value = false;
        }
    };

    const togglePolling = () => {
        if (isPolling.value) {
            stopPolling();
        } else {
            startPolling();
        }
    };

    // Automatically start polling on mount and clean up on unmount
    onMounted(() => {
        startPolling();
    });

    onUnmounted(() => {
        stopPolling();
    });

    return {
        isPolling,
        startPolling,
        stopPolling,
        togglePolling
    };
}