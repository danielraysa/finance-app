import { ref } from 'vue';

const notifications = ref([]);
let isListening = false;

export function useNotifications() {
    const listenForNotifications = (userId) => {
        if (isListening) return;
        isListening = true;

        // Listen for private notifications
        window.Echo.private(`App.Models.User.${userId}`)
            .notification((notification) => {
                console.log(notification);
                addNotification(notification);
            });

        // Listen for broadcast events. This event is broadcast as `event-updated`.
        /* window.Echo.private(`App.Models.User.${userId}`)
            .listen('.event-updated', (payload) => {
                console.log('EventProjectStatusUpdated payload', payload);

                const status = payload.status;
                const title = payload.title || 'Event project updated';

                let message = `Status changed to ${status}`;
                let type = 'info';

                if (status === 'approved') {
                    message = 'Your event project has been approved by the verifier.';
                    type = 'success';
                } else if (status === 'rejected') {
                    message = 'Your event project has been rejected by the verifier.';
                    type = 'error';
                }

                addNotification({
                    title,
                    message,
                    type,
                });
            }); */
    };

    const addNotification = (notification) => {
        const id = Date.now();
        notifications.value.push({
            id,
            ...notification,
        });

        // Auto-remove after 5 seconds
        setTimeout(() => {
            notifications.value = notifications.value.filter(n => n.id !== id);
        }, 5000);
    };

    const removeNotification = (id) => {
        notifications.value = notifications.value.filter(n => n.id !== id);
    };

    return { notifications, listenForNotifications, addNotification, removeNotification };
}
