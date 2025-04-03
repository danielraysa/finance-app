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
    category: Object
});

const form = useForm({
    name: props.category.name,
    type: props.category.type,
    color: props.category.color || '#6366F1', // Default indigo color
    icon: props.category.icon || '',
    description: props.category.description || '',
    is_active: props.category.is_active
});

const colorOptions = [
    { name: 'Red', value: '#EF4444' },
    { name: 'Orange', value: '#F97316' },
    { name: 'Amber', value: '#F59E0B' },
    { name: 'Yellow', value: '#EAB308' },
    { name: 'Lime', value: '#84CC16' },
    { name: 'Green', value: '#22C55E' },
    { name: 'Emerald', value: '#10B981' },
    { name: 'Teal', value: '#14B8A6' },
    { name: 'Cyan', value: '#06B6D4' },
    { name: 'Sky', value: '#0EA5E9' },
    { name: 'Blue', value: '#3B82F6' },
    { name: 'Indigo', value: '#6366F1' },
    { name: 'Violet', value: '#8B5CF6' },
    { name: 'Purple', value: '#A855F7' },
    { name: 'Fuchsia', value: '#D946EF' },
    { name: 'Pink', value: '#EC4899' },
    { name: 'Rose', value: '#F43F5E' },
];

const submit = () => {
    form.put(route('categories.update', props.category.id));
};
</script>

<template>
    <Head title="Edit Category" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Category</h2>
                <Link :href="route('categories.index')" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
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
                                    <InputLabel for="name" value="Category Name" />
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

                                <!-- Type -->
                                <div class="mb-6">
                                    <InputLabel value="Category Type" />
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

                                <!-- Color -->
                                <div class="mb-6">
                                    <InputLabel for="color" value="Color" />
                                    <div class="mt-2 grid grid-cols-8 gap-2">
                                        <div 
                                            v-for="color in colorOptions" 
                                            :key="color.value"
                                            class="w-8 h-8 rounded-full cursor-pointer border-2"
                                            :style="{ backgroundColor: color.value, borderColor: form.color === color.value ? 'black' : 'transparent' }"
                                            @click="form.color = color.value"
                                            :title="color.name"
                                        ></div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.color" />
                                </div>

                                <!-- Icon (emoji or letter) -->
                                <div class="mb-6">
                                    <InputLabel for="icon" value="Icon (Optional)" />
                                    <TextInput
                                        id="icon"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.icon"
                                        maxlength="2"
                                        placeholder="Emoji or 1-2 letters"
                                    />
                                    <p class="mt-1 text-sm text-gray-500">Use an emoji or 1-2 letters as an icon</p>
                                    <InputError class="mt-2" :message="form.errors.icon" />
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

                                <!-- Preview -->
                                <div class="mb-6 border rounded-lg p-4">
                                    <h3 class="text-sm font-medium text-gray-700 mb-2">Preview</h3>
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
                                            :style="{ backgroundColor: form.color }">
                                            <span class="text-white">{{ form.icon || form.name.charAt(0) }}</span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium">{{ form.name || 'Category Name' }}</h4>
                                            <p class="text-xs text-gray-500">{{ form.type === 'income' ? 'Income' : 'Expense' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end mt-8">
                                    <Link
                                        :href="route('categories.index')"
                                        class="px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Cancel
                                    </Link>
                                    <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        Update Category
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
