<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    cashAccount: Object,
    transactions: Object, // Changed from Array to Object to support pagination
    balance: Number
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
    <Head :title="'Account: ' + cashAccount.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Account Details</h2>
                <div class="flex space-x-2">
                    <Link :href="route('cash-accounts.edit', cashAccount.id)" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Edit Account
                    </Link>
                    <Link :href="route('cash-accounts.index')" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Back to List
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Account Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-800">{{ cashAccount.name }}</h3>
                                <p v-if="cashAccount.account_number" class="text-gray-600 mt-1">Account #: {{ cashAccount.account_number }}</p>
                                <p class="text-gray-600 mt-1">Status: <span :class="cashAccount.is_active ? 'text-green-600' : 'text-red-600'">{{ cashAccount.is_active ? 'Active' : 'Inactive' }}</span></p>
                                <p v-if="cashAccount.description" class="text-gray-600 mt-2">{{ cashAccount.description }}</p>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">Current Balance</p>
                                    <p class="text-3xl font-bold text-indigo-600">{{ formatCurrency(balance) }}</p>
                                    <p class="text-sm text-gray-600 mt-1">Initial Balance: {{ formatCurrency(cashAccount.initial_balance) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transactions List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
                            <Link :href="route('transactions.create', { cash_account_id: cashAccount.id })" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                Add Transaction
                            </Link>
                        </div>
                        
                        <div v-if="transactions.data.length === 0" class="text-center py-8">
                            <p class="text-gray-500">No transactions found for this account</p>
                            <Link :href="route('transactions.create', { cash_account_id: cashAccount.id })" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                                Record your first transaction
                            </Link>
                        </div>
                        
                        <div v-else>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="transaction in transactions.data" :key="transaction.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(transaction.transaction_date) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-6 h-6 rounded-full flex items-center justify-center mr-2"
                                                        :style="{ backgroundColor: transaction.category?.color || (transaction.type === 'income' ? '#22C55E' : '#EF4444') }">
                                                        <span class="text-white text-xs">{{ transaction.category?.icon || (transaction.category?.name ? transaction.category.name.charAt(0) : '') }}</span>
                                                    </div>
                                                    <div class="text-sm font-medium text-gray-900">{{ transaction.category?.name || 'Uncategorized' }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900 truncate max-w-xs">{{ transaction.description || '-' }}</div>
                                                <div v-if="transaction.reference_number" class="text-xs text-gray-500">Ref: {{ transaction.reference_number }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <div class="text-sm font-medium" :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                                    {{ transaction.type === 'income' ? '+' : '-' }} {{ formatCurrency(transaction.amount) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <Link :href="route('transactions.show', transaction.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">View</Link>
                                                <Link :href="route('transactions.edit', transaction.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <!-- Pagination Links -->
                                <div class="mt-6">
                                    <Pagination :links="transactions.links" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
