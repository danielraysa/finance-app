<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    eventProjects: Object
});

const search = ref(props.filters?.search || '');
let searchTimeout = null;
const submitSearch = () => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    const url = route('event-projects.index') + (params.toString() ? ('?' + params.toString()) : '');
    window.location.href = url;
};

const debouncedSubmit = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(submitSearch, 400);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};
</script>

<template>
    <Head title="Event Projects" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Event Projects</h2>
                <Link :href="route('event-projects.create')" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">Add Event Project</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="(eventProjects.data || []).length === 0" class="text-center py-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Event Projects Found</h3>
                            <p class="text-gray-500 mb-4">You haven't created any event projects yet.</p>
                            <Link :href="route('event-projects.create')" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">Create Your First Event Project</Link>
                        </div>

                        <div v-else>
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <input v-model="search" @input="debouncedSubmit" type="text" placeholder="Search by name, location or note" class="px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                </div>
                                <div class="text-sm text-gray-500">Results: {{ (eventProjects.data || []).length }}</div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allocated</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="eventProject in eventProjects.data" :key="eventProject.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(eventProject.event_date) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ eventProject.event_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ eventProject.location || '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ eventProject.status }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatCurrency(eventProject.details_sum_allocated_amount || 0) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <Link :href="route('event-projects.show', eventProject.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">View</Link>
                                                <Link :href="route('event-projects.edit', eventProject.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-6" v-if="!search && eventProjects.links.length > 3">
                                <div class="flex justify-between items-center">
                                    <div class="text-sm text-gray-700">Showing {{ eventProjects.from }} to {{ eventProjects.to }} of {{ eventProjects.total }} event projects</div>
                                    <div class="flex space-x-1">
                                        <Link v-for="(link, i) in eventProjects.links" :key="i" v-html="link.label" :href="link.url" :class="[
                                            'px-4 py-2 text-sm rounded-md',
                                            {
                                                'bg-indigo-600 text-white': link.active,
                                                'bg-white text-gray-700 hover:bg-gray-50': !link.active && link.url,
                                                'bg-gray-100 text-gray-500 cursor-not-allowed': !link.url
                                            }
                                        ]" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
