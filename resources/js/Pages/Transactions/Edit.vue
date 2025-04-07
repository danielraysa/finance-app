<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextArea from '@/Components/TextArea.vue';

const props = defineProps({
    transaction: Object,
    cashAccounts: Array,
    categories: Array
});

const form = useForm({
    cash_account_id: props.transaction.cash_account_id,
    transaction_category_id: props.transaction.transaction_category_id,
    type: props.transaction.type,
    amount: props.transaction.amount,
    transaction_date: props.transaction.transaction_date.substring(0, 10),
    reference_number: props.transaction.reference_number,
    description: props.transaction.description,
    attachment: null // We don't pre-fill the attachment
});

const submit = () => {
    if (form.attachment) {
        form.post(route('transactions.update', props.transaction.id), {
            method: 'put',
            forceFormData: true
        });
    } else {
        form.put(route('transactions.update', props.transaction.id));
    }
};

const filteredCategories = () => {
    if (!form.type) return props.categories;
    return props.categories.filter(category => category.type === form.type);
};
</script>

<template>
    <Head title="Edit Transaction" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Transaction</h2>
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
                                        <option value="" disabled>Select an account</option>
                                        <option v-for="account in cashAccounts" :key="account.id" :value="account.id">
                                            {{ account.name }}
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
                                        <option v-for="category in filteredCategories()" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.transaction_category_id" />
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
                                    <TextArea id="description" v-model="form.description" rows="3" />
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>

                                <!-- Attachment -->
                                <div class="mb-6">
                                    <InputLabel for="attachment" value="Attachment (Optional)" />
                                    <input
                                        id="attachment"
                                        type="file"
                                        class="mt-1 block w-full text-gray-700"
                                        @input="form.attachment = $event.target.files[0]"
                                    />
                                    <p v-if="transaction.attachment_path" class="mt-2 text-sm text-gray-500">
                                        Current attachment: {{ transaction.attachment_path.split('/').pop() }}
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.attachment" />
                                </div>

                                <div class="flex items-center justify-end mt-8">
                                    <Link
                                        :href="route('transactions.index')"
                                        class="px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Cancel
                                    </Link>
                                    <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        Update Transaction
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
