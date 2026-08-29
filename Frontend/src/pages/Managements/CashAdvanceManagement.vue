<template>
    <div class="main">

        <!-- TOPBAR -->
        <div class="topbar">

            <div class="d-flex align-items-center gap-2">

                <button class="btn-menu d-lg-none" @click="$emit('toggle-sidebar')" aria-label="Toggle menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12h18M3 6h18M3 18h18" />
                    </svg>
                </button>

                <div>

                    <div class="eyebrow">
                        Payroll administration
                    </div>

                    <h1>
                        Cash Advance Management
                    </h1>

                </div>

            </div>


            <div class="d-flex align-items-center gap-3">

                <div class="clock-chip">

                    <span class="dot"></span>

                    <span>
                        {{ liveClock }}
                    </span>

                </div>

            </div>

        </div>


        <!-- CONTENT -->
        <div class="content">

            <!-- SUMMARY -->
            <div class="row g-3 mb-3">

                <!-- Pending -->
                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp gold">
                            PEND
                        </div>

                        <div class="stat-label">
                            Pending Requests
                        </div>

                        <div class="stat-value">
                            {{ pendingCount }}
                        </div>

                        <div class="stat-delta stat-delta--gold">
                            {{ formatCurrency(pendingAmount) }} awaiting review
                        </div>

                    </div>

                </div>


                <!-- Approved -->
                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp blue">
                            APPR
                        </div>

                        <div class="stat-label">
                            Approved Requests
                        </div>

                        <div class="stat-value">
                            {{ approvedCount }}
                        </div>

                        <div class="stat-delta stat-delta--blue">
                            {{ formatCurrency(approvedAmount) }} ready for payment
                        </div>

                    </div>

                </div>


                <!-- Paid -->
                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp green">
                            PAID
                        </div>

                        <div class="stat-label">
                            Paid Requests
                        </div>

                        <div class="stat-value">
                            {{ paidCount }}
                        </div>

                        <div class="stat-delta text-success">
                            {{ formatCurrency(paidAmount) }} this month
                        </div>

                    </div>

                </div>


                <!-- Total -->
                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp red">
                            SUM
                        </div>

                        <div class="stat-label">
                            Total Cash Advanced
                        </div>

                        <div class="stat-value stat-value-money">
                            {{ formatCurrency(totalAdvanced) }}
                        </div>

                        <div class="stat-delta stat-delta--red">
                            {{ cashAdvanceData.length }} requests on file
                        </div>

                    </div>

                </div>

            </div>


            <!-- REQUEST LIST -->
            <div class="panel">

                <div class="section-header">

                    <div>

                        <div class="section-title">
                            Cash advance requests
                        </div>

                        <div class="panel-sub">
                            Review employee requests and process payments
                        </div>

                    </div>

                </div>


                <!-- SEARCH / FILTER -->
                <div class="toolbar">

                    <div class="search-box">

                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-4-4" />
                        </svg>

                        <input v-model="searchQuery" type="text" placeholder="Search employee..." />

                    </div>


                    <div class="filter-row">

                        <button v-for="filter in statusFilters" :key="filter.value" class="filter-pill"
                            :class="{ active: activeFilter === filter.value }" @click="activeFilter = filter.value">
                            {{ filter.label }}
                        </button>

                    </div>

                </div>


                <!-- TABLE -->
                <div class="table-responsive">

                    <table v-if="filteredRequests.length" class="table-ledger">

                        <thead>

                            <tr>

                                <th>
                                    Employee
                                </th>

                                <th>
                                    Department
                                </th>

                                <th>
                                    Amount
                                </th>

                                <th>
                                    Request Date
                                </th>

                                <th>
                                    Reason
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr v-for="request in filteredRequests" :key="request.id">

                                <!-- EMPLOYEE -->
                                <td>

                                    <div class="employee-cell">

                                        <div class="avatar-sm">
                                            {{ request.initials }}
                                        </div>

                                        <div>

                                            <div class="emp-name">
                                                {{ request.employeeName }}
                                            </div>

                                            <div class="emp-position">
                                                {{ request.position }}
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <!-- DEPARTMENT -->
                                <td>

                                    <span class="department">
                                        {{ request.department }}
                                    </span>

                                </td>


                                <!-- AMOUNT -->
                                <td>

                                    <span class="money">
                                        {{ formatCurrency(request.amount) }}
                                    </span>

                                </td>


                                <!-- DATE -->
                                <td>

                                    <div class="date-text">
                                        {{ formatDate(request.requestDate) }}
                                    </div>

                                    <div class="date-sub">
                                        {{ request.requestTime }}
                                    </div>

                                </td>


                                <!-- REASON -->
                                <td>

                                    <div class="reason-text" :title="request.reason">
                                        {{ request.reason }}
                                    </div>

                                </td>


                                <!-- STATUS -->
                                <td>

                                    <span class="badge-status" :class="badgeClass(request.status)">
                                        {{ formatStatus(request.status) }}
                                    </span>

                                </td>


                                <!-- ACTION -->
                                <td>

                                    <div class="action-group">

                                        <button v-if="request.status === 'pending'" class="btn-mini btn-mini-review"
                                            @click="openReviewModal(request)">
                                            Review
                                        </button>


                                        <button v-if="request.status === 'approved'" class="btn-mini btn-mini-pay"
                                            @click="openPaymentModal(request)">
                                            Mark as Paid
                                        </button>


                                        <button v-if="
                                            request.status === 'paid' ||
                                            request.status === 'rejected'
                                        " class="btn-mini btn-mini-view" @click="openViewModal(request)">
                                            View
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>


                    <div v-else class="empty-state">

                        <div class="empty-icon">
                            🧾
                        </div>

                        <div class="empty-title">
                            No cash advance requests found
                        </div>

                        <div class="empty-sub">
                            Try changing your search or filters.
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
             REVIEW MODAL
        ====================================================== -->
        <div v-if="showReviewModal" class="modal-backdrop" @click.self="closeModals">

            <div class="employee-modal">

                <div class="modal-header">

                    <div>

                        <div class="modal-title">
                            Cash Advance Request
                        </div>

                        <div class="panel-sub">
                            Review employee request
                        </div>

                    </div>

                    <button class="modal-close" @click="closeModals">
                        ×
                    </button>

                </div>


                <div class="modal-body">

                    <!-- Employee -->
                    <div class="employee-profile">

                        <div class="avatar-lg">
                            {{ selectedRequest?.initials }}
                        </div>

                        <div>

                            <div class="profile-name">
                                {{ selectedRequest?.employeeName }}
                            </div>

                            <div class="profile-position">
                                {{ selectedRequest?.position }}
                            </div>

                            <div class="profile-department">
                                {{ selectedRequest?.department }}
                            </div>

                        </div>

                    </div>


                    <!-- Request Info -->
                    <div class="request-grid">

                        <div class="request-info">

                            <div class="info-label">
                                Requested Amount
                            </div>

                            <div class="info-value money">
                                {{ formatCurrency(selectedRequest?.amount || 0) }}
                            </div>

                        </div>


                        <div class="request-info">

                            <div class="info-label">
                                Request Date
                            </div>

                            <div class="info-value">
                                {{ formatDate(selectedRequest?.requestDate) }}
                            </div>

                        </div>


                        <div class="request-info">

                            <div class="info-label">
                                Payment Method
                            </div>

                            <div class="info-value">
                                {{ selectedRequest?.paymentMethod }}
                            </div>

                        </div>


                        <div class="request-info">

                            <div class="info-label">
                                Status
                            </div>

                            <div>

                                <span class="badge-status" :class="badgeClass(selectedRequest?.status)">
                                    {{ formatStatus(selectedRequest?.status) }}
                                </span>

                            </div>

                        </div>

                    </div>


                    <!-- Reason -->
                    <div class="reason-box">

                        <div class="info-label">
                            Reason for Request
                        </div>

                        <div class="reason-content">
                            {{ selectedRequest?.reason }}
                        </div>

                    </div>


                    <!-- Notes -->
                    <div class="form-group full">

                        <label>
                            Admin Notes
                        </label>

                        <textarea v-model="adminNotes" rows="3" placeholder="Optional notes..."></textarea>

                    </div>

                </div>


                <div class="modal-footer modal-footer--split">

                    <button type="button" class="btn btn-secondary-ledger" @click="closeModals">
                        Cancel
                    </button>

                    <div class="d-flex gap-2">

                        <button type="button" class="btn btn-danger-ledger" @click="rejectRequest">
                            Reject
                        </button>

                        <button type="button" class="btn btn-primary-ledger" @click="approveRequest">
                            Approve
                        </button>

                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
             PAYMENT MODAL
        ====================================================== -->
        <div v-if="showPaymentModal" class="modal-backdrop" @click.self="closeModals">

            <div class="employee-modal payment-modal">

                <div class="modal-header">

                    <div>

                        <div class="modal-title">
                            Process Cash Advance
                        </div>

                        <div class="panel-sub">
                            Confirm payment to employee
                        </div>

                    </div>

                    <button class="modal-close" @click="closeModals">
                        ×
                    </button>

                </div>


                <div class="modal-body">

                    <div class="payment-summary">

                        <div class="info-label">
                            Employee
                        </div>

                        <div class="payment-employee">
                            {{ selectedRequest?.employeeName }}
                        </div>


                        <div class="info-label mt-3">
                            Approved Amount
                        </div>

                        <div class="payment-amount">
                            {{ formatCurrency(selectedRequest?.amount || 0) }}
                        </div>


                        <div class="payment-method-box">

                            <div class="info-label">
                                Payment Method
                            </div>

                            <div class="info-value">
                                {{ selectedRequest?.paymentMethod }}
                            </div>

                        </div>


                        <div class="form-group full mt-3">

                            <label>
                                Payment Reference
                            </label>

                            <input v-model="paymentReference" type="text" placeholder="e.g. CA-2026-001" />

                        </div>


                        <div class="form-group full mt-3">

                            <label>
                                Payment Notes
                            </label>

                            <textarea v-model="paymentNotes" rows="3"
                                placeholder="Optional payment notes..."></textarea>

                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary-ledger" @click="closeModals">
                        Cancel
                    </button>

                    <button type="button" class="btn btn-success-ledger" @click="markAsPaid">
                        Confirm Payment
                    </button>

                </div>

            </div>

        </div>


        <!-- =====================================================
             VIEW MODAL
        ====================================================== -->
        <div v-if="showViewModal" class="modal-backdrop" @click.self="closeModals">

            <div class="employee-modal">

                <div class="modal-header">

                    <div>

                        <div class="modal-title">
                            Cash Advance Details
                        </div>

                        <div class="panel-sub">
                            Request information
                        </div>

                    </div>

                    <button class="modal-close" @click="closeModals">
                        ×
                    </button>

                </div>


                <div class="modal-body">

                    <div class="employee-profile">

                        <div class="avatar-lg">
                            {{ selectedRequest?.initials }}
                        </div>

                        <div>

                            <div class="profile-name">
                                {{ selectedRequest?.employeeName }}
                            </div>

                            <div class="profile-position">
                                {{ selectedRequest?.position }}
                            </div>

                            <div class="profile-department">
                                {{ selectedRequest?.department }}
                            </div>

                        </div>

                    </div>


                    <div class="details-list">

                        <div class="detail-row">
                            <span>Amount</span>
                            <strong>{{ formatCurrency(selectedRequest?.amount || 0) }}</strong>
                        </div>

                        <div class="detail-row">
                            <span>Request Date</span>
                            <strong>{{ formatDate(selectedRequest?.requestDate) }}</strong>
                        </div>

                        <div class="detail-row">
                            <span>Status</span>
                            <span class="badge-status" :class="badgeClass(selectedRequest?.status)">
                                {{ formatStatus(selectedRequest?.status) }}
                            </span>
                        </div>

                        <div class="detail-row">
                            <span>Payment Method</span>
                            <strong>{{ selectedRequest?.paymentMethod }}</strong>
                        </div>

                        <div v-if="selectedRequest?.paidDate" class="detail-row">
                            <span>Paid Date</span>
                            <strong>{{ formatDate(selectedRequest.paidDate) }}</strong>
                        </div>

                        <div v-if="selectedRequest?.paymentReference" class="detail-row">
                            <span>Payment Reference</span>
                            <strong>{{ selectedRequest.paymentReference }}</strong>
                        </div>

                    </div>


                    <div class="reason-box mt-3">
                        <div class="info-label">Reason</div>
                        <div class="reason-content">{{ selectedRequest?.reason }}</div>
                    </div>


                    <div v-if="selectedRequest?.adminNotes" class="reason-box mt-3">
                        <div class="info-label">Admin Notes</div>
                        <div class="reason-content">{{ selectedRequest.adminNotes }}</div>
                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary-ledger" @click="closeModals">
                        Close
                    </button>

                </div>

            </div>

        </div>

    </div>
