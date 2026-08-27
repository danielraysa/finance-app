<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    cashFlows: Object,
    filters: Object
});

const search = ref(props.filters?.search || '');
const typeFilter = ref(props.filters?.type || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const sortBy = ref(props.filters?.sort_by || 'transaction_date');
const sortDir = ref(props.filters?.sort_dir || 'desc');

let searchTimeout = null;

const applyFilters = () => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (typeFilter.value) params.append('type', typeFilter.value);
    if (dateFrom.value) params.append('date_from', dateFrom.value);
    if (dateTo.value) params.append('date_to', dateTo.value);
    if (sortBy.value) params.append('sort_by', sortBy.value);
    if (sortDir.value) params.append('sort_dir', sortDir.value);

    const url = route('cash-flows.index') + (params.toString() ? ('?' + params.toString()) : '');
    window.location.href = url;
};

const debouncedSubmit = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
};

const resetFilters = () => {
    search.value = '';
    typeFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    sortBy.value = 'transaction_date';
    sortDir.value = 'desc';
    window.location.href = route('cash-flows.index');
};

const toggleSortDir = () => {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    applyFilters();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Cash Flows" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cash Flows</h2>
                <Link :href="route('cash-flows.create')" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">Add Cash Flow</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="(cashFlows.data || []).length === 0" class="text-center py-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Cash Flows Found</h3>
                            <p class="text-gray-500 mb-4">You haven't recorded any cash flows yet.</p>
                            <Link :href="route('cash-flows.create')" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">Record Your First Cash Flow</Link>
                        </div>

                        <div v-else>
                            <!-- Filters Section -->
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
                                    <!-- Search Input -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                        <input v-model="search" @input="debouncedSubmit" type="text" placeholder="Reference, description..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                    </div>

                                    <!-- Transaction Type Filter -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                        <select v-model="typeFilter" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <option value="">All Types</option>
                                            <option value="income">Income</option>
                                            <option value="expense">Expense</option>
                                        </select>
                                    </div>

                                    <!-- Date From -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                                        <input v-model="dateFrom" @change="applyFilters" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                    </div>

                                    <!-- Date To -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                                        <input v-model="dateTo" @change="applyFilters" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                    </div>

                                    <!-- Sort By -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                        <select v-model="sortBy" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <option value="created_at">Created Date</option>
                                            <option value="transaction_date">Transaction Date</option>
                                            <option value="reference_number">Reference Number</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex justify-between items-center">
                                    <div class="flex space-x-2">
                                        <button @click="toggleSortDir" class="px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-100">
                                            Sort {{ sortDir === 'asc' ? '↑' : '↓' }}
                                        </button>
                                        <button @click="resetFilters" class="px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-100">
                                            Reset Filters
                                        </button>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Showing <strong>{{ cashFlows.from }}</strong> to <strong>{{ cashFlows.to }}</strong> of <strong>{{ cashFlows.total }}</strong> results
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="cashFlow in cashFlows.data" :key="cashFlow.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(cashFlow.transaction_date) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ cashFlow.reference_number || '-' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900">{{ cashFlow.description || '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatCurrency(cashFlow.transactions_sum_amount || 0) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <Link :href="route('cash-flows.show', cashFlow.id)">
                                                    <PrimaryButton>View</PrimaryButton>
                                                </Link>
                                                <Link :href="route('cash-flows.edit', cashFlow.id)">
                                                    <SecondaryButton class="mx-2">Edit</SecondaryButton>
                                                </Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-6" v-if="cashFlows.links.length > 3">
                                <div class="flex justify-center">
                                    <div class="flex space-x-1">
                                        <Link v-for="(link, i) in cashFlows.links" :key="i" v-html="link.label" :href="link.url" :class="[
                                            'px-4 py-2 text-sm rounded-md',
                                            {
                                                'bg-indigo-600 text-white': link.active,
                                                'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300': !link.active && link.url,
                                                'bg-gray-100 text-gray-500 cursor-not-allowed border border-gray-300': !link.url
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
