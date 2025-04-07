
<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    budget: Object,
    categories: Array,
});

// Filter categories by type
const incomeCategories = computed(() => {
    return props.categories.filter(category => category.type === 'income');
});

const expenseCategories = computed(() => {
    return props.categories.filter(category => category.type === 'expense');
});

// Form data
const form = useForm({
    name: props.budget.name,
    period_type: props.budget.period_type,
    start_date: props.budget.start_date.substring(0, 10),
    end_date: props.budget.end_date.substring(0, 10),
    description: props.budget.description || '',
    is_active: props.budget.is_active,
    budget_items: props.budget.budget_items.map(item => ({
        id: item.id,
        transaction_category_id: item.transaction_category_id,
        planned_amount: item.planned_amount,
        actual_amount: item.actual_amount,
        notes: item.notes || '',
    })),
});

// Add a new budget item
const addBudgetItem = () => {
    form.budget_items.push({
        transaction_category_id: '',
        planned_amount: '',
        actual_amount: 0,
        notes: '',
    });
};

// Remove a budget item
const removeBudgetItem = (index) => {
    form.budget_items.splice(index, 1);
};

// Calculate total budget
const calculateTotalBudget = () => {
    return form.budget_items.reduce((total, item) => {
        const amount = parseFloat(item.planned_amount) || 0;
        return total + amount;
    }, 0);
};

// Calculate total actual
const calculateTotalActual = () => {
    return form.budget_items.reduce((total, item) => {
        const amount = parseFloat(item.actual_amount) || 0;
        return total + amount;
    }, 0);
};

// Format currency
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
    }).format(amount);
};

// Get error messages for nested form fields
const getCategoryError = (index) => {
    return form.errors[`budget_items.${index}.transaction_category_id`];
};

const getPlannedAmountError = (index) => {
    return form.errors[`budget_items.${index}.planned_amount`];
};

// Submit the form
const submit = () => {
    form.put(route('budgets.update', props.budget.id));
};
</script>
<template>
    <Head title="Edit Budget" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Budget</h2>
                <Link :href="route('budgets.index')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                    Back to Budgets
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <!-- Budget Details Section -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Budget Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Budget Name -->
                                    <div>
                                        <InputLabel for="name" value="Budget Name" />
                                        <TextInput
                                            id="name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.name"
                                            required
                                            autofocus
                                        />
                                        <InputError class="mt-2" :message="form.errors.name" />
                                    </div>

                                    <!-- Budget Period Type -->
                                    <div>
                                        <InputLabel for="period_type" value="Budget Period" />
                                        <select
                                            id="period_type"
                                            class="mt-1 block w-full"
                                            v-model="form.period_type"
                                            required
                                        >
                                            <option value="">Select Period Type</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="quarterly">Quarterly</option>
                                            <option value="yearly">Yearly</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.period_type" />
                                    </div>

                                    <!-- Start Date -->
                                    <div>
                                        <InputLabel for="start_date" value="Start Date" />
                                        <TextInput
                                            id="start_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                            v-model="form.start_date"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.start_date" />
                                    </div>

                                    <!-- End Date -->
                                    <div>
                                        <InputLabel for="end_date" value="End Date" />
                                        <TextInput
                                            id="end_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                            v-model="form.end_date"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.end_date" />
                                    </div>

                                    <!-- Description -->
                                    <div class="md:col-span-2">
                                        <InputLabel for="description" value="Description (Optional)" />
                                        <TextArea
                                            id="description"
                                            class="mt-1 block w-full"
                                            v-model="form.description"
                                            rows="3"
                                        />
                                        <InputError class="mt-2" :message="form.errors.description" />
                                    </div>

                                    <!-- Active Status -->
                                    <div>
                                        <div class="flex items-center mt-2">
                                            <Checkbox id="is_active" v-model:checked="form.is_active" />
                                            <InputLabel for="is_active" value="Active Budget" class="ml-2" />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.is_active" />
                                    </div>
                                </div>
                            </div>

                            <!-- Budget Items Section -->
                            <div class="mb-8">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Budget Items</h3>
                                    <button
                                        type="button"
                                        @click="addBudgetItem"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition"
                                    >
                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add Item
                                    </button>
                                </div>

                                <div v-if="form.budget_items.length === 0" class="bg-gray-50 p-4 rounded-md text-center">
                                    <p class="text-gray-500">No budget items added yet. Click "Add Item" to start creating your budget.</p>
                                </div>

                                <div v-else>
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
                                                        Notes
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Actions
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="(item, index) in form.budget_items" :key="index">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <select
                                                            v-model="item.transaction_category_id"
                                                            class="block w-full"
                                                            required
                                                        >
                                                            <option value="">Select Category</option>
                                                            <optgroup label="Income Categories">
                                                                <option
                                                                    v-for="category in incomeCategories"
                                                                    :key="category.id"
                                                                    :value="category.id"
                                                                >
                                                                    {{ category.name }}
                                                                </option>
                                                            </optgroup>
                                                            <optgroup label="Expense Categories">
                                                                <option
                                                                    v-for="category in expenseCategories"
                                                                    :key="category.id"
                                                                    :value="category.id"
                                                                >
                                                                    {{ category.name }}
                                                                </option>
                                                            </optgroup>
                                                        </select>
                                                        <InputError class="mt-1" :message="getCategoryError(index)" />
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="relative">
                                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                                <span class="text-gray-500 sm:text-sm">Rp</span>
                                                            </div>
                                                            <TextInput
                                                                type="number"
                                                                step="0.01"
                                                                min="0"
                                                                class="block w-full pl-7"
                                                                v-model="item.planned_amount"
                                                                required
                                                            />
                                                        </div>
                                                        <InputError class="mt-1" :message="getPlannedAmountError(index)" />
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">
                                                            {{ formatCurrency(item.actual_amount || 0) }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <TextInput
                                                            type="text"
                                                            class="block w-full"
                                                            v-model="item.notes"
                                                            placeholder="Optional notes"
                                                        />
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        <button
                                                            type="button"
                                                            @click="removeBudgetItem(index)"
                                                            class="text-red-600 hover:text-red-900"
                                                        >
                                                            Remove
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="bg-gray-50">
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        Total Budget
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ formatCurrency(calculateTotalBudget()) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ formatCurrency(calculateTotalActual()) }}
                                                    </td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.budget_items" />
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <Link
                                    :href="route('budgets.index')"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition mr-2"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update Budget
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
