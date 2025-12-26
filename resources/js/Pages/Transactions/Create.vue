<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextArea from '@/Components/TextArea.vue';

const props = defineProps({
    cashAccounts: Array,
    categories: Array
});

const newDetail = () => ({
    cash_account_id: '',
    transaction_category_id: '',
    type: 'expense',
    amount: '',
    reference_number: '',
    description: ''
});

const form = useForm({
    transaction_date: new Date().toISOString().substr(0, 10),
    reference_number: '',
    description: '',
    attachment: '',
    transactions: [ newDetail() ]
});

const addRow = () => {
    form.transactions.push(newDetail());
};

const removeRow = (index) => {
    if (form.transactions.length === 1) return;
    form.transactions.splice(index, 1);
};

const filteredCategoriesFor = (type) => {
    return props.categories.filter(category => category.type === type);
};

const submit = () => {
    // If there's a file, submit as FormData
    if (form.attachment) {
        form.post(route('transactions.store'), { forceFormData: true });
    } else {
        form.post(route('transactions.store'));
    }
};
</script>

<template>
    <Head title="Record Transaction" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Record Transaction</h2>
                <Link :href="route('transactions.index')" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="max-w-3xl mx-auto">
                                <!-- Master fields -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <InputLabel for="transaction_date" value="Transaction Date" />
                                        <TextInput id="transaction_date" type="date" class="mt-1 block w-full" v-model="form.transaction_date" required />
                                        <InputError class="mt-2" :message="form.errors.transaction_date" />
                                    </div>
                                    <div>
                                        <InputLabel for="reference_number" value="Reference Number (Optional)" />
                                        <TextInput id="reference_number" type="text" class="mt-1 block w-full" v-model="form.reference_number" />
                                        <InputError class="mt-2" :message="form.errors.reference_number" />
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <InputLabel for="description" value="Description (Optional)" />
                                    <TextArea id="description" v-model="form.description" rows="2" />
                                </div>

                                <div class="mb-6">
                                    <InputLabel for="attachment" value="Attachment (Optional)" />
                                    <input id="attachment" type="file" class="mt-1 block w-full text-gray-700" @input="form.attachment = $event.target.files[0]" />
                                </div>

                                <!-- Details table -->
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="font-medium">Transaction Details</h3>
                                        <button type="button" @click="addRow" class="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">Add Row</button>
                                    </div>

                                    <div class="space-y-4">
                                        <div v-for="(detail, idx) in form.transactions" :key="idx" class="p-4 border rounded-md">
                                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                <div>
                                                    <InputLabel value="Type" />
                                                    <div class="mt-2 flex space-x-2">
                                                        <label class="inline-flex items-center">
                                                            <input type="radio" class="form-radio text-indigo-600" :name="`type-${idx}`" value="income" v-model="detail.type" />
                                                            <span class="ml-2 text-gray-700">Income</span>
                                                        </label>
                                                        <label class="inline-flex items-center">
                                                            <input type="radio" class="form-radio text-indigo-600" :name="`type-${idx}`" value="expense" v-model="detail.type" />
                                                            <span class="ml-2 text-gray-700">Expense</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div>
                                                    <InputLabel value="Cash Account" />
                                                    <select class="mt-1 block w-full border-gray-300 rounded-md" v-model="detail.cash_account_id" required>
                                                        <option value="" disabled>Select account</option>
                                                        <option v-for="account in cashAccounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <InputLabel value="Category" />
                                                    <select class="mt-1 block w-full border-gray-300 rounded-md" v-model="detail.transaction_category_id" required>
                                                        <option value="" disabled>Select category</option>
                                                        <option v-for="cat in filteredCategoriesFor(detail.type)" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <InputLabel value="Amount" />
                                                    <TextInput type="number" step="0.01" class="mt-1 block w-full" v-model="detail.amount" required />
                                                </div>
                                            </div>

                                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <InputLabel value="Reference (Optional)" />
                                                    <TextInput type="text" class="mt-1 block w-full" v-model="detail.reference_number" />
                                                </div>
                                                <div class="flex items-end justify-end">
                                                    <button type="button" @click="removeRow(idx)" class="px-3 py-1 bg-red-600 text-white rounded-md text-sm">Remove</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end mt-8">
                                    <Link :href="route('transactions.index')" class="px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">Cancel</Link>
                                     <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Record Transaction</PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
