import { usePage } from '@inertiajs/react';
import { hasPermission } from '@/utils/authorization';

// Visibility only controls presentation; existing access permissions stay intact.
export function useModuleVisibility() {
    const { moduleVisibility = {} } = usePage().props as { moduleVisibility?: Record<string, boolean> };
    const isVisible = (key: string) => moduleVisibility[key] !== false;
    const canShow = (permissions: string[], permission: string) => {
        const key = permission === 'clock-in-out' ? 'attendance-records' : permission.replace(/^(manage|view)-/, '');
        return isVisible(key) && hasPermission(permissions, permission);
    };
    return { isVisible, canShow };
}
