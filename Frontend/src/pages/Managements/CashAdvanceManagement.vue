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
                            {{ formatCurrency(pendingBalanace) }} awaiting review
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
                            {{ formatCurrency(approveBalance) }} ready for payment
                        </div>

                    </div>

                </div>
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
                    <div class="">
                        <button class="btn-mini btn-mini-approve" @click="router.push({ name: 'cashAdvance' })">
                            New CA
                        </button>
                    </div>
                </div>
                <div class="toolbar">
                    <div class="search-box">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-4-4" />
                        </svg>
                        <input v-model="searchQuery" type="text" placeholder="Search employee..." />
                    </div>
                    <div class="filter-row">
                        <button v-for="filter in statusFilters" :key="filter.value" class="filter-pill" :class="{ active: activeFilter === filter.value }" @click="activeFilter = filter.value">
                            {{ filter.label }}
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table v-if="filteredRequests.length" class="table-ledger">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Amount.</th>
                                <th>Request Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="request in filteredRequests" :key="request.id">
                                <td>
                                    <div class="employee-cell">
                                        <div class="avatar-sm">
                                            <img :src="storageImage(request.employee.image)" style="object-fit: cover; border-radius: 50%; border: 1px dashed gray;" width="45" height="45" alt="">
                                        </div>
                                        <div>
                                            <div class="emp-name">
                                                {{ request.employee.last_name }}, {{ request.employee.first_name }}
                                            </div>
                                            <div class="emp-position">
                                                {{ request.employee.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="date-text">
                                        {{ request.employee.phone_number }}
                                    </div>
                                    <div class="date-sub">
                                        {{ request.employee.location }}
                                    </div>
                                </td>
                                <td v-if="request.payment_type === 'Installment'">
                                    <span class="money">
                                        {{ formatCurrency(request.installment_amount) }}
                                    </span>
                                    <div class="date-sub">
                                        Total Balance: {{ formatCurrency(request.installment_amount * request.installment_count) }}
                                    </div>
                                </td>
                                <td v-if="request.payment_type === 'Custom'">
                                    <span class="money">
                                        {{ formatCurrency(request.custom_amount) }}
                                    </span>
                                    <div class="date-sub">
                                        Total Balance: {{ formatCurrency(request.balance) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="date-text">
                                        {{ formatDate(request.requested_date) }}
                                    </div>
                                    <div class="date-sub">
                                        {{ formatDateTime(request.created_at) }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-status" :class="badgeClass(request.status)">
                                        {{ formatStatus(request.status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <button v-if="request.status === 'Pending'" class="btn-mini btn-mini-review" @click="openReviewModal(request)">
                                            Review
                                        </button>
                                        <button v-if="request.status === 'Deducted/Paid' || request.status === 'Rejected' || request.status === 'Approved'" class="btn-mini btn-mini-view" @click="openViewModal(request)">
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
                    <div class="employee-profile">
                        <div class="avatar-lg">
                            <img :src="storageImage(selectedRequest.employee.image)" style="object-fit: cover; border-radius: 50%; border: 1px dashed gray;" width="45" height="45" alt="">
                        </div>
                        <div class="profile-main">
                            <div class="profile-name-row">
                                <div class="profile-name">
                                    {{ selectedRequest.employee.last_name }}, {{ selectedRequest.employee.first_name }}
                                </div>
                                <span class="badge-type" :class="selectedRequest?.payment_type === 'Custom' ? 'badge-type--custom' : 'badge-type--installment'">
                                    {{ selectedRequest?.payment_type }}
                                </span>
                            </div>
                            <div class="profile-position">
                                {{ selectedRequest.employee.phone_number }}
                            </div>
                            <div class="profile-department">
                                {{ selectedRequest.employee.location }}
                            </div>
                        </div>
                    </div>
                    <div class="request-grid">
                        <div class="request-info">
                            <div class="info-label">
                                Amount Requested
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
                                {{ formatDate(selectedRequest?.requested_date) }} - {{ formatDateTime(selectedRequest?.created_at) }}
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

                    <!-- Type-specific deduction plan, so the admin knows what they're approving -->
                    <div v-if="selectedRequest?.payment_type === 'Installment'" class="plan-box">
                        <div class="info-label">Deduction Plan</div>
                        <div class="plan-row">
                            <span>Per payroll</span>
                            <strong>{{ formatCurrency(selectedRequest?.installment_amount || 0) }}</strong>
                        </div>
                        <div class="plan-row">
                            <span>Number of payrolls</span>
                            <strong>{{ selectedRequest?.installment_count || 0 }}</strong>
                        </div>
                        <p class="plan-note">
                            Deducted automatically every payroll cutoff until fully paid — no further action needed after approval.
                        </p>
                    </div>
                    <div v-else class="plan-box">
                        <div class="info-label">Deduction Plan</div>
                        <div class="plan-row">
                            <span>First deduction</span>
                            <strong>{{ formatCurrency(selectedRequest?.custom_amount || selectedRequest?.amount || 0) }}</strong>
                        </div>
                        <div class="plan-row">
                            <span>Target cutoff</span>
                            <strong>{{ selectedRequest?.target_cutoff_start ? formatDate(selectedRequest.target_cutoff_start) : 'Not set yet' }}</strong>
                        </div>
                        <p class="plan-note">
                            Only deducts once, on the targeted cutoff. If the amount doesn't cover the full request, you'll need to set the next deduction manually after this one clears.
                        </p>
                    </div>

                    <div class="reason-box">
                        <div class="info-label">
                            Reason for Request
                        </div>
                        <div class="reason-content">
                            {{ selectedRequest?.reason }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer--split">
                    <button type="button" class="btn btn-secondary-ledger" @click="closeModals">
                        Cancel
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-danger-ledger" @click="rejectRequest(selectedRequest)">
                            Reject
                        </button>
                        <button type="button" class="btn btn-primary-ledger" @click="approveRequest(selectedRequest)">
                            Approve
                        </button>
                    </div>
                </div>
            </div>
        </div>

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
                            {{ selectedRequest.employee.last_name }}, {{ selectedRequest.employee.first_name }}
                        </div>
                        <div class="info-label mt-3">
                            Approved Amount/Balance
                        </div>
                        <div class="payment-amount">
                            {{ formatCurrency(selectedRequest?.balance || 0) }}
                        </div>
                        <div class="form-group full mt-3">
                            <label>
                                Payment Reference
                            </label>
                            <input v-model="paymentReference" type="text" placeholder="e.g. CA-2026-001" />
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

        <!-- VIEW / DETAILS MODAL — redesigned -->
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
                            <img :src="storageImage(selectedRequest.employee.image)" style="object-fit: cover; border-radius: 50%; border: 1px dashed gray;" width="45" height="45" alt="">
                        </div>
                        <div class="profile-main">
                            <div class="profile-name-row">
                                <div class="profile-name">
                                    {{ selectedRequest.employee.last_name }}, {{ selectedRequest.employee.first_name }}
                                </div>
                                <span class="badge-type" :class="selectedRequest?.payment_type === 'Custom' ? 'badge-type--custom' : 'badge-type--installment'">
                                    {{ selectedRequest?.payment_type }}
                                </span>
                            </div>
                            <div class="profile-position">
                                {{ selectedRequest?.employee.phone_number }}
                            </div>
                            <div class="profile-department">
                                {{ selectedRequest?.employee.location }}
                            </div>
                        </div>
                    </div>

                    <!-- Balance ledger strip -->
                    <div class="ledger-strip">
                        <div class="ledger-cell">
                            <div class="info-label">Requested</div>
                            <div class="ledger-value">{{ formatCurrency(selectedRequest?.amount || 0) }}</div>
                        </div>
                        <div class="ledger-divider">−</div>
                        <div class="ledger-cell">
                            <div class="info-label">Already Deducted</div>
                            <div class="ledger-value">{{ formatCurrency((selectedRequest?.amount || 0) - (selectedRequest?.balance || 0)) }}</div>
                        </div>
                        <div class="ledger-divider">=</div>
                        <div class="ledger-cell ledger-cell--highlight">
                            <div class="info-label">Balance Remaining</div>
                            <div class="ledger-value ledger-value--big">{{ formatCurrency(selectedRequest?.balance || 0) }}</div>
                        </div>
                    </div>

                    <!-- Installment: read-only, auto-deducting -->
                    <div v-if="selectedRequest?.payment_type === 'Installment'" class="auto-note">
                        <span class="auto-note-icon">↻</span>
                        <div>
                            <div class="auto-note-title">Deducts automatically every payroll</div>
                            <div class="auto-note-sub">
                                {{ formatCurrency(selectedRequest?.installment_amount || 0) }} per cutoff ·
                                {{ selectedRequest?.installment_count || 0 }} payroll{{ selectedRequest?.installment_count === 1 ? '' : 's' }} remaining.
                                No manual action needed.
                            </div>
                        </div>
                    </div>

                    <!-- Custom: set next targeted deduction -->
                    <div v-else-if="selectedRequest?.status !== 'Rejected'" class="deduction-set-box">
                        <div class="info-label">Set Next Deduction</div>
                        <p class="deduction-set-sub">
                            Custom deductions only happen once, on the exact payroll cutoff you target below. Pick how much to take and which cutoff it should apply to.
                        </p>

                        <div v-if="selectedRequest?.balance <= 0" class="fully-paid-note">
                            This cash advance is fully paid off — nothing left to schedule.
                        </div>

                        <template v-else>
                            <div class="deduction-fields">
                                <div class="form-group">
                                    <label>Amount to deduct</label>
                                    <input type="number" min="1" :max="selectedRequest.balance" step="1" v-model.number="nextDeductionAmount" />
                                    <div v-if="nextDeductionAmount > selectedRequest.balance" class="field-hint field-hint--error">
                                        Can't exceed the remaining balance of {{ formatCurrency(selectedRequest.balance) }}.
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Target payroll cutoff</label>
                                    <input type="date" v-model="nextDeductionDate" @change="validateCutoffDate" />
                                    <div v-if="cutoffDateError" class="field-hint field-hint--error">
                                        {{ cutoffDateError }}
                                    </div>
                                    <div v-else-if="nextDeductionDate" class="field-hint">
                                        Confirmed payroll cutoff date.
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="details-list">
                        <div class="detail-row">
                            <span>Request Date</span>
                            <strong>{{ formatDate(selectedRequest?.requested_date) }}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Status</span>
                            <span class="badge-status" :class="badgeClass(selectedRequest?.status)">
                                {{ formatStatus(selectedRequest?.status) }}
                            </span>
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
                    <button v-if="selectedRequest?.payment_type === 'Custom' && selectedRequest?.balance > 0" type="button" class="btn btn-primary-ledger" :disabled="!canSetDeduction" @click="setDeduction">
                        Set Deduction
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import router from '@/router';
import { useCashAdvanceStore } from '@/stores/useCashAdvance'
import { storageImage } from '@/utils/image';
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
    watch
} from 'vue'

defineOptions({
    name: 'CashAdvanceManagementPage'
})

defineEmits([
    'toggle-sidebar'
])

const cashAdvanceStore = useCashAdvanceStore();
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

const cashAdvanceData = ref([]);
const searchQuery = ref('')
const activeFilter = ref('all')
const statusFilters = [
    { label: 'All', value: 'all' },
    { label: 'Pending', value: 'Pending' },
    { label: 'Approved', value: 'Approved' },
    { label: 'Paid', value: 'Deducted/Paid' },
    { label: 'Rejected', value: 'Rejected' }
]
const filteredRequests = computed(() => {
    const search = searchQuery.value.toLowerCase().trim()

    return cashAdvanceData.value.filter(request => {
        const firstName = request.employee.first_name?.toLowerCase() ?? ''
        const lastName = request.employee.last_name?.toLowerCase() ?? ''
        const fullName = `${firstName} ${lastName}`

        const matchesSearch =
            !search ||
            firstName.includes(search) ||
            lastName.includes(search) ||
            fullName.includes(search) 

        const matchesFilter =
            activeFilter.value === 'all' ||
            request.status === activeFilter.value

        return matchesSearch && matchesFilter
    })
})

const pendingRequests = computed(() =>
    cashAdvanceData.value.filter(request => request.status === 'Pending')
)

const approvedRequests = computed(() =>
    cashAdvanceData.value.filter(request => request.status === 'Approved')
)

const paidRequests = computed(() =>
    cashAdvanceData.value.filter(request => request.status === 'paidDeducted/Paid')
)

const pendingCount = computed(() => pendingRequests.value.length)

const approvedCount = computed(() => approvedRequests.value.length)

const paidCount = computed(() => paidRequests.value.length)

const pendingBalanace = computed(() =>
    pendingRequests.value.reduce((total, request) => total + Number(request.balance || 0), 0)
)

const approveBalance = computed(() =>
    approvedRequests.value.reduce((total, request) => total + Number(request.balance || 0), 0)
)

const paidAmount = computed(() =>
    paidRequests.value.reduce((total, request) => total + Number(request.amount || 0), 0)
)

const totalAdvanced = computed(() =>
    cashAdvanceData.value
        .filter(request => request.status === 'paid')
        .reduce((total, request) => total + Number(request.amount || 0), 0)
)

const showReviewModal = ref(false)

const showPaymentModal = ref(false)

const showViewModal = ref(false)

const selectedRequest = ref(null)

const adminNotes = ref('')

const paymentReference = ref('')

const paymentNotes = ref('')

// --- Set-deduction state (Custom CAs only) ---
const nextDeductionAmount = ref(null)
const nextDeductionDate = ref('')
const cutoffDateError = ref('')

function validateCutoffDate() {

    cutoffDateError.value = ''

    if (!nextDeductionDate.value) return

    // Payroll cutoffs start on Sunday under the current schedule
    // (payout Saturday minus 6 days). Reject anything else up front
    // so a mismatched target_cutoff_start can't be saved.
    const parsed = new Date(nextDeductionDate.value + 'T00:00:00')

    if (parsed.getDay() !== 0) {
        cutoffDateError.value = 'This date is not a confirmed payroll cutoff date. Cutoffs start on Sunday.'
    }
}

const canSetDeduction = computed(() => {
    if (!selectedRequest.value) return false
    if (!nextDeductionAmount.value || nextDeductionAmount.value <= 0) return false
    if (nextDeductionAmount.value > selectedRequest.value.balance) return false
    if (!nextDeductionDate.value) return false
    if (cutoffDateError.value) return false
    return true
})

// Reset the set-deduction fields whenever a different request is opened
watch(selectedRequest, (val) => {
    nextDeductionAmount.value = val?.balance || null
    nextDeductionDate.value = ''
    cutoffDateError.value = ''
})

function openReviewModal(request) {
    selectedRequest.value = request
    adminNotes.value = request.adminNotes || ''
    showReviewModal.value = true
}

const approveRequest = async (selectedRequest) => {
    if (!selectedRequest) return
    await cashAdvanceStore.reviewCashAdvance({
        "cash_advance_id": selectedRequest.id,
        "status": "Approved",
    });
    getCashAdvances({
        "employee_id": null,
        "status": null,
        "per_page": 100,
    });
    showReviewModal.value = false
}

const rejectRequest = async (selectedRequest) => {
    if (!selectedRequest) return
    await cashAdvanceStore.reviewCashAdvance({
        "cash_advance_id": selectedRequest.id,
        "status": "Rejected",
    });
    getCashAdvances({
        "employee_id": null,
        "status": null,
        "per_page": 100,
    });
    showReviewModal.value = false
}

function openPaymentModal(request) {
    selectedRequest.value = request
    paymentReference.value = generatePaymentReference()
    paymentNotes.value = ''
    showPaymentModal.value = true
}

const markAsPaid = async () => {

    if (!selectedRequest.value) return

    // selectedRequest.value.status = 'Deducted/Paid'

    selectedRequest.value.paidDate = getTodayDate();

    await cashAdvanceStore.reviewCashAdvance({
        "cash_advance_id": selectedRequest.value.id,
        "status": "Deducted/Paid",
    });
    getCashAdvances({
        "employee_id": null,
        "status": null,
        "per_page": 100,
    });

    // selectedRequest.value.paymentReference =
    //     paymentReference.value || generatePaymentReference()

    // selectedRequest.value.adminNotes =
    //     paymentNotes.value || 'Payment completed by admin.'

    showPaymentModal.value = false

    selectedRequest.value = null
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

const setDeduction = async () => {

    if (!canSetDeduction.value) return

    // Writes to custom_amount / target_cutoff_start on the CA — NOT
    // the original `amount` field. Backend endpoint needs to accept
    // both fields (the old call only ever sent amount_deducted).
    await cashAdvanceStore.nextDeduction({
        cash_advance_id: selectedRequest.value.id,
        custom_amount: nextDeductionAmount.value,
        target_cutoff_start: nextDeductionDate.value,
    });

    getCashAdvances({
        "employee_id": null,
        "status": null,
        "per_page": 100,
    });

    closeModals()
}

function formatStatus(status) {

    const labels = {
        "Pending": 'PENDING',
        "Approved": 'APPROVED',
        "Deducted/Paid": 'PAID',
        "Rejected": 'REJECTED'
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

function formatDateTime(date) {
    if (!date) return '—';

    return new Date(date).toLocaleString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
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

const getCashAdvances = async (data) => {
    const caLists = await cashAdvanceStore.getCashAdvances(data);
    cashAdvanceData.value = caLists.data;
}

onMounted(() => {

    tickClock()

    clockTimer = setInterval(tickClock, 1000)

    getCashAdvances({
        "employee_id": null,
        "status": null,
        "per_page": 100,
    });

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

.btn:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.btn-primary-ledger {
    background: var(--ink, #1C2B4A);
    color: #F3DFA6;
    border-color: var(--ink, #1C2B4A);
}

.btn-primary-ledger:hover:not(:disabled) {
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


/* TYPE BADGE (view modal) */

.badge-type {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .6rem;
    font-weight: 600;
    padding: .25rem .5rem;
    border-radius: 5px;
    letter-spacing: .03em;
    display: inline-block;
    white-space: nowrap;
}

.badge-type--installment {
    background: var(--amber-bg, #F6EEDB);
    color: var(--gold-dark, #9C7726);
}

.badge-type--custom {
    background: #E4E9F4;
    color: var(--ink-2, #28395E);
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

.btn-mini-approve {
    background: #5087fd;
    color: white;
    width: 90px;
    border-color: #E7D9B5;
}

.btn-mini-approve:hover {
    background: #bdd2ff;
    color: rgb(56, 56, 56);
    border-color: #a5a5a5;
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

.profile-main {
    min-width: 0;
    flex: 1;
}

.profile-name-row {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: wrap;
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


/* LEDGER STRIP (view modal balance breakdown) */

.ledger-strip {
    display: grid;
    grid-template-columns: 1fr auto 1fr auto 1fr;
    align-items: center;
    gap: .6rem;
    background: var(--paper, #F2F1EA);
    border-radius: 8px;
    padding: .9rem 1rem;
    margin-bottom: 1.1rem;
}

.ledger-cell {
    min-width: 0;
}

.ledger-cell--highlight .ledger-value {
    color: var(--ink, #1C2B4A);
}

.ledger-divider {
    font-family: 'IBM Plex Mono', monospace;
    color: var(--slate, #6B7280);
    font-size: .85rem;
}

.ledger-value {
    font-family: 'IBM Plex Mono', monospace;
    font-weight: 600;
    font-size: .82rem;
    color: var(--ink-2, #28395E);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ledger-value--big {
    font-size: 1rem;
}


/* AUTO-DEDUCT NOTE (installment) */

.auto-note {
    display: flex;
    gap: .7rem;
    align-items: flex-start;
    background: var(--amber-bg, #F6EEDB);
    border: 1px solid #E7D9B5;
    border-radius: 8px;
    padding: .8rem .9rem;
    margin-bottom: 1.1rem;
}

.auto-note-icon {
    font-size: 1.1rem;
    line-height: 1;
    color: var(--gold-dark, #9C7726);
}

.auto-note-title {
    font-weight: 600;
    font-size: .82rem;
    color: var(--ink, #1C2B4A);
}

.auto-note-sub {
    font-size: .74rem;
    color: var(--slate, #6B7280);
    margin-top: .15rem;
    line-height: 1.4;
}


/* DEDUCTION PLAN BOX (review modal) */

.plan-box {
    background: var(--paper, #F2F1EA);
    border-radius: 8px;
    padding: .85rem 1rem;
    margin-bottom: 1.1rem;
}

.plan-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .82rem;
    padding: .35rem 0;
}

.plan-row span {
    color: var(--slate, #6B7280);
}

.plan-row strong {
    color: var(--ink, #1C2B4A);
    font-family: 'IBM Plex Mono', monospace;
    font-size: .8rem;
}

.plan-note {
    font-size: .72rem;
    color: var(--slate, #6B7280);
    line-height: 1.45;
    margin: .5rem 0 0;
}


/* SET-DEDUCTION BOX (custom) */

.deduction-set-box {
    background: var(--paper, #F2F1EA);
    border-radius: 8px;
    padding: .9rem 1rem 1rem;
    margin-bottom: 1.1rem;
}

.deduction-set-sub {
    font-size: .74rem;
    color: var(--slate, #6B7280);
    line-height: 1.45;
    margin: .2rem 0 .8rem;
}

.fully-paid-note {
    font-size: .78rem;
    color: var(--green, #2F8F5B);
    font-weight: 600;
}

.deduction-fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .8rem;
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

.field-hint {
    font-size: .68rem;
    color: var(--slate, #6B7280);
}

.field-hint--error {
    color: var(--red, #C24D3B);
}

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

    .ledger-strip {
        grid-template-columns: 1fr;
        text-align: left;
    }

    .ledger-divider {
        display: none;
    }

    .deduction-fields {
        grid-template-columns: 1fr;
    }
}
</style>