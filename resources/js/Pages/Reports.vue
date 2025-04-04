<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import { Chart, registerables } from 'chart.js';
// Import jsPDF and jspdf-autotable
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

// Register all Chart.js components
Chart.register(...registerables);

const props = defineProps({
    filters: Object,
    cashAccounts: Array,
    transactions: Array,
    summary: Object,
    categoryBreakdown: Array
});

const startDate = ref(props.filters.startDate);
const endDate = ref(props.filters.endDate);
const cashAccountId = ref(props.filters.cashAccountId || '');
const type = ref(props.filters.type || '');

const pieChartRef = ref(null);
let pieChart = null;

onMounted(() => {
    if (props.categoryBreakdown && props.categoryBreakdown.length > 0) {
        renderPieChart();
    }
});

watch(() => props.categoryBreakdown, (newVal) => {
    if (newVal && newVal.length > 0) {
        if (pieChart) {
            pieChart.destroy();
        }
        renderPieChart();
    }
}, { deep: true });

const renderPieChart = () => {
    const ctx = document.getElementById('categoryPieChart');
    if (!ctx) return;
    
    const labels = props.categoryBreakdown.map(item => item.category.name);
    const data = props.categoryBreakdown.map(item => item.total);
    const backgroundColors = props.categoryBreakdown.map(item => item.category.color || getRandomColor());
    
    pieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
    
    pieChartRef.value = pieChart;
};

const getRandomColor = () => {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
};

const applyFilters = () => {
    router.get(route('reports'), {
        start_date: startDate.value,
        end_date: endDate.value,
        cash_account_id: cashAccountId.value || null,
        type: type.value || null
    }, {
        preserveState: true,
        replace: true
    });
};

