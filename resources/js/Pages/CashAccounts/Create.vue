<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

const form = useForm({
    name: '',
    account_number: '',
    description: '',
    initial_balance: 0,
    is_active: true
});

const submit = () => {
    form.post(route('cash-accounts.store'));
};
</script>

<template>
    <Head title="Create Cash Account" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Cash Account</h2>
                <Link :href="route('cash-accounts.index')" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
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
                                <!-- Name -->
                                <div class="mb-6">
                                    <InputLabel for="name" value="Account Name" />
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

                                <!-- Account Number -->
                                <div class="mb-6">
                                    <InputLabel for="account_number" value="Account Number (Optional)" />
                                    <TextInput
                                        id="account_number"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.account_number"
                                    />
                                    <InputError class="mt-2" :message="form.errors.account_number" />
                                </div>

                                <!-- Initial Balance -->
                                <div class="mb-6">
                                    <InputLabel for="initial_balance" value="Initial Balance" />
                                    <TextInput
                                        id="initial_balance"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        v-model="form.initial_balance"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.initial_balance" />
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

                                <!-- Is Active -->
                                <div class="mb-6 flex items-center">
                                    <Checkbox
                                        id="is_active"
                                        v-model:checked="form.is_active"
                                    />
                                    <InputLabel for="is_active" value="Active" class="ml-2" />
                                    <InputError class="mt-2" :message="form.errors.is_active" />
                                </div>

                                <div class="flex items-center justify-end mt-8">
                                    <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        Create Account
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
