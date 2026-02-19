<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    notification: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'notification-click']);

const isDeleting = ref(false);

const isRead = computed(() => {
    return props.notification.read_at !== null;
});

const formattedTime = computed(() => {
    const date = new Date(props.notification.created_at);
    const now = new Date();
    const diff = now - date;

    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;

    return date.toLocaleDateString();
});

const notificationContent = computed(() => {
    try {
        const data = typeof props.notification.data === 'string'
            ? JSON.parse(props.notification.data)
            : props.notification.data;
        return data;
    } catch {
        return props.notification.data;
    }
});

const getNotificationTitle = computed(() => {
    const content = notificationContent.value;
    if (typeof content === 'string') return content;
    return content?.title || 'New notification';
});

const getNotificationLink = computed(() => {
    const content = notificationContent.value;
    if (typeof content === 'string') return content;
    return content?.link || '#';
});

const getMessage = computed(() => {
    const content = notificationContent.value;
    if (typeof content === 'string') {
        return content;
    }
    return content?.message || content?.title || 'New notification';
});

const markAsRead = async () => {
    if (isRead.value) {
        if (getNotificationLink.value && getNotificationLink.value !== '#') {
            return router.visit(getNotificationLink.value);
        }
        return;
    }

    try {
        // await fetch(`/notifications/${props.notification.id}/mark-as-read`, {
        //     method: 'POST',
        //     headers: {
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        //     },
        // });
        router.post(route('notifications.mark-as-read', props.notification.id), {
            onSuccess: () => {
                emit('notification-click');
            },
        });
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const deleteNotification = async (e) => {
    e.stopPropagation();
    isDeleting.value = true;

    try {
        router.delete(route('notifications.destroy', props.notification.id), {
            onSuccess: () => {
                emit('notification-click');
            },
        });
        // await fetch(`/notifications/${props.notification.id}`, {
        //     method: 'DELETE',
        //     headers: {
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        //     },
        // });
    } catch (error) {
        console.error('Error deleting notification:', error);
    } finally {
        isDeleting.value = false;
    }
};
</script>

<template>
    <div
        @click="markAsRead"
        :class="[
            'px-4 py-3 cursor-pointer transition ease-in-out duration-150 hover:bg-gray-50 flex items-start justify-between group',
            isRead ? 'bg-white' : 'bg-blue-50',
        ]"
    >
        <div class="flex-1 min-w-0">
            <div class="flex items-center">
                <!-- Unread Indicator -->
                <div
                    v-if="!isRead"
                    class="flex-shrink-0 h-2 w-2 bg-blue-600 rounded-full mt-1.5 mr-2"
                />
                <div v-else class="h-2 w-2 mr-2" />

                <!-- Notification Type Badge -->
                <span
                    class="text-xs font-semibold uppercase px-2 py-1 rounded"
                    :class="{
                        'bg-green-100 text-green-800': notification.type.includes('Created'),
                        'bg-blue-100 text-blue-800': notification.type.includes('Updated'),
                        'bg-yellow-100 text-yellow-800': notification.type.includes('Approval'),
                        'bg-gray-100 text-gray-800': !notification.type.includes('Created') && !notification.type.includes('Updated') && !notification.type.includes('Approval'),
                    }"
                >
                    {{ getNotificationTitle }}
                </span>
            </div>

            <!-- Message -->
            <p
                :class="[
                    'mt-1 text-sm',
                    isRead ? 'text-gray-600' : 'text-gray-900 font-medium',
                ]"
            >
                {{ getMessage }}
            </p>

            <!-- Time -->
            <p class="mt-1 text-xs text-gray-500">
                {{ formattedTime }}
            </p>
        </div>

        <!-- Delete Button -->
        <button
            @click="deleteNotification"
            :disabled="isDeleting"
            class="flex-shrink-0 ml-2 opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-600 transition ease-in-out duration-150 disabled:opacity-50"
            title="Delete notification"
        >
            <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>
    </div>
</template>