</template>


<script setup>

import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref
} from 'vue'


defineOptions({
    name: 'CashAdvanceManagementPage'
})


defineEmits([
    'toggle-sidebar'
])


// =====================================================
// CLOCK
// =====================================================

const liveClock = ref('--:--:--')

let clockTimer = null


function tickClock() {

    liveClock.value =
        new Date().toLocaleTimeString(
            'en-US',
            {
                hour12: true
            }
        )

}


// =====================================================
// CASH ADVANCE DATA
// =====================================================

const cashAdvanceData = ref([

    {
        id: 1,
        employeeId: 1,
        employeeName: 'Jonas Diaz',
        initials: 'JD',
        department: 'Warehouse',
        position: 'Warehouse Staff',
        amount: 5000,
        requestDate: '2026-08-29',
        requestTime: '09:15 AM',
        reason: 'Emergency personal expense',
        paymentMethod: 'Cash',
        status: 'pending',
        adminNotes: null,
        paidDate: null,
        paymentReference: null
    },

    {
        id: 2,
        employeeId: 2,
        employeeName: 'Carla Santos',
        initials: 'CS',
        department: 'Accounting',
        position: 'Accountant',
        amount: 3000,
        requestDate: '2026-08-28',
        requestTime: '10:30 AM',
        reason: 'Medical and transportation expenses',
        paymentMethod: 'GCash',
        status: 'approved',
        adminNotes: 'Approved by HR.',
        paidDate: null,
        paymentReference: null
    },

    {
        id: 3,
        employeeId: 3,
        employeeName: 'Ramon Tan',
        initials: 'RT',
        department: 'Logistics',
        position: 'Logistics Staff',
        amount: 2000,
        requestDate: '2026-08-25',
        requestTime: '02:20 PM',
        reason: 'Family emergency',
        paymentMethod: 'Cash',
        status: 'paid',
        adminNotes: 'Approved and released.',
        paidDate: '2026-08-26',
        paymentReference: 'CA-2026-001'
    },

    {
        id: 4,
        employeeId: 4,
        employeeName: 'Paulo Lim',
        initials: 'PL',
        department: 'IT',
        position: 'IT Staff',
        amount: 7500,
        requestDate: '2026-08-24',
        requestTime: '08:45 AM',
        reason: 'Urgent household expenses',
        paymentMethod: 'Bank Transfer',
        status: 'rejected',
        adminNotes: 'Amount exceeds the current cash advance limit.',
        paidDate: null,
        paymentReference: null
    },

    {
        id: 5,
        employeeId: 5,
        employeeName: 'Nadia Ang',
        initials: 'NA',
        department: 'Human Resources',
        position: 'HR Assistant',
        amount: 4000,
        requestDate: '2026-08-23',
        requestTime: '11:10 AM',
        reason: 'Educational expenses',
        paymentMethod: 'GCash',
        status: 'paid',
        adminNotes: 'Payment completed.',
        paidDate: '2026-08-24',
        paymentReference: 'CA-2026-002'
    },

    {
        id: 6,
        employeeId: 6,
        employeeName: 'Erik Villar',
        initials: 'EV',
        department: 'Sales',
        position: 'Sales Representative',
        amount: 2500,
        requestDate: '2026-08-22',
        requestTime: '03:45 PM',
        reason: 'Transportation expenses',
        paymentMethod: 'Cash',
        status: 'pending',
        adminNotes: null,
        paidDate: null,
        paymentReference: null
    }

])


