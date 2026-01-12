import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notification', () => {
    const notifications = ref([]);

    /**
     * Add a notification
     * @param {Object} notification - { type: 'success'|'error'|'info'|'warning', message: string, duration: number }
     */
    const add = (notification) => {
        const id = Date.now() + Math.random().toString(36).substr(2, 9);
        const duration = notification.duration || 5000;

        const newNotification = {
            id,
            type: notification.type || 'info',
            message: notification.message,
            duration
        };

        notifications.value.push(newNotification);

        if (duration > 0) {
            setTimeout(() => {
                remove(id);
            }, duration);
        }

        return id;
    };

    const remove = (id) => {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index !== -1) {
            notifications.value.splice(index, 1);
        }
    };

    const success = (message, duration) => add({ type: 'success', message, duration });
    const error = (message, duration) => add({ type: 'error', message, duration });
    const info = (message, duration) => add({ type: 'info', message, duration });
    const warning = (message, duration) => add({ type: 'warning', message, duration });

    return {
        notifications,
        add,
        remove,
        success,
        error,
        info,
        warning
    };
});
