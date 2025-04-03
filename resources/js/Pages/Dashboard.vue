<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Chart, registerables } from 'chart.js';

// Register all Chart.js components
Chart.register(...registerables);

const props = defineProps({
    summary: Object,
    recentTransactions: Array,
    cashAccounts: Array,
    chartData: Object
});

const chartLoaded = ref(false);

onMounted(() => {
    if (props.chartData && props.chartData.labels.length > 0) {
        renderChart();
    }
});

const renderChart = () => {
    initChart();
};

const initChart = () => {
    const ctx = document.getElementById('financialChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: props.chartData.labels,
            datasets: [
                {
                    label: 'Income',
                    data: props.chartData.incomeData,
                    backgroundColor: 'rgba(34, 197, 94, 0.5)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                },
                {
                    label: 'Expense',
                    data: props.chartData.expenseData,
                    backgroundColor: 'rgba(239, 68, 68, 0.5)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    chartLoaded.value = true;
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
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Financial Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Total Balance Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900">Total Balance</h3>
                            <p class="mt-2 text-3xl font-bold text-indigo-600">{{ formatCurrency(summary.totalCashBalance) }}</p>
                            <p class="mt-1 text-sm text-gray-500">Current cash balance</p>
                        </div>
                    </div>
                    
                    <!-- Total Income Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900">Total Income</h3>
                            <p class="mt-2 text-3xl font-bold text-green-600">{{ formatCurrency(summary.totalIncome) }}</p>
                            <p class="mt-1 text-sm text-gray-500">All time income</p>
                        </div>
                    </div>
                    
                    <!-- Total Expense Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900">Total Expense</h3>
                            <p class="mt-2 text-3xl font-bold text-red-600">{{ formatCurrency(summary.totalExpense) }}</p>
                            <p class="mt-1 text-sm text-gray-500">All time expense</p>
                        </div>
                    </div>
                    
                    <!-- Net Balance Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900">Net Balance</h3>
                            <p class="mt-2 text-3xl font-bold" :class="summary.netBalance >= 0 ? 'text-green-600' : 'text-red-600'">{{ formatCurrency(summary.netBalance) }}</p>
                            <p class="mt-1 text-sm text-gray-500">Income - Expense</p>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Cash Accounts -->
                    <div class="lg:col-span-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Cash Accounts</h3>
                                    <Link :href="route('cash-accounts.create')" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Add New
                                    </Link>
                                </div>
                                
                                <div v-if="cashAccounts.length === 0" class="text-center py-4">
                                    <p class="text-gray-500">No cash accounts found</p>
                                    <Link :href="route('cash-accounts.create')" class="mt-2 inline-block text-indigo-600 hover:text-indigo-800">
                                        Create your first cash account
                                    </Link>
                                </div>
                                
                                <ul v-else class="divide-y divide-gray-200">
                                    <li v-for="account in cashAccounts" :key="account.id" class="py-3">
                                        <Link :href="route('cash-accounts.show', account.id)" class="block hover:bg-gray-50">
                                            <div class="flex justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ account.name }}</p>
                                                    <p v-if="account.account_number" class="text-xs text-gray-500">{{ account.account_number }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm font-semibold" :class="account.current_balance >= 0 ? 'text-green-600' : 'text-red-600'">
                                                        {{ formatCurrency(account.current_balance) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </Link>
                                    </li>
                                </ul>
                                
                                <div class="mt-4 text-right">
                                    <Link :href="route('cash-accounts.index')" class="text-sm text-indigo-600 hover:text-indigo-800">
                                        View all cash accounts →
                                    </Link>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Transactions -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
                                    <Link :href="route('transactions.create')" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Add New
                                    </Link>
                                </div>
                                
                                <div v-if="recentTransactions.length === 0" class="text-center py-4">
                                    <p class="text-gray-500">No transactions found</p>
                                    <Link :href="route('transactions.create')" class="mt-2 inline-block text-indigo-600 hover:text-indigo-800">
                                        Record your first transaction
                                    </Link>
                                </div>
                                
                                <ul v-else class="divide-y divide-gray-200">
                                    <li v-for="transaction in recentTransactions" :key="transaction.id" class="py-3">
                                        <Link :href="route('transactions.show', transaction.id)" class="block hover:bg-gray-50">
                                            <div class="flex justify-between">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ transaction.category.name }}</p>
                                                    <p class="text-xs text-gray-500">{{ formatDate(transaction.transaction_date) }} • {{ transaction.cash_account.name }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm font-semibold" :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                                        {{ transaction.type === 'income' ? '+' : '-' }} {{ formatCurrency(transaction.amount) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </Link>
                                    </li>
                                </ul>
                                
                                <div class="mt-4 text-right">
                                    <Link :href="route('transactions.index')" class="text-sm text-indigo-600 hover:text-indigo-800">
                                        View all transactions →
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart -->
                    <div class="lg:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Income vs Expense (Last 6 Months)</h3>
                                
                                <div v-if="chartData.labels.length === 0" class="text-center py-12">
                                    <p class="text-gray-500">No data available for chart</p>
                                </div>
                                
                                <div v-else class="h-80">
                                    <canvas id="financialChart"></canvas>
                                </div>
                                
                                <div class="mt-6 text-right">
                                    <Link :href="route('reports')" class="text-sm text-indigo-600 hover:text-indigo-800">
                                        View detailed reports →
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