// =====================================================
// SEARCH / FILTER
// =====================================================

const searchQuery = ref('')

const activeFilter = ref('all')


const statusFilters = [
    { label: 'All', value: 'all' },
    { label: 'Pending', value: 'pending' },
    { label: 'Approved', value: 'approved' },
    { label: 'Paid', value: 'paid' },
    { label: 'Rejected', value: 'rejected' }
]


const filteredRequests = computed(() => {

    const search =
        searchQuery.value
            .toLowerCase()
            .trim()


    return cashAdvanceData.value.filter(request => {

        const matchesSearch =
            !search ||
            request.employeeName.toLowerCase().includes(search) ||
            request.department.toLowerCase().includes(search) ||
            request.reason.toLowerCase().includes(search)


        const matchesFilter =
            activeFilter.value === 'all' ||
            request.status === activeFilter.value


        return matchesSearch && matchesFilter

    })

})


// =====================================================
// SUMMARY
// =====================================================

const pendingRequests = computed(() =>
    cashAdvanceData.value.filter(request => request.status === 'pending')
)

const approvedRequests = computed(() =>
    cashAdvanceData.value.filter(request => request.status === 'approved')
)

const paidRequests = computed(() =>
    cashAdvanceData.value.filter(request => request.status === 'paid')
)