const resetFilters = () => {
    startDate.value = new Date().toISOString().substring(0, 7) + '-01'; // First day of current month
    endDate.value = new Date().toISOString().substring(0, 10); // Today
    cashAccountId.value = '';
    type.value = '';
    
    router.get(route('reports'), {
        start_date: startDate.value,
        end_date: endDate.value,
        cash_account_id: null,
        type: null
    }, {
        preserveState: true,
        replace: true
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const downloadCSV = () => {
    const headers = ['Date', 'Category', 'Account', 'Description', 'Reference', 'Type', 'Amount'];
    const rows = props.transactions.map(transaction => {
        return [
            formatDate(transaction.transaction_date),
            transaction.category.name,
            transaction.cash_account.name,
            transaction.description || '',
            transaction.reference_number || '',
            transaction.type,
            transaction.amount
        ];
    });

    const csvContent = [
        headers.join(','),
        ...rows.map(row => row.join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const fileName = `financial_report_${formatDate(new Date())}.csv`;
    
    if (navigator.msSaveBlob) { // IE 10+
        navigator.msSaveBlob(blob, fileName);
    } else {
        link.href = URL.createObjectURL(blob);
        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};

// Add PDF export function
const downloadPDF = () => {
    // Create a new PDF document
    const doc = new jsPDF();
    
    // Add title
    doc.setFontSize(18);
    doc.text('Financial Report', 14, 22);
    
    // Add date range
    doc.setFontSize(12);
    doc.text(`Period: ${formatDate(startDate.value)} to ${formatDate(endDate.value)}`, 14, 30);
    
    // Add summary section
    doc.setFontSize(14);
    doc.text('Summary', 14, 40);
    
    // Create summary table
    autoTable(doc, {
        startY: 45,
        head: [['Type', 'Amount']],
        body: [
            ['Total Income', formatCurrency(props.summary.totalIncome)],
            ['Total Expense', formatCurrency(props.summary.totalExpense)],
            ['Net Balance', formatCurrency(props.summary.netBalance)]
        ],
        theme: 'grid',
        headStyles: { fillColor: [66, 66, 255] }
    });
    
    // Add category breakdown section
    const finalY = doc.lastAutoTable.finalY || 45;
    doc.text('Category Breakdown', 14, finalY + 15);
    
    // Check if we have a pie chart to include
    if (pieChartRef.value && props.categoryBreakdown.length > 0) {
        // Get the canvas element
        const canvas = document.getElementById('categoryPieChart');
        if (canvas) {
            // Convert the canvas to an image
            const imgData = canvas.toDataURL('image/png');
            
            // Create category breakdown table (narrower to make room for chart)
            const categoryData = props.categoryBreakdown.map(item => [
                item.category.name,
                item.category.type.charAt(0).toUpperCase() + item.category.type.slice(1),
                formatCurrency(item.total),
                `${Math.round((item.total / (item.category.type === 'income' ? props.summary.totalIncome : props.summary.totalExpense)) * 100)}%`
            ]);
            
            // Calculate available space
            const tableHeight = categoryData.length * 10 + 15; // Approximate height of table
            const chartSize = Math.min(60, tableHeight); // Make chart size proportional but not too big
            
            // Add the chart image to the PDF - smaller size and better positioned
            doc.addImage(imgData, 'PNG', 130, finalY + 20, chartSize, chartSize);
            
            autoTable(doc, {
                startY: finalY + 20,
                head: [['Category', 'Type', 'Amount', 'Percentage']],
                body: categoryData,
                theme: 'grid',
                headStyles: { fillColor: [66, 66, 255] },
                margin: { right: 100 }, // Make room for the chart
                tableWidth: 110
            });
        } else {
            // If canvas element not found, just show the table normally
            createCategoryTable(doc, finalY + 20);
        }
    } else {
        // No chart available, just show the table normally
        createCategoryTable(doc, finalY + 20);
    }
    
    // Add transactions section
    const finalY2 = doc.lastAutoTable.finalY || (finalY + 20);
    // Add some extra space to ensure no overlap
    const transactionsStartY = finalY2 + 20;
    
    doc.text('Transactions', 14, finalY2 + 15);
    
    // Create transactions table
    const transactionData = props.transactions.map(transaction => [
        formatDate(transaction.transaction_date),
        transaction.category.name,
        transaction.cash_account.name,
        transaction.description || '-',
        transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1),
        (transaction.type === 'income' ? '+' : '-') + ' ' + formatCurrency(transaction.amount)
    ]);
    
    autoTable(doc, {
        startY: transactionsStartY,
        head: [['Date', 'Category', 'Account', 'Description', 'Type', 'Amount']],
        body: transactionData,
        theme: 'grid',
        headStyles: { fillColor: [66, 66, 255] },
        didDrawPage: (data) => {
            // Add header to each page
            doc.setFontSize(10);
            doc.text('Financial Report', data.settings.margin.left, 10);
        },
        // Adjust column widths to fit content better
        columnStyles: {
            0: { cellWidth: 25 }, // Date
            1: { cellWidth: 30 }, // Category
            2: { cellWidth: 30 }, // Account
            3: { cellWidth: 40 }, // Description
            4: { cellWidth: 20 }, // Type
            5: { cellWidth: 40 }  // Amount
        }
    });
    
    // Save the PDF
    const fileName = `financial_report_${formatDate(new Date()).replace(/\s/g, '_')}.pdf`;
    doc.save(fileName);
};

// Helper function to create the category table (used when no chart is available)
const createCategoryTable = (doc, startY) => {
    const categoryData = props.categoryBreakdown.map(item => [
        item.category.name,
        item.category.type.charAt(0).toUpperCase() + item.category.type.slice(1),
        formatCurrency(item.total),
        `${Math.round((item.total / (item.category.type === 'income' ? props.summary.totalIncome : props.summary.totalExpense)) * 100)}%`,
        item.count
    ]);
    
    autoTable(doc, {
        startY: startY,
        head: [['Category', 'Type', 'Amount', 'Percentage', 'Count']],
        body: categoryData,
        theme: 'grid',
        headStyles: { fillColor: [66, 66, 255] }
    });
};

</script>

<template>
    <Head title="Financial Reports" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Financial Reports</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Reports</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input 
                                    type="date" 
                                    id="start_date" 
                                    v-model="startDate"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                />
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input 
                                    type="date" 
                                    id="end_date" 
                                    v-model="endDate"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                />
                            </div>
                            <div>
                                <label for="cash_account_id" class="block text-sm font-medium text-gray-700 mb-1">Cash Account</label>
                                <select 
                                    id="cash_account_id" 
                                    v-model="cashAccountId"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">All Accounts</option>
                                    <option v-for="account in cashAccounts" :key="account.id" :value="account.id">
                                        {{ account.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                                <select 
                                    id="type" 
                                    v-model="type"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">All Types</option>
                                    <option value="income">Income</option>
                                    <option value="expense">Expense</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-3">
                            <button 
                                @click="resetFilters"
                                class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                            >
                                Reset
                            </button>
                            <button 
                                @click="applyFilters"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Apply Filters
                            </button>
                            <button 
                                @click="downloadCSV"
                                class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                            >
                                Export CSV
                            </button>
                            <button 
                                @click="downloadPDF"
                                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                Export PDF
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Total Income Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900">Total Income</h3>
                            <p class="mt-2 text-3xl font-bold text-green-600">{{ formatCurrency(summary.totalIncome) }}</p>
                        </div>
                    </div>
                    
                    <!-- Total Expense Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900">Total Expense</h3>
                            <p class="mt-2 text-3xl font-bold text-red-600">{{ formatCurrency(summary.totalExpense) }}</p>
                        </div>
                    </div>
                    
                    <!-- Net Balance Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900">Net Balance</h3>
                            <p class="mt-2 text-3xl font-bold" :class="summary.netBalance >= 0 ? 'text-green-600' : 'text-red-600'">{{ formatCurrency(summary.netBalance) }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Category Breakdown -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Category Breakdown</h3>
                            
                            <div v-if="categoryBreakdown.length === 0" class="text-center py-12">
                                <p class="text-gray-500">No data available for category breakdown</p>
                            </div>
                            
                            <div v-else>
                                <ul class="divide-y divide-gray-200">
                                    <li v-for="item in categoryBreakdown" :key="item.category.id" class="py-3 flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                                :style="{ backgroundColor: item.category.color || '#6366F1' }">
                                                <span class="text-white text-xs">{{ item.category.icon || item.category.name.charAt(0) }}</span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ item.category.name }}</p>
                                                <p class="text-xs text-gray-500">{{ item.count }} transactions</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium" :class="item.category.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                                {{ formatCurrency(item.total) }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ Math.round((item.total / (item.category.type === 'income' ? summary.totalIncome : summary.totalExpense)) * 100) }}%
                                            </p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Category Chart</h3>
                            
                            <div v-if="categoryBreakdown.length === 0" class="text-center py-12">
                                <p class="text-gray-500">No data available for chart</p>
                            </div>
                            
                            <div v-else class="h-64">
                                <div class="flex items-center justify-center h-full">
                                    <canvas id="categoryPieChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Transactions List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Transactions</h3>
                        
                        <div v-if="transactions.length === 0" class="text-center py-8">
                            <p class="text-gray-500">No transactions found for the selected filters</p>
                        </div>
                        
                        <div v-else>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="transaction in transactions" :key="transaction.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(transaction.transaction_date) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-6 h-6 rounded-full flex items-center justify-center mr-2"
                                                        :style="{ backgroundColor: transaction.category.color || (transaction.type === 'income' ? '#22C55E' : '#EF4444') }">
                                                        <span class="text-white text-xs">{{ transaction.category.icon || transaction.category.name.charAt(0) }}</span>
                                                    </div>
                                                    <div class="text-sm font-medium text-gray-900">{{ transaction.category.name }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ transaction.cash_account.name }}
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
