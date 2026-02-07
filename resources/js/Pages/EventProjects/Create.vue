<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextArea from '@/Components/TextArea.vue';
import { computed } from 'vue';

const props = defineProps({
    budgetItems: Array,
    cashFlows: Array // optional if you want to link a cash flow
});
const moneyFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR'
});
const newDetail = () => ({
    budget_item_id: '',
    allocated_amount: '',
    approved_amount: ''
});

const form = useForm({
    event_name: '',
    event_date: new Date().toISOString().substr(0, 10),
    location: '',
    description: '',
    note: '',
    status: 'planned',
    attachment: null,
    cash_flow_id: null,
    details: [ newDetail() ]
});

const totalAllocatedAmount = computed(() => {
    return moneyFormatter.format(form.details.reduce((total, detail) => {
        const amount = parseFloat(detail.allocated_amount);
        return total + (isNaN(amount) ? 0 : amount);
    }, 0).toFixed(2));
});

const addRow = () => { form.details.push(newDetail()); };
const removeRow = (index) => { if (form.details.length > 1) form.details.splice(index,1); };

const submit = () => {
    if (form.attachment) {
        form.post(route('event-projects.store'), { forceFormData: true });
    } else {
        form.post(route('event-projects.store'));
    }
};
</script>

<template>
    <Head title="Create Event Project" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Event Project</h2>
                <Link :href="route('event-projects.index')" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700">Back to List</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="mb-6">
                                    <InputLabel for="event_name" value="Event Name" />
                                    <TextInput id="event_name" type="text" class="mt-1 block w-full" v-model="form.event_name" required />
                                    <InputError class="mt-2" :message="form.errors.event_name" />
                                </div>
                                <div class="mb-6">
                                    <InputLabel for="event_date" value="Event Date" />
                                    <TextInput id="event_date" type="date" class="mt-1 block w-full" v-model="form.event_date" required />
                                    <InputError class="mt-2" :message="form.errors.event_date" />
                                </div>

                                <div class="mb-6">
                                    <InputLabel for="location" value="Location (Optional)" />
                                    <TextInput id="location" type="text" class="mt-1 block w-full" v-model="form.location" />
                                </div>

                                <div class="mb-6">
                                    <InputLabel for="status" value="Status" />
                                    <select id="status" v-model="form.status" class="mt-1 block w-full border-gray-300 rounded-md">
                                        <option value="planned">Planned</option>
                                        <option value="approved">Approved</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>

                                <div class="mb-6 col-span-2">
                                    <InputLabel for="description" value="Description (Optional)" />
                                    <TextArea id="description" v-model="form.description" rows="3" />
                                </div>

                                <div class="mb-6 col-span-2">
                                    <InputLabel for="note" value="Note (Optional)" />
                                    <TextArea id="note" v-model="form.note" rows="2" />
                                </div>

                                <div class="mb-6">
                                    <InputLabel for="attachment" value="Attachment (Optional)" />
                                    <input id="attachment" type="file" class="mt-1 block w-full text-gray-700" @input="form.attachment = $event.target.files[0]" />
                                </div>

                                <!-- <div class="mb-6">
                                    <InputLabel for="cash_flow_id" value="Link to Cash Flow (Optional)" />
                                    <select id="cash_flow_id" v-model="form.cash_flow_id" class="mt-1 block w-full border-gray-300 rounded-md">
                                        <option value="" disabled>Select cash flow</option>
                                        <option v-for="cf in props.cashFlows || []" :key="cf.id" :value="cf.id">{{ cf.reference_number || cf.transaction_date }}</option>
                                    </select>
                                </div> -->
                            </div>

                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-medium">Budget Details</h3>
                                    <button type="button" @click="addRow" class="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">Add Row</button>
                                </div>

                                <div class="space-y-4 mb-3">
                                    <div v-for="(detail, idx) in form.details" :key="idx" class="p-4 border rounded-md">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <InputLabel value="Budget Item" />
                                                <select class="mt-1 block w-full border-gray-300 rounded-md" v-model="detail.budget_item_id" required>
                                                    <option value="" disabled>Select budget item</option>
                                                    <option v-for="bi in props.budgetItems" :key="bi.id" :value="bi.id">{{ bi.budget.name }} - {{ bi.category.name }} ({{ moneyFormatter.format(bi.planned_amount) }})</option>
                                                </select>
                                            </div>

                                            <div>
                                                <InputLabel value="Allocated Amount" />
                                                <TextInput type="number" step="0.01" class="mt-1 block w-full" v-model="detail.allocated_amount" required />
                                            </div>

                                            <div>
                                                <InputLabel value="Approved Amount (Optional)" />
                                                <TextInput type="number" step="0.01" class="mt-1 block w-full" v-model="detail.approved_amount" />
                                            </div>
                                        </div>

                                        <div class="mt-3 flex justify-end">
                                            <button type="button" @click="removeRow(idx)" class="px-3 py-1 bg-red-600 text-white rounded-md text-sm">Remove</button>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <InputLabel value="Total Allocated Amount" />
                                    <TextInput class="mt-1 block w-full" v-model="totalAllocatedAmount" readonly />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-8">
                                <Link :href="route('event-projects.index')" class="px-4 py-2 bg-gray-300 rounded-md text-sm">Cancel</Link>
                                <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Create</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
