<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    transaction: Object
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<template>
    <Head title="Transaction Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Transaction Details</h2>
                <div class="flex space-x-2">
                    <Link :href="route('transactions.edit', transaction.id)" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Edit Transaction
                    </Link>
                    <Link :href="route('transactions.index')" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Back to List
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Transaction Header -->
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 pb-6 border-b">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center mr-4"
                                    :style="{ backgroundColor: transaction.category?.color || (transaction.type === 'income' ? '#22C55E' : '#EF4444') }">
                                    <span class="text-white text-lg">{{ transaction.category?.icon || (transaction.category?.name ? transaction.category.name.charAt(0) : '') }}</span>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800">
                                        {{ transaction.type === 'income' ? 'Income' : 'Expense' }}
                                    </h3>
                                    <p class="text-gray-600">{{ formatDate(transaction.transaction_date) }}</p>
                                </div>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <p class="text-3xl font-bold" :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                    {{ transaction.type === 'income' ? '+' : '-' }} {{ formatCurrency(transaction.amount) }}
                                </p>
                            </div>
                        </div>

                        <!-- Transaction Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Transaction Information</h4>
                                
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Category:</span>
                                        <span class="text-gray-900 font-medium">{{ transaction.category?.name || 'Uncategorized' }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Account:</span>
                                        <span class="text-gray-900 font-medium">{{ transaction.cash_account?.name || 'Unknown Account' }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Type:</span>
                                        <span class="text-gray-900 font-medium capitalize">{{ transaction.type }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Amount:</span>
                                        <span class="text-gray-900 font-medium">{{ formatCurrency(transaction.amount) }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Date:</span>
                                        <span class="text-gray-900 font-medium">{{ formatDate(transaction.transaction_date) }}</span>
                                    </div>
                                    
                                    <div v-if="transaction.reference_number" class="flex justify-between">
                                        <span class="text-gray-600">Reference Number:</span>
                                        <span class="text-gray-900 font-medium">{{ transaction.reference_number }}</span>
                                    </div>
                                    
                                    <div v-if="transaction.created_at" class="flex justify-between">
                                        <span class="text-gray-600">Created:</span>
                                        <span class="text-gray-900 font-medium">{{ formatDate(transaction.created_at) }}</span>
                                    </div>
                                    
                                    <div v-if="transaction.updated_at && transaction.updated_at !== transaction.created_at" class="flex justify-between">
                                        <span class="text-gray-600">Last Updated:</span>
                                        <span class="text-gray-900 font-medium">{{ formatDate(transaction.updated_at) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Additional Details</h4>
                                
                                <div v-if="transaction.description" class="mb-6">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Description</h5>
                                    <p class="text-gray-900 bg-gray-50 p-3 rounded-md">{{ transaction.description }}</p>
                                </div>
                                
                                <div v-if="transaction.attachment_path" class="mb-6">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Attachment</h5>
                                    <div class="bg-gray-50 p-3 rounded-md">
                                        <a :href="transaction.attachment_path" target="_blank" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            {{ transaction.attachment_path.split('/').pop() }}
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="mt-6 flex space-x-3">
                                    <Link :href="route('transactions.edit', transaction.id)" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </Link>
                                    <Link :href="route('cash-accounts.show', transaction.cash_account_id)" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 text-sm rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        View Account
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
