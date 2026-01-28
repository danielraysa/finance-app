<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    roles: Array,
    userRoles: Array,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    roles: props.userRoles,
});

const submit = () => {
    form.put(route('users.update', props.user.id));
};
</script>

<template>
    <Head :title="`Edit User - ${user.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit User: {{ user.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <!-- Name -->
                        <div class="mb-4">
                            <InputLabel for="name" value="Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <!-- Password (Optional) -->
                        <div class="mb-4">
                            <InputLabel for="password" value="Password (Leave blank to keep current)" />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>

                        <!-- Password Confirmation -->
                        <div class="mb-4" v-if="form.password">
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.password_confirmation" class="mt-2" />
                        </div>

                        <!-- Roles -->
                        <div class="mb-4">
                            <InputLabel value="Assign Roles" />
                            <div class="mt-2 space-y-2">
                                <label v-for="role in roles" :key="role.id" class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :value="role.id"
                                        v-model="form.roles"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                                    />
                                    <span class="ms-2">{{ role.name }}</span>
                                </label>
                            </div>
                            <InputError :message="form.errors.roles" class="mt-2" />
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-4">
                            <PrimaryButton :disabled="form.processing">Update User</PrimaryButton>
                            <Link :href="route('users.index')" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Cancel
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
