<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import NotificationItem from '@/Components/NotificationItem.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    notifications: {
        type: Object,
        required: true,
    },
    unreadCount: {
        type: Number,
        required: true,
    },
});

const filterType = ref('all');

const filteredNotifications = computed(() => {
    if (filterType.value === 'unread') {
        return props.notifications.data.filter(n => n.read_at === null);
    }
    return props.notifications.data;
});

const markAllAsRead = async () => {
    try {
        const response = await fetch(route('notifications.mark-all-as-read'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
        });

        if (response.ok) {
            window.location.reload();
        }
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
};

const deleteAllNotifications = async () => {
    if (!confirm('Are you sure you want to delete all notifications?')) {
        return;
    }

    try {
        const response = await fetch(route('notifications.destroy-all'), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
        });

        if (response.ok) {
            window.location.reload();
        }
    } catch (error) {
        console.error('Error deleting notifications:', error);
    }
};
</script>

<template>
    <Head title="Notifications" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notifications</h2>
                <div class="flex gap-2">
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition"
                    >
                        Mark all as read
                    </button>
                    <button
                        v-if="notifications.total > 0"
                        @click="deleteAllNotifications"
                        class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition"
                    >
                        Clear all
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Filter Tabs -->
                    <div class="border-b border-gray-200">
                        <div class="flex">
                            <button
                                @click="filterType = 'all'"
                                :class="[
                                    'px-4 py-4 text-sm font-medium border-b-2 transition ease-in-out duration-150',
                                    filterType === 'all'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                ]"
                            >
                                All
                                <span v-if="notifications.total > 0" class="ml-2 px-2 py-1 text-xs bg-gray-100 rounded-full">
                                    {{ notifications.total }}
                                </span>
                            </button>
                            <button
                                @click="filterType = 'unread'"
                                :class="[
                                    'px-4 py-4 text-sm font-medium border-b-2 transition ease-in-out duration-150',
                                    filterType === 'unread'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                ]"
                            >
                                Unread
                                <span v-if="unreadCount > 0" class="ml-2 px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">
                                    {{ unreadCount }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Notifications List -->
                    <div class="divide-y divide-gray-100">
                        <div v-if="filteredNotifications.length === 0" class="px-6 py-12 text-center">
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
                            <p class="mt-2 text-lg font-medium text-gray-900">
                                {{ filterType === 'unread' ? 'No unread notifications' : 'No notifications' }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ filterType === 'unread' ? "You're all caught up!" : 'You have no notifications yet.' }}
                            </p>
                        </div>

                        <NotificationItem
                            v-for="notification in filteredNotifications"
                            :key="notification.id"
                            :notification="notification"
                            @notification-click="() => window.location.reload()"
                        />
                    </div>

                    <div v-if="filterType === 'all'" class="border-t border-gray-100 px-6 py-4">
                        <Pagination :links="notifications.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
