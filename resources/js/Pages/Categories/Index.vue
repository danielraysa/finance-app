<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array
});
</script>

<template>
    <Head title="Transaction Categories" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Transaction Categories</h2>
                <Link :href="route('categories.create')" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Add New Category
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="categories.length === 0" class="text-center py-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Categories Found</h3>
                            <p class="text-gray-500 mb-4">You haven't created any transaction categories yet.</p>
                            <Link :href="route('categories.create')" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Create Your First Category
                            </Link>
                        </div>
                        
                        <div v-else>
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Income Categories</h3>
                                <div v-if="categories.filter(c => c.type === 'income').length === 0" class="text-center py-4 bg-gray-50 rounded-lg">
                                    <p class="text-gray-500">No income categories found</p>
                                </div>
                                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div v-for="category in categories.filter(c => c.type === 'income')" :key="category.id" 
                                        class="border rounded-lg p-4 flex justify-between items-center"
                                        :class="{'opacity-50': !category.is_active}">
                                        <div>
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                                    :style="{ backgroundColor: category.color || '#4F46E5' }">
                                                    <span class="text-white text-sm">{{ category.icon || category.name.charAt(0) }}</span>
                                                </div>
                                                <div>
                                                    <h4 class="font-medium">{{ category.name }}</h4>
                                                    <p v-if="category.description" class="text-xs text-gray-500 truncate max-w-xs">{{ category.description }}</p>
                                                    <p v-if="!category.is_active" class="text-xs text-red-500">Inactive</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <Link :href="route('categories.edit', category.id)" class="text-indigo-600 hover:text-indigo-900">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Expense Categories</h3>
                                <div v-if="categories.filter(c => c.type === 'expense').length === 0" class="text-center py-4 bg-gray-50 rounded-lg">
                                    <p class="text-gray-500">No expense categories found</p>
                                </div>
                                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div v-for="category in categories.filter(c => c.type === 'expense')" :key="category.id" 
                                        class="border rounded-lg p-4 flex justify-between items-center"
                                        :class="{'opacity-50': !category.is_active}">
                                        <div>
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                                    :style="{ backgroundColor: category.color || '#EF4444' }">
                                                    <span class="text-white text-sm">{{ category.icon || category.name.charAt(0) }}</span>
                                                </div>
                                                <div>
                                                    <h4 class="font-medium">{{ category.name }}</h4>
                                                    <p v-if="category.description" class="text-xs text-gray-500 truncate max-w-xs">{{ category.description }}</p>
                                                    <p v-if="!category.is_active" class="text-xs text-red-500">Inactive</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <Link :href="route('categories.edit', category.id)" class="text-indigo-600 hover:text-indigo-900">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </Link>
                                        </div>
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
