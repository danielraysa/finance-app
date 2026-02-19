<script setup>
import { computed, ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import NotificationList from '@/Components/NotificationList.vue';

const showNotifications = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const page = usePage();

onMounted(() => {
    fetchNotifications();
    // Poll for new notifications every 60 seconds
    setInterval(fetchNotifications, 60000);
});

const fetchNotifications = async () => {
    try {
        const response = await fetch(route('notifications.list'));
        const data = await response.json();
        notifications.value = data.notifications;
        unreadCount.value = data.unreadCount;
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
};

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
};

const closeNotifications = () => {
    showNotifications.value = false;
};

const handleNotificationClick = () => {
    fetchNotifications();
};
</script>

<template>
    <div class="relative">
        <button
            @click="toggleNotifications"
            class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
            :title="unreadCount > 0 ? `${unreadCount} unread notifications` : 'Notifications'"
        >
            <!-- Bell Icon -->
            <svg
                class="w-6 h-6"
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

            <!-- Unread Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Notification Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="showNotifications"
                class="absolute right-0 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
            >
                <NotificationList
                    :notifications="notifications"
                    :unread-count="unreadCount"
                    @close="closeNotifications"
                    @notification-click="handleNotificationClick"
                />
            </div>
        </Transition>

        <!-- Click outside to close -->
        <div
            v-if="showNotifications"
            @click="closeNotifications"
            class="fixed inset-0 z-40"
        />
    </div>
</template>
