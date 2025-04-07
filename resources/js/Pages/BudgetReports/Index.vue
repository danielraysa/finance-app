
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    budgetOverview: Object,
    budgetComparison: Array,
    categoryDistribution: Array,
    activeBudgets: Array,
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

// Calculate percentage
const calculatePercentage = (actual, planned) => {
    if (planned <= 0) {
        return 0;
    }
    return Math.min(100, Math.round((actual / planned) * 100));
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
    <Head title="Budget Reports" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Budget Reports</h2>
                <Link :href="route('budgets.index')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                    Manage Budgets
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Budget Overview Card -->
                <div v-if="budgetOverview" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Current Budget Overview</h3>
                            <Link :href="route('budget-reports.show', budgetOverview.id)" class="text-indigo-600 hover:text-indigo-900">
                                View Detailed Report
                            </Link>
                        </div>

                        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
                            <div>
                                <h4 class="text-xl font-bold text-gray-900">{{ budgetOverview.name }}</h4>
                                <p class="text-gray-600 mt-1">
                                    {{ formatDate(budgetOverview.start_date) }} to {{ formatDate(budgetOverview.end_date) }}
                                </p>
                            </div>
                            <div class="mt-4 md:mt-0 text-right">
                                <div class="text-sm text-gray-500">Budget Period</div>
                                <div class="text-lg font-semibold capitalize">{{ budgetOverview.period_type }}</div>
                                <div class="text-sm text-gray-500 mt-2">Days Remaining</div>
                                <div class="text-lg font-semibold">{{ budgetOverview.days_remaining }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Planned Amount -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-500">Planned Amount</div>
                                <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(budgetOverview.total_planned) }}</div>
                            </div>

                            <!-- Actual Amount -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-500">Actual Amount</div>
                                <div class="text-2xl font-bold" :class="budgetOverview.total_actual <= budgetOverview.total_planned ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(budgetOverview.total_actual) }}
                                </div>
                            </div>

                            <!-- Budget Progress -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-500">Budget Progress</div>
                                <div class="text-2xl font-bold text-blue-600">
                                    {{ budgetOverview.progress_percentage }}%
                                </div>
                                <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full bg-blue-600" :style="{ width: `${budgetOverview.progress_percentage}%` }"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Time elapsed</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Active Budget Message -->
                <div v-if="!budgetOverview" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No active budget found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new budget.</p>
                            <div class="mt-6">
                                <Link :href="route('budgets.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                    Create Budget
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Distribution -->
                <div v-if="categoryDistribution && categoryDistribution.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Category Distribution</h3>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Category Chart -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="aspect-w-1 aspect-h-1">
                                    <!-- This is a placeholder for a chart component -->
                                    <div class="w-full h-full flex items-center justify-center">
                                        <div class="text-center">
                                            <div class="text-gray-500">Category Distribution Chart</div>
                                            <div class="mt-2 text-sm text-gray-400">Implement with Chart.js or similar library</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Category Table -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Category
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Planned
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actual
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                % Used
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in categoryDistribution" :key="item.category_name">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-3 w-3 rounded-full mr-2" :style="{ backgroundColor: item.category_color }"></div>
                                                    <div class="text-sm font-medium text-gray-900">{{ item.category_name }}</div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatCurrency(item.planned_amount) }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatCurrency(item.actual_amount) }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="mr-2 w-16 bg-gray-200 rounded-full h-2">
                                                        <div class="h-2 rounded-full" :style="{ width: `${item.percentage}%`, backgroundColor: item.category_color }"></div>
                                                    </div>
                                                    <span class="text-sm text-gray-500">{{ item.percentage }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Budget Comparison -->
                <div v-if="budgetComparison && budgetComparison.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Budget Comparison</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Budget Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Period
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date Range
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Planned
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actual
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            % Used
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="budget in budgetComparison" :key="budget.id">
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
                                            <div class="text-sm text-gray-900">{{ formatCurrency(budget.total_planned) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm" :class="budget.total_actual <= budget.total_planned ? 'text-green-600' : 'text-red-600'">
                                                {{ formatCurrency(budget.total_actual) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="mr-2 w-16 bg-gray-200 rounded-full h-2">
                                                    <div 
                                                        class="h-2 rounded-full" 
                                                        :class="getProgressBarColor(calculatePercentage(budget.total_actual, budget.total_planned))"
                                                        :style="{ width: `${calculatePercentage(budget.total_actual, budget.total_planned)}%` }">
                                                    </div>
                                                </div>
                                                <span class="text-sm text-gray-500">{{ calculatePercentage(budget.total_actual, budget.total_planned) }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('budget-reports.show', budget.id)" class="text-indigo-600 hover:text-indigo-900">
                                                View Report
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- All Active Budgets -->
                <div v-if="activeBudgets && activeBudgets.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">All Active Budgets</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="budget in activeBudgets" :key="budget.id" class="bg-gray-50 p-4 rounded-lg">
                                <div class="font-medium text-gray-900">{{ budget.name }}</div>
                                <div class="text-sm text-gray-500 mt-1 capitalize">{{ budget.period_type }}</div>
                                <div class="text-sm text-gray-500 mt-1">
                                    {{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}
                                </div>
                                <div class="mt-4">
                                    <Link :href="route('budget-reports.show', budget.id)" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                        View Report
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
