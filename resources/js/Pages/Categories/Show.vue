<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    category: Object,
    transactions: Array,
    stats: Object
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head :title="'Category: ' + category.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Category Details</h2>
                <div class="flex space-x-2">
                    <Link :href="route('categories.edit', category.id)" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Edit Category
                    </Link>
                    <Link :href="route('categories.index')" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Back to List
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Category Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-start">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center mr-4"
                                :style="{ backgroundColor: category.color || (category.type === 'income' ? '#22C55E' : '#EF4444') }">
                                <span class="text-white text-xl">{{ category.icon || category.name.charAt(0) }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex flex-col md:flex-row md:justify-between md:items-start">
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-800">{{ category.name }}</h3>
                                        <div class="flex items-center mt-1">
                                            <span class="px-2 py-1 text-xs rounded-full capitalize" 
                                                :class="category.type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                                {{ category.type }}
                                            </span>
                                            <span class="ml-2 text-gray-600">Status: <span :class="category.is_active ? 'text-green-600' : 'text-red-600'">{{ category.is_active ? 'Active' : 'Inactive' }}</span></span>
                                        </div>
                                        <p v-if="category.description" class="text-gray-600 mt-2">{{ category.description }}</p>
                                    </div>
                                    <div class="mt-4 md:mt-0 md:text-right">
                                        <p class="text-sm text-gray-600">Total {{ category.type === 'income' ? 'Income' : 'Expenses' }}</p>
                                        <p class="text-2xl font-bold" :class="category.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                            {{ formatCurrency(stats.total || 0) }}
                                        </p>
                                        <p class="text-sm text-gray-600 mt-1">{{ stats.count || 0 }} transactions</p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Category Details</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-500">Color</p>
                                            <div class="flex items-center mt-1">
                                                <div class="w-4 h-4 rounded-full mr-2" :style="{ backgroundColor: category.color }"></div>
                                                <span class="text-sm">{{ category.color }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Created</p>
                                            <p class="text-sm">{{ formatDate(category.created_at) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Last Updated</p>
                                            <p class="text-sm">{{ formatDate(category.updated_at) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transactions List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Transactions in this Category</h3>
                            <Link :href="route('transactions.create', { transaction_category_id: category.id, type: category.type })" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                Add Transaction
                            </Link>
                        </div>
                        
                        <div v-if="transactions.length === 0" class="text-center py-8">
                            <p class="text-gray-500">No transactions found in this category</p>
                            <Link :href="route('transactions.create', { transaction_category_id: category.id, type: category.type })" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                                Record your first transaction
                            </Link>
                        </div>
                        
                        <div v-else>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="transaction in transactions" :key="transaction.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(transaction.transaction_date) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ transaction.cash_account?.name || 'Unknown Account' }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900 truncate max-w-xs">{{ transaction.description || '-' }}</div>
                                                <div v-if="transaction.reference_number" class="text-xs text-gray-500">Ref: {{ transaction.reference_number }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <div class="text-sm font-medium" :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                                    {{ formatCurrency(transaction.amount) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <Link :href="route('transactions.show', transaction.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">View</Link>
                                                <Link :href="route('transactions.edit', transaction.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
