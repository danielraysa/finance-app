<script setup>
import { computed } from 'vue';
import NotificationItem from '@/Components/NotificationItem.vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    notifications: {
        type: Array,
        required: true,
    },
    unreadCount: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['close', 'notification-click']);

const hasNotifications = computed(() => {
    return Array.isArray(props.notifications) && props.notifications.length > 0;
});

const displayedNotifications = computed(() => {
    return props.notifications.slice(0, 5);
});

const markAllAsRead = async () => {
    try {
        // await fetch('/notifications/mark-all-as-read', {
        //     method: 'POST',
        //     headers: {
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        //     },
        // });
        router.post(route('notifications.mark-all-as-read'), {
            onSuccess: () => {
                emit('notification-click');
            },
        });
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
};
</script>

<template>
    <div class="py-4">
        <!-- Header -->
        <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
            <button
                v-if="unreadCount > 0"
                @click="markAllAsRead"
                class="text-sm text-blue-600 hover:text-blue-700 font-medium"
            >
                Mark all as read
            </button>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            <div v-if="hasNotifications" class="divide-y divide-gray-100">
                <NotificationItem
                    v-for="notification in displayedNotifications"
                    :key="notification.id"
                    :notification="notification"
                    @close="$emit('close')"
                    @notification-click="$emit('notification-click')"
                />
            </div>

            <!-- Empty State -->
            <div v-else class="px-4 py-8 text-center">
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                    />
                </svg>
                <p class="mt-2 text-sm font-medium text-gray-900">No notifications</p>
                <p class="mt-1 text-sm text-gray-500">You're all caught up!</p>
            </div>

            <!-- View All Notifications Link -->
            <div v-if="hasNotifications" class="border-t border-gray-100 px-4 py-3 text-center bg-gray-50">
                <Link
                    :href="route('notifications.index')"
                    class="text-sm font-medium text-blue-600 hover:text-blue-700 transition ease-in-out duration-150"
                    @click="$emit('close')"
                >
                View all
                </Link>
            </div>
        </div>
    </div>
</template>
