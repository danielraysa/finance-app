<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    filters: Object,
    cashAccounts: Array,
    summary: Object,
    incomeBreakdown: Array,
    expenseBreakdown: Array,
});

const startDate = ref(props.filters.startDate);
const endDate = ref(props.filters.endDate);
const cashAccountId = ref(props.filters.cashAccountId || '');

const formatCurrency = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value || 0);

const applyFilters = () => {
    router.get(route('reports.profit-loss'), {
        start_date: startDate.value,
        end_date: endDate.value,
        cash_account_id: cashAccountId.value || null,
    }, { preserveState: true, replace: true });
};

const netProfitStatus = computed(() => props.summary.netProfit >= 0 ? 'Laba Bersih' : 'Rugi Bersih');
</script>

<template>
    <Head title="Laporan Laba Rugi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Laba Rugi</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                            <input v-model="startDate" type="date" class="mt-1 block w-full rounded-md border-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                            <input v-model="endDate" type="date" class="mt-1 block w-full rounded-md border-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rekening</label>
                            <select v-model="cashAccountId" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">Semua Rekening</option>
                                <option v-for="account in cashAccounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Terapkan Filter
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <p class="text-sm text-gray-500">Total Pendapatan</p>
                        <p class="mt-2 text-3xl font-bold text-green-600">{{ formatCurrency(summary.totalIncome) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <p class="text-sm text-gray-500">Total Beban</p>
                        <p class="mt-2 text-3xl font-bold text-red-600">{{ formatCurrency(summary.totalExpense) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <p class="text-sm text-gray-500">{{ netProfitStatus }}</p>
                        <p class="mt-2 text-3xl font-bold" :class="summary.netProfit >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatCurrency(summary.netProfit) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pendapatan</h3>
                        <div v-if="incomeBreakdown.length === 0" class="text-gray-500">Tidak ada data pendapatan.</div>
                        <ul v-else class="space-y-3">
                            <li v-for="item in incomeBreakdown" :key="item.category" class="flex justify-between border-b pb-2">
                                <span>{{ item.category }}</span>
                                <span class="font-semibold text-green-600">{{ formatCurrency(item.total) }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Beban</h3>
                        <div v-if="expenseBreakdown.length === 0" class="text-gray-500">Tidak ada data beban.</div>
                        <ul v-else class="space-y-3">
                            <li v-for="item in expenseBreakdown" :key="item.category" class="flex justify-between border-b pb-2">
                                <span>{{ item.category }}</span>
                                <span class="font-semibold text-red-600">{{ formatCurrency(item.total) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
