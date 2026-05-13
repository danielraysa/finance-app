<script setup>
import { computed } from 'vue';
import { useNotifications } from '@/Composables/useNotifications';
import { usePage } from '@inertiajs/vue3';

const { notifications, listenForNotifications, addNotification, removeNotification } = useNotifications();
const page = usePage();

// Start listening when component mounts
if (page.props.auth?.user) {
    console.log('Listening for notifications for user ID:', page.props.auth.user.id);
    listenForNotifications(page.props.auth.user.id);
}

const getNotificationClass = (type) => {
    const classes = {
        success: 'bg-green-100 text-green-800 border-l-4 border-green-500',
        error: 'bg-red-100 text-red-800 border-l-4 border-red-500',
        info: 'bg-blue-100 text-blue-800 border-l-4 border-blue-500',
        warning: 'bg-yellow-100 text-yellow-800 border-l-4 border-yellow-500',
    };
    return classes[type] || classes.info;
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: all 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateX(30px);
}
</style>
<template>
    <div class="fixed top-4 right-4 space-y-2 z-50">
        <TransitionGroup name="fade">
            <div
                v-for="notification in notifications"
                :key="notification.id"
                :class="getNotificationClass(notification.type || notification.status)"
                class="px-4 py-3 rounded-lg shadow-lg flex items-center justify-between"
            >
                <div>
                    <h3 class="font-semibold">{{ notification.title }}</h3>
                    <p class="text-sm">{{ notification.message }}</p>
                </div>
                <button
                    @click="removeNotification(notification.id)"
                    class="ml-4 text-lg"
                >×</button>
            </div>
        </TransitionGroup>
    </div>
</template>
