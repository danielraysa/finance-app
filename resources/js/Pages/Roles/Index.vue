<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Pagination from '@/Components/Pagination.vue';
import { Link, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    roles: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort_by || 'created_at');
const sortDir = ref(props.filters?.sort_dir || 'desc');

const applyFilters = () => {
    router.get(route('roles.index'), {
        search: search.value || undefined,
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

const deleteRole = (id) => {
    if (confirm('Are you sure you want to delete this role?')) {
        router.delete(route('roles.destroy', id));
    }
};

const canDeleteRole = (role) => {
    return !['admin', 'user', 'verificator'].includes(role.name);
};
</script>

<template>
    <Head title="Roles" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Manage Roles
                </h2>
                <Link :href="route('roles.create')" class="inline-flex">
                    <PrimaryButton>Add Role</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="search" value="Search" />
                            <TextInput
                                id="search"
                                v-model="search"
                                type="text"
                                placeholder="Search roles..."
                                class="mt-1 block w-full"
                            />
                        </div>
                        <div class="flex items-end gap-2">
                            <PrimaryButton @click="applyFilters" class="w-full">
                                Apply Filters
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white overflow-x-scroll shadow-sm sm:rounded-lg">
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
                                    Permissions
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
                            <tr v-for="role in roles.data" :key="role.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ role.name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="permission in role.permissions.slice(0, 3)"
                                            :key="permission.id"
                                            class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded"
                                        >
                                            {{ permission.name }}
                                        </span>
                                        <span
                                            v-if="role.permissions.length > 3"
                                            class="inline-block text-xs text-gray-600"
                                        >
                                            +{{ role.permissions.length - 3 }} more
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(role.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <Link :href="route('roles.edit', role.id)">
                                        <button class="transition ease-in-out duration-150 px-4 py-2 text-sm text-white rounded-md bg-indigo-600 hover:bg-indigo-700">
                                            View
                                        </button>
                                    </Link>
                                    <button
                                        v-if="canDeleteRole(role)"
                                        @click="deleteRole(role.id)"
                                        class="transition ease-in-out duration-150 px-4 py-2 text-sm text-white rounded-md bg-red-600 hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                    <button v-else class="transition ease-in-out duration-150 px-4 py-2 text-sm rounded-md bg-red-300 hover:bg-red-400 text-gray-400">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    <Pagination :links="roles.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
