<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Pagination from '@/Components/Pagination.vue';
import { Link, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role || '');
const sortBy = ref(props.filters?.sort_by || 'created_at');
const sortDir = ref(props.filters?.sort_dir || 'desc');

const applyFilters = () => {
    router.get(route('users.index'), {
        search: search.value || undefined,
        role: roleFilter.value || undefined,
        sort_by: sortBy.value,
        sort_dir: sortDir.value,
    });
};

const toggleSort = (column) => {
    if (sortBy.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDir.value = 'asc';
    }
    applyFilters();
};

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('users.destroy', id));
    }
};
</script>

<template>
    <Head title="Users" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Manage Users
                </h2>
                <Link :href="route('users.create')" class="inline-flex">
                    <PrimaryButton>Add User</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <InputLabel for="search" value="Search" />
                            <TextInput
                                id="search"
                                v-model="search"
                                type="text"
                                placeholder="Search by name or email"
                                class="mt-1 block w-full"
                            />
                        </div>
                        <div>
                            <InputLabel for="role" value="Filter by Role" />
                            <select
                                id="role"
                                v-model="roleFilter"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">All Roles</option>
                                <option v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ role.name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <PrimaryButton @click="applyFilters" class="w-full">
                                Apply Filters
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="toggleSort('name')"
                                >
                                    Name
                                    <span v-if="sortBy === 'name'">
                                        {{ sortDir === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Roles
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="toggleSort('created_at')"
                                >
                                    Created
                                    <span v-if="sortBy === 'created_at'">
                                        {{ sortDir === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ user.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ user.email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span
                                        v-for="role in user.roles"
                                        :key="role.id"
                                        class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1"
                                    >
                                        {{ role.name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <Link :href="route('users.edit', user.id)" class="text-indigo-600 hover:text-indigo-900">
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteUser(user.id)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    <Pagination :links="users.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
