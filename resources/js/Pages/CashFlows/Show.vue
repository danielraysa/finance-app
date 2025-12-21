<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    cashFlow: Object
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const totalAmount = computed(() => {
    return (props.cashFlow.transactions || []).reduce((sum, t) => sum + Number(t.amount || 0), 0);
});

const attachmentPath = computed(() => {
    return props.cashFlow.attachment ? `/storage/${props.cashFlow.attachment}` : null;
});
</script>

<template>
    <Head :title="`Cash Flow: ${cashFlow.reference_number || formatDate(cashFlow.transaction_date)}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cash Flow Details</h2>
                <div class="flex space-x-2">
                    <Link :href="route('cash-flows.edit', cashFlow.id)" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                        Edit
                    </Link>
                    <Link :href="route('cash-flows.index')" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700">
                        Back to List
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-6">
                        <div class="flex justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Summary</h3>
                                <p class="text-sm text-gray-600 mt-1">Date: <span class="font-medium text-gray-900">{{ formatDate(cashFlow.transaction_date) }}</span></p>
                                <p v-if="cashFlow.reference_number" class="text-sm text-gray-600 mt-1">Reference: <span class="font-medium text-gray-900">{{ cashFlow.reference_number }}</span></p>
                                <p v-if="cashFlow.description" class="text-sm text-gray-600 mt-2">{{ cashFlow.description }}</p>
                                <p class="text-sm text-gray-600 mt-2">Total: <span class="text-xl font-bold">{{ formatCurrency(totalAmount) }}</span></p>
                            </div>

                            <div class="text-right">
                                <p class="text-sm text-gray-600">Recorded by</p>
                                <p class="font-medium text-gray-900 mt-1">{{ cashFlow.user?.name || '-' }}</p>

                                <div v-if="attachmentPath" class="mt-4">
                                    <a :href="attachmentPath" target="_blank" class="inline-flex items-center px-3 py-2 bg-gray-100 rounded-md text-sm text-gray-700 hover:bg-gray-200">
                                        View Attachment
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Transactions</h3>

                            <div v-if="(cashFlow.transactions || []).length === 0" class="text-center py-8">
                                <p class="text-gray-500">No transactions found for this cash flow</p>
                            </div>

                            <div v-else class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <!-- <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th> -->
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="tx in cashFlow.transactions" :key="tx.id">
                                            <td class="px-4 py-4 text-sm text-gray-700 capitalize">{{ tx.type }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-900">{{ tx.cash_account?.name || '-' }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-900">{{ tx.category?.name || 'Uncategorized' }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-900">{{ tx.description || '-' }}</td>
                                            <td class="px-4 py-4 text-sm text-right" :class="tx.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                                {{ formatCurrency(tx.amount) }}
                                            </td>
                                            <!-- <td class="px-4 py-4 text-sm text-right">
                                                <Link :href="route('transactions.show', tx.id)" class="text-indigo-600 hover:text-indigo-900">View</Link>
                                            </td> -->
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