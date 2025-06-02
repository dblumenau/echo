import { onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

export function useFlashMessages() {
    const page = usePage();

    const showFlashMessages = () => {
        const flash = page.props.flash as any;
        
        if (flash?.success) {
            toast.success(flash.success);
        }
        
        if (flash?.error) {
            toast.error(flash.error);
        }
        
        if (flash?.warning) {
            toast.warning(flash.warning);
        }
        
        if (flash?.info) {
            toast.info(flash.info);
        }
    };

    // Show flash messages on mount (for initial page load)
    onMounted(() => {
        showFlashMessages();
    });

    // Also show flash messages after Inertia navigations
    const removeFinishListener = router.on('finish', () => {
        showFlashMessages();
    });

    // Clean up the event listener
    onUnmounted(() => {
        removeFinishListener();
    });

    return {
        showFlashMessages
    };
}