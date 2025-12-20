
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    budget: Object,
    budgetItems: Array,
    dailySpending: Array,
    categoryBreakdown: Array,
    topSpendingCategories: Array,
});

// Open PDF in a new tab using a full navigation (bypasses Inertia)
const openPdf = (id) => {
    const url = route('budget-reports.pdf', id);
    window.open(url, '_blank', 'noopener,noreferrer');
};

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
</script>
<template>
    <Head :title="`Budget Report: ${budget.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Budget Report</h2>
                <div class="flex space-x-2">
                    <button @click.prevent="openPdf(budget.id)" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                        Download PDF
                    </button>
                    <Link :href="route('budgets.show', budget.id)" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        View Budget
                    </Link>
                    <Link :href="route('budget-reports.index')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                        All Reports
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
                                </p>
                                <p v-if="budget.description" class="text-gray-600 mt-2">{{ budget.description }}</p>
                            </div>
                            <div class="mt-4 md:mt-0 text-right">
                                <div class="text-sm text-gray-500">Budget Period</div>
                                <div class="text-lg font-semibold capitalize">{{ budget.period_type }}</div>
                                <div class="text-sm text-gray-500 mt-2">Days Remaining</div>
                                <div class="text-lg font-semibold">{{ budget.days_remaining }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Planned Amount -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-500">Planned Amount</div>
                                <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(budget.total_planned) }}</div>
                            </div>

                            <!-- Actual Amount -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-500">Actual Amount</div>
                                <div class="text-2xl font-bold" :class="budget.total_actual <= budget.total_planned ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(budget.total_actual) }}
                                </div>
                            </div>

                            <!-- Remaining Amount -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-500">Budget Progress</div>
                                <div class="text-2xl font-bold text-blue-600">
                                    {{ budget.progress_percentage }}%
                                </div>
                                <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full bg-blue-600" :style="{ width: `${budget.progress_percentage}%` }"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Time elapsed</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Category Breakdown</h3>

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
                                        <tr v-for="item in categoryBreakdown" :key="item.category_name">
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
                                                        <div class="h-2 rounded-full" :style="{ width: `${item.percentage_used}%`, backgroundColor: item.category_color }"></div>
                                                    </div>
                                                    <span class="text-sm text-gray-500">{{ item.percentage_used }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Spending Categories -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Spending Categories</h3>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div v-for="(category, index) in topSpendingCategories" :key="index" class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <div class="h-3 w-3 rounded-full mr-2" :style="{ backgroundColor: category.category_color }"></div>
                                    <div class="text-sm font-medium text-gray-900">{{ category.category_name }}</div>
                                </div>
                                <div class="text-lg font-bold text-gray-900">{{ formatCurrency(category.actual_amount) }}</div>
                                <div class="text-xs text-gray-500">{{ category.percentage_of_total }}% of total</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily Spending Trend -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Spending Trend</h3>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="aspect-w-16 aspect-h-6">
                                <!-- This is a placeholder for a chart component -->
                                <div class="w-full h-full flex items-center justify-center">
                                    <div class="text-center">
                                        <div class="text-gray-500">Daily Spending Trend Chart</div>
                                        <div class="mt-2 text-sm text-gray-400">Implement with Chart.js or similar library</div>
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