const pendingCount = computed(() => pendingRequests.value.length)

const approvedCount = computed(() => approvedRequests.value.length)

const paidCount = computed(() => paidRequests.value.length)


const pendingAmount = computed(() =>
    pendingRequests.value.reduce((total, request) => total + Number(request.amount || 0), 0)
)

const approvedAmount = computed(() =>
    approvedRequests.value.reduce((total, request) => total + Number(request.amount || 0), 0)
)

const paidAmount = computed(() =>
    paidRequests.value.reduce((total, request) => total + Number(request.amount || 0), 0)
)


const totalAdvanced = computed(() =>
    cashAdvanceData.value
        .filter(request => request.status === 'paid')
        .reduce((total, request) => total + Number(request.amount || 0), 0)
)


// =====================================================
// MODALS
// =====================================================

const showReviewModal = ref(false)

const showPaymentModal = ref(false)

const showViewModal = ref(false)


const selectedRequest = ref(null)


const adminNotes = ref('')

const paymentReference = ref('')

const paymentNotes = ref('')


function openReviewModal(request) {

    selectedRequest.value = request

    adminNotes.value = request.adminNotes || ''

    showReviewModal.value = true

}


function approveRequest() {

    if (!selectedRequest.value) return

    selectedRequest.value.status = 'approved'

    selectedRequest.value.adminNotes =
        adminNotes.value || 'Approved by admin.'

    showReviewModal.value = false

    selectedRequest.value = null

    adminNotes.value = ''

}


