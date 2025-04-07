
<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import debounce from 'lodash.debounce';

const props = defineProps({
    budgets: Object,
    filters: Object,
});

const confirmingDeletion = ref(false);
const processing = ref(false);
const budgetToDelete = ref(null);

// Create reactive references to the filters
const filters = ref({
    search: props.filters.search || '',
    period_type: props.filters.period_type || '',
    is_active: props.filters.is_active === undefined ? null : props.filters.is_active,
});

// Format date to a more readable format
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString();
};

// Format currency
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
    }).format(amount);
};

// Debounced search function
const debouncedSearch = debounce(() => {
    filterBudgets();
}, 300);

// Filter budgets
const filterBudgets = () => {
    router.get(
        route('budgets.index'),
        {
            search: filters.value.search,
            period_type: filters.value.period_type,
            is_active: filters.value.is_active,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

// Reset filters
const resetFilters = () => {
    filters.value = {
        search: '',
        period_type: '',
        is_active: null,
    };
    filterBudgets();
};

// Confirm budget deletion
const confirmDelete = (budget) => {
    budgetToDelete.value = budget;
    confirmingDeletion.value = true;
};

// Close modal
const closeModal = () => {
    confirmingDeletion.value = false;
    budgetToDelete.value = null;
};

// Delete budget
const deleteBudget = () => {
    if (!budgetToDelete.value) return;

    processing.value = true;
    
    router.delete(route('budgets.destroy', budgetToDelete.value.id), {
        onSuccess: () => {
            closeModal();
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};

// Watch for changes in props.filters and update local filters
watch(
    () => props.filters,
    (newFilters) => {
        filters.value = {
            search: newFilters.search || '',
            period_type: newFilters.period_type || '',
            is_active: newFilters.is_active === undefined ? null : newFilters.is_active,
        };
    },
    { deep: true }
);
</script>

<template>
    <Head title="Budgets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Budgets</h2>
                <Link :href="route('budgets.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                    Create Budget
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Filters -->
                        <div class="mb-6 flex flex-wrap items-center gap-4">
                            <div class="flex-1">
                                <input
                                    type="text"
                                    v-model="filters.search"
                                    placeholder="Search budgets..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    @input="debouncedSearch"
                                />
                            </div>
                            <div>
                                <select
                                    v-model="filters.period_type"
                                    class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    @change="filterBudgets"
                                >
                                    <option value="">All Periods</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                            <div>
                                <select
                                    v-model="filters.is_active"
                                    class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    @change="filterBudgets"
                                >
                                    <option :value="null">All Status</option>
                                    <option :value="1">Active</option>
                                    <option :value="0">Inactive</option>
                                </select>
                            </div>
                            <button
                                @click="resetFilters"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
                            >
                                Reset
                            </button>
                        </div>

                        <!-- Budgets Table -->
                        <div v-if="budgets.data.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Period
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date Range
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Amount
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="budget in budgets.data" :key="budget.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ budget.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 capitalize">{{ budget.period_type }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ formatCurrency(budget.total_amount) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="[
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    budget.is_active
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-red-100 text-red-800',
                                                ]"
                                            >
                                                {{ budget.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <Link
                                                    :href="route('budgets.show', budget.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    View
                                                </Link>
                                                <Link
                                                    :href="route('budgets.edit', budget.id)"
                                                    class="text-amber-600 hover:text-amber-900"
                                                >
                                                    Edit
                                                </Link>
                                                <Link
                                                    :href="route('budget-reports.show', budget.id)"
                                                    class="text-green-600 hover:text-green-900"
                                                >
                                                    Report
                                                </Link>
                                                <button
                                                    @click.prevent="confirmDelete(budget)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <svg
                                class="mx-auto h-12 w-12 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No budgets found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new budget.</p>
                            <div class="mt-6">
                                <Link
                                    :href="route('budgets.create')"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <svg
                                        class="-ml-1 mr-2 h-5 w-5"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    Create Budget
                                </Link>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="budgets.data.length > 0" class="mt-6">
                            <Pagination :links="budgets.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmingDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete this budget?
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    This action will permanently delete this budget and all its budget items.
                </p>
                <div class="mt-6 flex justify-end space-x-4">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <DangerButton @click="deleteBudget" :class="{ 'opacity-25': processing }" :disabled="processing">
                        Delete Budget
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
