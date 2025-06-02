import { toast } from 'vue-sonner';

export function useClipboard() {
    const copyToClipboard = async (text: string, label: string = 'Text') => {
        try {
            await navigator.clipboard.writeText(text);
            toast.success(`${label} copied to clipboard!`);
            return true;
        } catch (err) {
            toast.error('Failed to copy to clipboard');
            console.error('Clipboard error:', err);
            return false;
        }
    };

    const copyJson = async (data: any, label: string = 'JSON') => {
        try {
            const formatted = JSON.stringify(data, null, 2);
            return await copyToClipboard(formatted, label);
        } catch (err) {
            toast.error('Failed to format JSON for clipboard');
            console.error('JSON formatting error:', err);
            return false;
        }
    };

    return {
        copyToClipboard,
        copyJson
    };
}