function rejectRequest() {

    if (!selectedRequest.value) return

    selectedRequest.value.status = 'rejected'

    selectedRequest.value.adminNotes =
        adminNotes.value || 'Request rejected by admin.'

    showReviewModal.value = false

    selectedRequest.value = null

    adminNotes.value = ''

}


function openPaymentModal(request) {

    selectedRequest.value = request

    paymentReference.value = generatePaymentReference()

    paymentNotes.value = ''

    showPaymentModal.value = true

}


function markAsPaid() {

    if (!selectedRequest.value) return

    selectedRequest.value.status = 'paid'

    selectedRequest.value.paidDate = getTodayDate()

    selectedRequest.value.paymentReference =
        paymentReference.value || generatePaymentReference()

    selectedRequest.value.adminNotes =
        paymentNotes.value || 'Payment completed by admin.'

    showPaymentModal.value = false

    selectedRequest.value = null

    paymentReference.value = ''

    paymentNotes.value = ''

}


function openViewModal(request) {

    selectedRequest.value = request

    showViewModal.value = true

}


function closeModals() {

    showReviewModal.value = false

    showPaymentModal.value = false

    showViewModal.value = false

    selectedRequest.value = null

}


// =====================================================
// STATUS
// =====================================================

function formatStatus(status) {

    const labels = {
        pending: 'PENDING',
        approved: 'APPROVED',
        paid: 'PAID',
        rejected: 'REJECTED'
    }

    return labels[status] || status

}


function badgeClass(status) {

    return {
        'badge-pending': status === 'pending',
        'badge-approved': status === 'approved',
        'badge-paid': status === 'paid',
        'badge-rejected': status === 'rejected'
    }

}


// =====================================================
// FORMATTING
// =====================================================

function formatCurrency(value) {

    return new Intl.NumberFormat(
        'en-PH',
        {
            style: 'currency',
            currency: 'PHP',
            minimumFractionDigits: 2
        }
    ).format(Number(value) || 0)

}


function formatDate(date) {

    if (!date) return '—'

    return new Date(date + 'T00:00:00').toLocaleDateString(
        'en-US',
        {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        }
    )

}


