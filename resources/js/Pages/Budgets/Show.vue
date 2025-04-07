
<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    budget: Object,
    totalPlanned: Number,
    totalActual: Number,
});

// Calculate remaining amount
const remainingAmount = computed(() => {
    return props.totalPlanned - props.totalActual;
});

// Calculate percentage used
const percentageUsed = computed(() => {
    if (props.totalPlanned <= 0) {
        return 0;
    }
    return Math.min(100, Math.round((props.totalActual / props.totalPlanned) * 100));
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

// Get progress bar color based on percentage
const getProgressBarColor = (percentage) => {
    if (percentage < 70) {
        return 'bg-green-600';
    } else if (percentage < 90) {
        return 'bg-yellow-500';
    } else if (percentage <= 100) {
        return 'bg-orange-500';
    } else {
        return 'bg-red-600';
    }
};
</script>
<template>
    <Head :title="budget.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Budget Details</h2>
                <div class="flex space-x-2">
                    <Link :href="route('budget-reports.show', budget.id)" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                        View Report
                    </Link>
                    <Link :href="route('budgets.edit', budget.id)" class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 transition">
                        Edit Budget
                    </Link>
                    <Link :href="route('budgets.index')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                        Back to Budgets
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Budget Overview Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">{{ budget.name }}</h3>
                                <p class="text-gray-600 mt-1">
                                    {{ formatDate(budget.start_date) }} to {{ formatDate(budget.end_date) }}
                                    <span class="ml-2 px-2 py-1 text-xs rounded-full" :class="budget.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        {{ budget.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                                <p v-if="budget.description" class="text-gray-600 mt-2">{{ budget.description }}</p>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <div class="text-right">
                                    <div class="text-sm text-gray-500">Budget Period</div>
                                    <div class="text-lg font-semibold capitalize">{{ budget.period_type }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Planned Amount -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-500">Planned Amount</div>
                                <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(totalPlanned) }}</div>
                            </div>

                            <!-- Actual Amount -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-500">Actual Amount</div>
                                <div class="text-2xl font-bold" :class="totalActual <= totalPlanned ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(totalActual) }}
                                </div>
                            </div>

                            <!-- Remaining Amount -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-500">Remaining</div>
                                <div class="text-2xl font-bold" :class="remainingAmount >= 0 ? 'text-blue-600' : 'text-red-600'">
                                    {{ formatCurrency(remainingAmount) }}
                                </div>
                                <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full" :style="{ width: `${percentageUsed}%` }" :class="percentageUsed <= 100 ? 'bg-blue-600' : 'bg-red-600'"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">{{ percentageUsed }}% used</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Budget Items Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Budget Items</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Category
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Planned Amount
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actual Amount
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Remaining
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Progress
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in budget.budget_items" :key="item.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-4 w-4 rounded-full mr-2" :style="{ backgroundColor: item.category.color || '#CBD5E0' }"></div>
                                                <div class="text-sm font-medium text-gray-900">{{ item.category.name }}</div>
                                                <div class="ml-1 text-xs text-gray-500 capitalize">({{ item.category.type }})</div>
                                            </div>
                                            <div v-if="item.notes" class="text-xs text-gray-500 mt-1">{{ item.notes }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ formatCurrency(item.planned_amount) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm" :class="item.actual_amount <= item.planned_amount ? 'text-green-600' : 'text-red-600'">
                                                {{ formatCurrency(item.actual_amount) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm" :class="item.remaining_amount >= 0 ? 'text-blue-600' : 'text-red-600'">
                                                {{ formatCurrency(item.remaining_amount) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="h-2.5 rounded-full" :style="{ width: `${item.percentage_used}%` }" :class="getProgressBarColor(item.percentage_used)"></div>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">{{ item.percentage_used }}% used</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
