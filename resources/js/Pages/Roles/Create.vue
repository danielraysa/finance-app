<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    permissions: Array,
});

const form = useForm({
    name: '',
    description: '',
    permissions: [],
});

const submit = () => {
    form.post(route('roles.store'));
};
</script>

<template>
    <Head title="Create Role" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Role
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <!-- Name -->
                        <div class="mb-4">
                            <InputLabel for="name" value="Role Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <InputLabel for="description" value="Description" />
                            <TextArea
                                id="description"
                                v-model="form.description"
                                class="mt-1 block w-full"
                                placeholder="Enter role description"
                            />
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <!-- Permissions -->
                        <div class="mb-4">
                            <InputLabel value="Assign Permissions" />
                            <div class="mt-2 grid grid-cols-2 gap-4 max-h-96 overflow-y-auto border border-gray-300 p-4 rounded">
                                <label v-for="permission in permissions" :key="permission.id" class="flex items-center">
                                    <input
                                        type="checkbox"
                                        :value="permission.id"
                                        v-model="form.permissions"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                                    />
                                    <span class="ms-2 text-sm">{{ permission.name }}</span>
                                </label>
                            </div>
                            <InputError :message="form.errors.permissions" class="mt-2" />
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-4">
                            <PrimaryButton :disabled="form.processing">Create Role</PrimaryButton>
                            <Link :href="route('roles.index')">
                                <SecondaryButton>Cancel</SecondaryButton>
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