function getTodayDate() {

    const date = new Date()

    const year = date.getFullYear()

    const month = String(date.getMonth() + 1).padStart(2, '0')

    const day = String(date.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`

}


function generatePaymentReference() {

    const random = Math.floor(100 + Math.random() * 900)

    return `CA-2026-${random}`

}


// =====================================================
// LIFECYCLE
// =====================================================

onMounted(() => {

    tickClock()

    clockTimer = setInterval(tickClock, 1000)

})


onBeforeUnmount(() => {

    if (clockTimer) clearInterval(clockTimer)

})

</script>


<style scoped>
.main {
    flex: 1;
    min-width: 0;
}


/* TOPBAR */

.topbar {
    background: var(--paper-2, #FBFAF6);
    border-bottom: 1px solid var(--line, #DCD8CB);
    padding: 1rem 1.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.topbar h1 {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.4rem;
    margin: 0;
}

.eyebrow {
    font-size: .72rem;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--slate, #6B7280);
    font-weight: 600;
}

.btn-menu {
    border: 1px solid var(--line, #DCD8CB);
    background: var(--paper-2, #FBFAF6);
    color: var(--ink, #1C2B4A);
    border-radius: 8px;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

@media (min-width: 992px) {
    .d-lg-none {
        display: none;
    }
}


/* CLOCK */

.clock-chip {
    font-family: 'IBM Plex Mono', monospace;
    background: var(--ink, #1C2B4A);
    color: #F3DFA6;
    border-radius: 8px;
    padding: .5rem .9rem;
    font-size: .82rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}

.clock-chip .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--green, #2F8F5B);
    box-shadow: 0 0 0 3px rgba(47, 143, 91, .25);
}


/* CONTENT */

.content {
    padding: 1.75rem;
}


/* BUTTONS */

.btn {
    border-radius: 6px;
    padding: .5rem .9rem;
    border: 1px solid transparent;
    cursor: pointer;
    font-size: .8rem;
    font-weight: 600;
    font-family: inherit;
}

.btn-primary-ledger {
    background: var(--ink, #1C2B4A);
    color: #F3DFA6;
    border-color: var(--ink, #1C2B4A);
}

.btn-primary-ledger:hover {
    background: #28395E;
}

.btn-secondary-ledger {
    background: var(--paper-2, #FBFAF6);
    color: var(--ink-2, #28395E);
    border-color: var(--line, #DCD8CB);
}

.btn-secondary-ledger:hover {
    background: var(--paper, #F2F1EA);
}

.btn-danger-ledger {
    background: var(--red-bg, #F7E9E6);
    color: var(--red, #C24D3B);
    border-color: #E5B7AE;
}

.btn-danger-ledger:hover {
    background: #F3DBD5;
}

.btn-success-ledger {
    background: var(--green, #2F8F5B);
    color: #fff;
    border-color: var(--green, #2F8F5B);
}

.btn-success-ledger:hover {
    background: #277A4C;
}


/* STATS */

.punch-card {
    background: var(--paper-2, #FBFAF6);
    border: 1px solid var(--line, #DCD8CB);
    border-radius: 10px;
    position: relative;
    padding: 1.25rem 1.3rem 1.1rem;
    min-height: 145px;
}

.punch-card::before {
    content: "";
    position: absolute;
    top: -1px;
    left: 14px;
    right: 14px;
    height: 1px;
    background-image: radial-gradient(circle, var(--paper, #F2F1EA) 3px, transparent 3.2px);
    background-size: 16px 16px;
    background-position: 0 -8px;
    background-repeat: repeat-x;
}

.stat-label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--slate, #6B7280);
    font-weight: 600;
}

.stat-value {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 2.1rem;
    line-height: 1.15;
    margin-top: .5rem;
}

.stat-value-money {
    font-size: 1.55rem;
}

.stat-delta {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .72rem;
    margin-top: .35rem;
}

.stat-delta--gold {
    color: var(--gold-dark, #9C7726);
}

.stat-delta--blue {
    color: #426B8F;
}

.stat-delta--red {
    color: var(--red, #C24D3B);
}

.text-success {
    color: var(--green, #2F8F5B);
}


/* STAMPS */

.stamp {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 2px dashed;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'IBM Plex Mono', monospace;
    font-size: .58rem;
    font-weight: 600;
    transform: rotate(-8deg);
}

.stamp.gold {
    color: var(--gold-dark, #9C7726);
    border-color: var(--gold, #C79A3D);
}

.stamp.blue {
    color: #426B8F;
    border-color: #6D94B6;
}

.stamp.green {
    color: var(--green, #2F8F5B);
    border-color: var(--green, #2F8F5B);
}

.stamp.red {
    color: var(--red, #C24D3B);
    border-color: #E5B7AE;
}


/* PANEL */

.panel {
    background: var(--paper-2, #FBFAF6);
    border: 1px solid var(--line, #DCD8CB);
    border-radius: 10px;
    padding: 1.3rem 1.4rem;
}

.section-title {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: .15rem;
}

.panel-sub {
    font-size: .78rem;
    color: var(--slate, #6B7280);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.2rem;
}


/* TOOLBAR */

.toolbar {
    display: flex;
    gap: .7rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.search-box {
    flex: 1;
    min-width: 220px;
    display: flex;
    align-items: center;
    gap: .5rem;
    border: 1px solid var(--line, #DCD8CB);
    background: var(--paper-2, #FBFAF6);
    border-radius: 7px;
    padding: .55rem .7rem;
    color: var(--slate, #6B7280);
}

.search-box input {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    color: var(--ink, #1C2B4A);
    font-size: .8rem;
}

.filter-row {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
}

.filter-pill {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .72rem;
    font-weight: 600;
    padding: .4rem .75rem;
    border-radius: 20px;
    border: 1px solid var(--line, #DCD8CB);
    background: var(--paper-2, #FBFAF6);
    color: var(--slate, #6B7280);
    cursor: pointer;
    transition: all .15s ease;
}

.filter-pill:hover {
    background: var(--paper, #F2F1EA);
}

.filter-pill.active {
    background: var(--ink, #1C2B4A);
    color: #F3DFA6;
    border-color: var(--ink, #1C2B4A);
}


/* TABLE */

.table-responsive {
    overflow-x: auto;
}

.table-ledger {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}

.table-ledger thead th {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .09em;
    color: var(--slate, #6B7280);
    border-bottom: 1px solid var(--line, #DCD8CB);
    font-weight: 600;
    padding: .5rem .5rem .65rem;
    text-align: left;
    white-space: nowrap;
}

.table-ledger tbody td {
    padding: .8rem .5rem;
    border-bottom: 1px dashed var(--line, #DCD8CB);
    vertical-align: middle;
    font-size: .82rem;
}

.table-ledger tbody tr:last-child td {
    border-bottom: none;
}


/* EMPLOYEE CELL */

.employee-cell {
    display: flex;
    align-items: center;
    gap: .65rem;
    min-width: 190px;
}

.avatar-sm {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--amber-bg, #F6EEDB);
    color: var(--gold-dark, #9C7726);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: .78rem;
    flex-shrink: 0;
}

.emp-name {
    font-weight: 600;
}

.emp-position {
    font-size: .68rem;
    color: var(--slate, #6B7280);
    margin-top: .1rem;
}

.department {
    font-size: .78rem;
    color: var(--ink-2, #28395E);
}

.money {
    font-family: 'IBM Plex Mono', monospace;
    font-weight: 600;
    font-size: .82rem;
}

.date-text {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .73rem;
    white-space: nowrap;
}

.date-sub {
    font-size: .65rem;
    color: var(--slate, #6B7280);
    margin-top: .1rem;
}

.reason-text {
    max-width: 190px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--slate, #6B7280);
    font-size: .78rem;
}


/* STATUS BADGES */

.badge-status {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .62rem;
    font-weight: 600;
    padding: .3rem .55rem;
    border-radius: 5px;
    letter-spacing: .03em;
    display: inline-block;
}

.badge-pending {
    background: var(--amber-bg, #F6EEDB);
    color: var(--gold-dark, #9C7726);
}

.badge-approved {
    background: #E8EEF8;
    color: #426B8F;
}

.badge-paid {
    background: var(--green-bg, #E5F2EA);
    color: var(--green, #2F8F5B);
}

.badge-rejected {
    background: var(--red-bg, #F7E9E6);
    color: var(--red, #C24D3B);
}


/* ACTIONS */

.action-group {
    display: flex;
    gap: .4rem;
    white-space: nowrap;
}

.btn-mini {
    border-radius: 6px;
    padding: .38rem .65rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: .68rem;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid transparent;
    transition: .15s ease;
}

.btn-mini-review {
    background: var(--amber-bg, #F6EEDB);
    color: var(--gold-dark, #9C7726);
    border-color: #E7D9B5;
}

.btn-mini-review:hover {
    background: #EFE1BD;
}

.btn-mini-pay {
    background: var(--green-bg, #E5F2EA);
    color: var(--green, #2F8F5B);
    border-color: #C8DFCf;
}

.btn-mini-pay:hover {
    background: #D7E9DB;
}

.btn-mini-view {
    background: #E8EEF8;
    color: #426B8F;
    border-color: #CBD7EA;
}

.btn-mini-view:hover {
    background: #DDE6F4;
}


/* EMPTY */

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--slate, #6B7280);
}

.empty-icon {
    font-size: 2rem;
}

.empty-title {
    font-family: 'Fraunces', serif;
    color: var(--ink, #1C2B4A);
    font-size: 1.05rem;
    font-weight: 600;
    margin-top: .5rem;
}

.empty-sub {
    font-size: .75rem;
    margin-top: .2rem;
}


/* MODAL */

.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(28, 43, 74, .42);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 1000;
}

.employee-modal {
    width: min(650px, 100%);
    max-height: 90vh;
    overflow-y: auto;
    background: var(--paper-2, #FBFAF6);
    border: 1px solid var(--line, #DCD8CB);
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(28, 43, 74, .18);
}

.payment-modal {
    width: min(550px, 100%);
}

.modal-header {
    padding: 1.2rem 1.3rem;
    border-bottom: 1px solid var(--line, #DCD8CB);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.modal-title {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.2rem;
}

.modal-close {
    border: none;
    background: transparent;
    color: var(--slate, #6B7280);
    font-size: 1.6rem;
    line-height: 1;
    cursor: pointer;
}

.modal-close:hover {
    color: var(--ink, #1C2B4A);
}

.modal-body {
    padding: 1.3rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: .6rem;
    padding: 1.3rem;
    border-top: 1px solid var(--line, #DCD8CB);
}

.modal-footer--split {
    justify-content: space-between;
}


/* EMPLOYEE PROFILE */

.employee-profile {
    display: flex;
    align-items: center;
    gap: .8rem;
    padding-bottom: 1.1rem;
    border-bottom: 1px dashed var(--line, #DCD8CB);
    margin-bottom: 1.1rem;
}

.avatar-lg {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 50%;
    background: var(--amber-bg, #F6EEDB);
    color: var(--gold-dark, #9C7726);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1rem;
}

.profile-name {
    font-family: 'Fraunces', serif;
    font-size: .95rem;
    font-weight: 600;
}

.profile-position {
    font-size: .72rem;
    color: var(--slate, #6B7280);
}

.profile-department {
    font-size: .68rem;
    color: var(--slate, #6B7280);
    margin-top: .1rem;
}


/* REQUEST GRID */

.request-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1.1rem;
}

.request-info {
    padding: .8rem;
    background: var(--paper, #F2F1EA);
    border-radius: 7px;
}

.info-label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .63rem;
    font-weight: 600;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: var(--slate, #6B7280);
    margin-bottom: .3rem;
}

.info-value {
    font-size: .82rem;
    font-weight: 600;
    color: var(--ink, #1C2B4A);
}


/* REASON */

.reason-box {
    padding: .85rem;
    background: var(--paper, #F2F1EA);
    border-radius: 7px;
    margin-bottom: 1.1rem;
}

.reason-content {
    font-size: .82rem;
    line-height: 1.5;
    color: var(--ink, #1C2B4A);
}


/* FORM */

.form-group {
    display: flex;
    flex-direction: column;
    gap: .35rem;
}

.form-group label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .63rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--slate, #6B7280);
    font-weight: 600;
}

.form-group input,
.form-group textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid var(--line, #DCD8CB);
    background: var(--paper-2, #FBFAF6);
    color: var(--ink, #1C2B4A);
    border-radius: 7px;
    padding: .6rem .7rem;
    font-size: .8rem;
    outline: none;
    font-family: inherit;
    resize: vertical;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: var(--gold, #C79A3D);
    box-shadow: 0 0 0 3px rgba(199, 154, 61, .12);
}


/* PAYMENT SUMMARY */

.payment-employee {
    font-family: 'Fraunces', serif;
    font-size: 1.05rem;
    font-weight: 600;
    margin-top: .25rem;
}

.payment-amount {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 1.9rem;
    font-weight: 600;
    margin-top: .3rem;
    color: var(--ink, #1C2B4A);
}

.payment-method-box {
    margin-top: 1.2rem;
    padding: .8rem;
    background: var(--paper, #F2F1EA);
    border-radius: 7px;
}


/* DETAILS LIST */

.details-list {
    margin-bottom: 1.1rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: .7rem 0;
    border-bottom: 1px dashed var(--line, #DCD8CB);
    font-size: .82rem;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row>span:first-child {
    color: var(--slate, #6B7280);
}


/* LAYOUT UTILITIES */

.d-flex {
    display: flex;
}

.align-items-center {
    align-items: center;
}

.gap-2 {
    gap: .5rem;
}

.gap-3 {
    gap: 1rem;
}

.mb-3 {
    margin-bottom: 1rem;
}

.mt-3 {
    margin-top: 1rem;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -.5rem;
}

.row>[class*="col-"] {
    padding: 0 .5rem;
}

.g-3>* {
    padding: .5rem;
}

.col-6 {
    width: 50%;
}

@media (min-width: 992px) {
    .col-lg-3 {
        width: 25%;
    }
}

@media (max-width: 991px) {
    .col-6 {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .content {
        padding: 1rem;
    }

    .topbar {
        padding: 1rem;
    }

    .request-grid {
        grid-template-columns: 1fr;
    }

    .search-box {
        min-width: 100%;
    }

    .reason-text {
        max-width: 120px;
    }

    .modal-footer--split {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .modal-footer--split>.d-flex {
        justify-content: stretch;
    }

    .modal-footer--split .btn {
        flex: 1;
    }
}
</style>