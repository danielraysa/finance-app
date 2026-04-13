<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const props = defineProps({
    eventProjects: Object,
    filters: Object
});

const user = computed(() => page.props.auth.user);
const isVerificator = computed(() => user.value?.roles?.map((role) => role.name).includes('verificator'));

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const sortBy = ref(props.filters?.sort_by || 'event_date');
const sortDir = ref(props.filters?.sort_dir || 'desc');

let searchTimeout = null;

const applyFilters = () => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (statusFilter.value) params.append('status', statusFilter.value);
    if (dateFrom.value) params.append('date_from', dateFrom.value);
    if (dateTo.value) params.append('date_to', dateTo.value);
    if (sortBy.value) params.append('sort_by', sortBy.value);
    if (sortDir.value) params.append('sort_dir', sortDir.value);

    const url = route('event-projects.index') + (params.toString() ? ('?' + params.toString()) : '');
    window.location.href = url;
};

const debouncedSubmit = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
};

const resetFilters = () => {
    search.value = '';
    statusFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    sortBy.value = 'event_date';
    sortDir.value = 'desc';
    window.location.href = route('event-projects.index');
};

const toggleSortDir = () => {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    applyFilters();
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const confirmingApproval = ref(false);
const approvingId = ref(null);
const approvingLoading = ref(false);

const confirmingRejection = ref(false);
const rejectingId = ref(null);
const rejectingLoading = ref(false);
const rejectionReason = ref('');

const approveEventProject = (id) => {
    approvingId.value = id;
    confirmingApproval.value = true;
};

const closeApprovalModal = () => {
    confirmingApproval.value = false;
    approvingId.value = null;
    approvingLoading.value = false;
};

const confirmApproval = async () => {
    if (!approvingId.value) return;
    approvingLoading.value = true;

    try {
        router.post(route('event-projects.approval', approvingId.value), {}, {
            onStart: () => {
                approvingLoading.value = true;
            },
            onSuccess: () => {
                window.location.reload();
            },
            onError: () => {
                approvingLoading.value = false;
            },
            onFinish: () => {
                confirmingApproval.value = false;
                approvingId.value = null;
            },
        });
    } catch (e) {
        console.error(e);
        approvingLoading.value = false;
        confirmingApproval.value = false;
        approvingId.value = null;
    }
};

const rejectEventProject = (id) => {
    rejectingId.value = id;
    rejectionReason.value = '';
    confirmingRejection.value = true;
};

const closeRejectionModal = () => {
    confirmingRejection.value = false;
    rejectingId.value = null;
    rejectionReason.value = '';
    rejectingLoading.value = false;
};

const confirmRejection = async () => {
    if (!rejectingId.value || !rejectionReason.value.trim()) {
        alert('Please provide a rejection reason');
        return;
    }

    rejectingLoading.value = true;

    try {
        router.post(route('event-projects.reject', rejectingId.value), {
            rejection_reason: rejectionReason.value
        }, {
            onStart: () => {
                rejectingLoading.value = true;
            },
            onSuccess: () => {
                window.location.reload();
            },
            onError: () => {
                rejectingLoading.value = false;
            },
            onFinish: () => {
                confirmingRejection.value = false;
                rejectingId.value = null;
                rejectionReason.value = '';
            },
        });
    } catch (e) {
        console.error(e);
        rejectingLoading.value = false;
        confirmingRejection.value = false;
        rejectingId.value = null;
        rejectionReason.value = '';
    }
};
</script>

<template>
    <Head title="Event Projects" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Event Projects</h2>
                <Link :href="route('event-projects.create')" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">Add Event Project</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="(eventProjects.data || []).length === 0" class="text-center py-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Event Projects Found</h3>
                            <p class="text-gray-500 mb-4">You haven't created any event projects yet.</p>
                            <Link :href="route('event-projects.create')" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">Create Your First Event Project</Link>
                        </div>

                        <div v-else>
                            <!-- Filters Section -->
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
                                    <!-- Search Input -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                        <input v-model="search" @input="debouncedSubmit" type="text" placeholder="Name, location..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                    </div>

                                    <!-- Status Filter -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select v-model="statusFilter" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <option value="">All Status</option>
                                            <option value="planned">Planned</option>
                                            <option value="approved">Approved</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>

                                    <!-- Date From -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                                        <input v-model="dateFrom" @change="applyFilters" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                    </div>

                                    <!-- Date To -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                                        <input v-model="dateTo" @change="applyFilters" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                    </div>

                                    <!-- Sort By -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                        <select v-model="sortBy" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <option value="created_at">Created Date</option>
                                            <option value="event_date">Event Date</option>
                                            <option value="event_name">Event Name</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex justify-between items-center">
                                    <div class="flex space-x-2">
                                        <button @click="toggleSortDir" class="px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-100">
                                            Sort {{ sortDir === 'asc' ? '↑' : '↓' }}
                                        </button>
                                        <button @click="resetFilters" class="px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-100">
                                            Reset Filters
                                        </button>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Showing <strong>{{ eventProjects.from }}</strong> to <strong>{{ eventProjects.to }}</strong> of <strong>{{ eventProjects.total }}</strong> results
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allocated</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="eventProject in eventProjects.data" :key="eventProject.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ eventProject.event_name }}
                                                <p class="text-sm text-gray-500">{{ formatDate(eventProject.event_date) }}</p>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ eventProject.location || '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ eventProject.status }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatCurrency(eventProject.details_sum_allocated_amount || 0) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(eventProject.created_at) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <Link :href="route('event-projects.show', eventProject.id)">
                                                    <PrimaryButton class="mr-2">View</PrimaryButton>
                                                </Link>
                                                <Link v-if="eventProject.status == 'planned' && eventProject.user_id == user.id && eventProject.cashFlow == null" :href="route('event-projects.edit', eventProject.id)" class="text-indigo-600 hover:text-indigo-900">
                                                    <SecondaryButton class="mr-2">Edit</SecondaryButton>
                                                </Link>
                                                <button v-if="isVerificator && eventProject.status == 'planned'" @click="approveEventProject(eventProject.id)" class="transition ease-in-out duration-150 tracking-widest px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 mr-2">Approve</button>
                                                <button v-if="isVerificator && eventProject.status == 'planned'" @click="rejectEventProject(eventProject.id)" class="transition ease-in-out duration-150 tracking-widest px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">Reject</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-6" v-if="eventProjects.links.length > 3">
                                <div class="flex justify-center">
                                    <div class="flex space-x-1">
                                        <Link v-for="(link, i) in eventProjects.links" :key="i" v-html="link.label" :href="link.url" :class="[
                                            'px-4 py-2 text-sm rounded-md',
                                            {
                                                'bg-indigo-600 text-white': link.active,
                                                'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300': !link.active && link.url,
                                                'bg-gray-100 text-gray-500 cursor-not-allowed border border-gray-300': !link.url
                                            }
                                        ]" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

        <!-- Approval Confirmation Modal -->
        <Modal :show="confirmingApproval" @close="closeApprovalModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Approve Event Project</h2>
                <p class="mt-1 text-sm text-gray-600">Are you sure you want to approve this event project? This action will mark the project as approved.</p>
                <div class="mt-6 flex justify-end space-x-4">
                    <SecondaryButton @click="closeApprovalModal">Cancel</SecondaryButton>
                    <PrimaryButton @click="confirmApproval" :class="{ 'opacity-50 pointer-events-none': approvingLoading }" :disabled="approvingLoading">
                        <span v-if="!approvingLoading">Confirm Approval</span>
                        <span v-else>Approving...</span>
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Rejection Modal -->
        <Modal :show="confirmingRejection" @close="closeRejectionModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Reject Event Project</h2>
                <p class="mt-1 text-sm text-gray-600">Please provide a reason for rejecting this event project.</p>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                    <textarea v-model="rejectionReason" placeholder="Enter the reason for rejection..." rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-red-500" />
                    <p class="mt-1 text-xs text-gray-500">Minimum 10 characters required</p>
                </div>
                <div class="mt-6 flex justify-end space-x-4">
                    <SecondaryButton @click="closeRejectionModal">Cancel</SecondaryButton>
                    <button @click="confirmRejection" :class="['px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700', { 'opacity-50 pointer-events-none': rejectingLoading }]" :disabled="rejectingLoading || !rejectionReason.trim() || rejectionReason.trim().length < 10">
                        <span v-if="!rejectingLoading">Confirm Rejection</span>
                        <span v-else>Rejecting...</span>
                    </button>
                </div>
            </div>
        </Modal>
</template>
