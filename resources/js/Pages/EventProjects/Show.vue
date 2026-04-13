<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const props = defineProps({
    eventProject: Object
});

const user = computed(() => page.props.auth.user);
const isVerificator = computed(() => user.value?.roles?.map((role) => role.name).includes('verificator'));

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'planned': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'completed': 'bg-blue-100 text-blue-800',
        'rejected': 'bg-red-100 text-red-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const totalAllocated = () => {
    return (props.eventProject.details || []).reduce((sum, d) => sum + parseFloat(d.allocated_amount || 0), 0);
};

const totalApproved = () => {
    return (props.eventProject.details || []).reduce((sum, d) => sum + parseFloat(d.approved_amount || 0), 0);
};

const deleteEventProject = () => {
    if (confirm('Are you sure you want to delete this event project? This action cannot be undone.')) {
        router.delete(route('event-projects.destroy', props.eventProject.id));
    }
};

const downloadAttachment = () => {
    if (props.eventProject.attachment) {
        window.location.href = `/storage/${props.eventProject.attachment}`;
    }
};

const generated = ref(false);

const generateCashFlow = (id) => {
    if (confirm('Generate Cash Flow for this Event Project?')) {
        generated.value = true;
        router.post(route('event-projects.generate-cash-flow', id), {}, {
            onFinish: () => {
                generated.value = false;
            }
        });
    }
};
</script>

<template>
    <Head :title="`${eventProject.event_name} - Event Project`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ eventProject.event_name }}</h2>
                <div class="flex space-x-2">
                    <Link :href="route('event-projects.index')" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700">Back to List</Link>
                    <Link v-if="user.id === eventProject.user_id && !['approved', 'completed'].includes(eventProject.status)" :href="route('event-projects.edit', eventProject.id)" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">Edit</Link>
                    <button v-if="user.id === eventProject.user_id && !['approved', 'completed'].includes(eventProject.status)" @click="deleteEventProject" class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">Delete</button>
                    <!-- generate cash flow if approved/completed -->
                    <Link v-if="['approved', 'completed'].includes(eventProject.status) && !eventProject.cash_flow"  @click="generateCashFlow(eventProject.id)"  :disabled="generated" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">Generate Cash Flow</Link>
                    <div v-else-if="eventProject.cash_flow" class="px-4 py-2 bg-green-100 text-green-800 text-sm rounded-md">
                        Cash Flow Generated
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Event Overview Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <!-- Event Date & Status -->
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Event Date</label>
                                <p class="text-lg font-semibold text-gray-900">{{ formatDate(eventProject.event_date) }}</p>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Status</label>
                                <span :class="['inline-block px-3 py-1 rounded-full text-sm font-medium', getStatusBadgeClass(eventProject.status)]">
                                    {{ eventProject.status.charAt(0).toUpperCase() + eventProject.status.slice(1) }}
                                </span>
                                <div v-if="eventProject.rejection_reason" class="mt-2">
                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Alasan</label>
                                    <p class="text-gray-900">{{ eventProject.rejection_reason || '-' }}</p>
                                </div>
                            </div>

                            <!-- Location -->
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Location</label>
                                <p class="text-lg font-semibold text-gray-900">{{ eventProject.location || '-' }}</p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div v-if="eventProject.description" class="mb-4">
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-2">Description</label>
                            <p class="text-gray-700">{{ eventProject.description }}</p>
                        </div>

                        <!-- Note -->
                        <div v-if="eventProject.note" class="mb-4">
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-2">Note</label>
                            <p class="text-gray-700 bg-blue-50 p-3 rounded border border-blue-200">{{ eventProject.note }}</p>
                        </div>

                        <!-- Attachment -->
                        <div v-if="eventProject.attachment" class="mb-4">
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-2">Attachment</label>
                            <button @click="downloadAttachment" class="px-4 py-2 bg-indigo-100 text-indigo-700 text-sm rounded-md hover:bg-indigo-200">
                                📎 Download Attachment
                            </button>
                        </div>

                        <!-- Verification Info -->
                        <div v-if="eventProject.status === 'approved'" class="border-t pt-4 mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Approved By</label>
                                    <p class="text-gray-900">{{ eventProject.verificator?.name || 'System' }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Approved Date</label>
                                    <p class="text-gray-900">{{ formatDate(eventProject.verified_date) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Budget Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Budget Details</h3>

                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <p class="text-sm font-medium text-blue-600">Total Allocated</p>
                                <p class="text-2xl font-bold text-blue-900">{{ formatCurrency(totalAllocated()) }}</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                <p class="text-sm font-medium text-green-600">Total Approved</p>
                                <p class="text-2xl font-bold text-green-900">{{ formatCurrency(totalApproved()) }}</p>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                                <p class="text-sm font-medium text-purple-600">Difference</p>
                                <p class="text-2xl font-bold text-purple-900">{{ formatCurrency(totalAllocated() - totalApproved()) }}</p>
                            </div>
                        </div>

                        <!-- Budget Items Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget Item - Category</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Allocated</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Approved</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="detail in eventProject.details" :key="detail.id">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ detail.budget_item?.budget?.name || '-' }} [{{ detail.budget_item?.category?.name || '-' }}]</td>
                                        <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">{{ formatCurrency(detail.allocated_amount) }}</td>
                                        <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">{{ formatCurrency(detail.approved_amount) }}</td>
                                        <td class="px-6 py-4 text-sm text-right font-medium" :class="detail.allocated_amount > detail.approved_amount ? 'text-red-600' : 'text-green-600'">
                                            {{ formatCurrency(detail.allocated_amount - detail.approved_amount) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Cash Flow Information -->
                <div v-if="eventProject.cash_flow" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Linked Cash Flow</h3>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-600">Cash Flow: <span class="font-semibold text-gray-900">{{ eventProject.cash_flow?.name || 'Unknown' }}</span></p>
                            <p class="text-sm text-gray-600 mt-1">ID: <span class="font-mono text-gray-900">{{ eventProject.cash_flow?.id }}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
