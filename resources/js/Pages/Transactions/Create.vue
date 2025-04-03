<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    cashAccounts: Array,
    categories: Array
});

const form = useForm({
    cash_account_id: '',
    transaction_category_id: '',
    type: 'expense',
    amount: '',
    transaction_date: new Date().toISOString().substr(0, 10),
    reference_number: '',
    description: '',
    attachment: ''
});

const filteredCategories = ref([]);

const updateCategoryOptions = (type) => {
    filteredCategories.value = props.categories.filter(category => category.type === type);
    form.transaction_category_id = '';
};

// Initialize filtered categories
updateCategoryOptions(form.type);

const submit = () => {
    form.post(route('transactions.store'));
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
                            <div class="max-w-xl mx-auto">
                                <!-- Transaction Type -->
                                <div class="mb-6">
                                    <InputLabel value="Transaction Type" />
                                    <div class="mt-2 flex space-x-4">
                                        <label class="inline-flex items-center">
                                            <input 
                                                type="radio" 
                                                class="form-radio text-indigo-600" 
                                                name="type" 
                                                value="income" 
                                                v-model="form.type"
                                                @change="updateCategoryOptions('income')"
                                            />
                                            <span class="ml-2 text-gray-700">Income</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input 
                                                type="radio" 
                                                class="form-radio text-indigo-600" 
                                                name="type" 
                                                value="expense" 
                                                v-model="form.type"
                                                @change="updateCategoryOptions('expense')"
                                            />
                                            <span class="ml-2 text-gray-700">Expense</span>
                                        </label>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.type" />
                                </div>

                                <!-- Cash Account -->
                                <div class="mb-6">
                                    <InputLabel for="cash_account_id" value="Cash Account" />
                                    <select
                                        id="cash_account_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.cash_account_id"
                                        required
                                    >
                                        <option value="" disabled>Select a cash account</option>
                                        <option v-for="account in cashAccounts" :key="account.id" :value="account.id">
                                            {{ account.name }} ({{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(account.current_balance) }})
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.cash_account_id" />
                                </div>

                                <!-- Transaction Category -->
                                <div class="mb-6">
                                    <InputLabel for="transaction_category_id" value="Category" />
                                    <select
                                        id="transaction_category_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.transaction_category_id"
                                        required
                                    >
                                        <option value="" disabled>Select a category</option>
                                        <option v-for="category in filteredCategories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.transaction_category_id" />
                                    <div v-if="filteredCategories.length === 0" class="mt-2 text-sm text-red-600">
                                        No categories found for {{ form.type }}. Please create a category first.
                                        <Link :href="route('categories.create')" class="text-indigo-600 hover:text-indigo-800 ml-1">
                                            Create category
                                        </Link>
                                    </div>
                                </div>

                                <!-- Amount -->
                                <div class="mb-6">
                                    <InputLabel for="amount" value="Amount" />
                                    <TextInput
                                        id="amount"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        v-model="form.amount"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.amount" />
                                </div>

                                <!-- Transaction Date -->
                                <div class="mb-6">
                                    <InputLabel for="transaction_date" value="Transaction Date" />
                                    <TextInput
                                        id="transaction_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        v-model="form.transaction_date"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.transaction_date" />
                                </div>

                                <!-- Reference Number -->
                                <div class="mb-6">
                                    <InputLabel for="reference_number" value="Reference Number (Optional)" />
                                    <TextInput
                                        id="reference_number"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.reference_number"
                                    />
                                    <InputError class="mt-2" :message="form.errors.reference_number" />
                                </div>

                                <!-- Description -->
                                <div class="mb-6">
                                    <InputLabel for="description" value="Description (Optional)" />
                                    <textarea
                                        id="description"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.description"
                                        rows="3"
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>

                                <div class="flex items-center justify-end mt-8">
                                    <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        Record Transaction
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
