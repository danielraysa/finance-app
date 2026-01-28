<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    role: Object,
});
</script>

<template>
    <Head :title="`Role - ${role.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ role.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Role Information</h3>
                        <dl class="space-y-2">
                            <div class="flex">
                                <dt class="font-medium text-gray-600 w-32">Name:</dt>
                                <dd class="text-gray-900">{{ role.name }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="font-medium text-gray-600 w-32">Created:</dt>
                                <dd class="text-gray-900">{{ new Date(role.created_at).toLocaleDateString() }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Permissions ({{ role.permissions.length }})</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <span
                                v-for="permission in role.permissions"
                                :key="permission.id"
                                class="inline-block bg-gray-100 text-gray-800 text-sm px-3 py-1 rounded"
                            >
                                {{ permission.name }}
                            </span>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <Link :href="route('roles.edit', role.id)" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Edit
                        </Link>
                        <Link :href="route('roles.index')" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Back
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
