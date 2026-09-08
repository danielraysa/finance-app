<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    filters: Object,
    cashAccounts: Array,
    summary: Object,
    cashInTransactions: Array,
    cashOutTransactions: Array,
});

const startDate = ref(props.filters.startDate);
const endDate = ref(props.filters.endDate);
const cashAccountId = ref(props.filters.cashAccountId || '');

const formatCurrency = (value) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value || 0);

const applyFilters = () => {
    router.get(route('reports.cash-flow'), {
        start_date: startDate.value,
        end_date: endDate.value,
        cash_account_id: cashAccountId.value || null,
    }, { preserveState: true, replace: true });
};

const exportUrl = (format) => route('reports.cash-flow.export', {
    format,
    start_date: startDate.value,
    end_date: endDate.value,
    cash_account_id: cashAccountId.value || undefined,
});
</script>

<template>
    <Head title="Laporan Arus Kas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Arus Kas</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between border-b border-gray-200 pb-4 mb-5">
                        <div>
                            <p class="text-sm font-semibold tracking-wide text-green-800 uppercase">Laporan Keuangan</p>
                            <h3 class="text-2xl font-bold text-gray-900">Laporan Arus Kas</h3>
                            <p class="text-sm text-gray-500 mt-1">Periode {{ startDate }} sampai {{ endDate }}</p>
                        </div>
                        <div class="flex gap-2 mt-4 md:mt-0">
                            <a :href="exportUrl('csv')" class="px-3 py-2 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">CSV</a>
                            <a :href="exportUrl('pdf')" class="px-3 py-2 text-sm bg-green-800 text-white rounded-md hover:bg-green-900">PDF</a>
                        </div>
                    </div>
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
                        <p class="text-sm text-gray-500">Uang Masuk</p>
                        <p class="mt-2 text-3xl font-bold text-green-600">{{ formatCurrency(summary.cashIn) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <p class="text-sm text-gray-500">Uang Keluar</p>
                        <p class="mt-2 text-3xl font-bold text-red-600">{{ formatCurrency(summary.cashOut) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <p class="text-sm text-gray-500">Arus Kas Bersih</p>
                        <p class="mt-2 text-3xl font-bold" :class="summary.netCashFlow >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatCurrency(summary.netCashFlow) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Uang Masuk</h3>
                        <div v-if="cashInTransactions.length === 0" class="text-gray-500">Tidak ada arus kas masuk.</div>
                        <ul v-else class="space-y-3">
                            <li v-for="item in cashInTransactions" :key="item.id" class="flex justify-between border-b pb-2">
                                <div>
                                    <p class="font-medium">{{ item.reference_number }} {{ item.description ? `(${item.description})` : ''}}}</p>
                                    <p class="text-xs text-gray-500">{{ item.category }} • {{ item.account }}</p>
                                </div>
                                <span class="font-semibold text-green-600">{{ formatCurrency(item.amount) }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Uang Keluar</h3>
                        <div v-if="cashOutTransactions.length === 0" class="text-gray-500">Tidak ada arus kas keluar.</div>
                        <ul v-else class="space-y-3">
                            <li v-for="item in cashOutTransactions" :key="item.id" class="flex justify-between border-b pb-2">
                                <div>
                                    <p class="font-medium">{{ item.reference_number }} {{ item.description ? `(${item.description})` : ''}}</p>
                                    <p class="text-xs text-gray-500">{{ item.category }} • {{ item.account }}</p>
                                </div>
                                <span class="font-semibold text-red-600">{{ formatCurrency(item.amount) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
