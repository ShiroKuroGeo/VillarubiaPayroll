<template>
    <div class="main">
        <div class="topbar">

            <div class="d-flex align-items-center gap-2">

                <button class="btn-menu d-lg-none" @click="$emit('toggle-sidebar')" aria-label="Toggle menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12h18M3 6h18M3 18h18" />
                    </svg>
                </button>
                <div>

                    <div class="eyebrow">
                        {{ todayLabel }}
                    </div>

                    <h1>
                        Payroll
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

        <div class="content">
            <div v-if="showGenerateOnly" class="generate-gate">

                <div class="generate-card">

                    <!-- LEFT COLUMN: intro + action -->
                    <div class="generate-main">

                        <div class="stamp gold">PAYROLL</div>

                        <div class="generate-title">It's payroll day</div>

                        <div class="generate-sub">
                            Generate this week's payroll to view and release employee payouts.
                        </div>

                        <div class="warning-header">
                            <div class="warning-icon">⚠</div>
                            <div>
                                <h2>Generate Weekly Payroll</h2>
                                <p>Payroll can only be generated on Saturday.</p>
                            </div>
                        </div>

                        <div v-if="generateError" class="generate-error">
                            {{ generateError }}
                        </div>

                        <button class="generate-btn" :disabled="generating" @click="handleGeneratePayroll">
                            {{ generating ? 'Generating…' : 'Generate Payroll' }}
                        </button>

                    </div>

                    <!-- RIGHT COLUMN: checklist, always visible, fills remaining space -->
                    <div class="generate-checklist">

                        <div class="checklist-heading">Before you continue, review the checklist</div>

                        <div class="checklist-grid">

                            <div class="warning-section">
                                <h3>🕒 Attendance</h3>
                                <ul>
                                    <li>Attendance for the current payroll week has been imported from the biometric system.</li>
                                    <li>All attendance records are correct and up to date.</li>
                                    <li>No employees with missing <strong>Time In</strong> / <strong>Time Out</strong>.</li>
                                    <li>Check <strong>Late</strong>, <strong>Half Day</strong>, and <strong>Absent</strong> statuses.</li>
                                </ul>
                            </div>

                            <div class="warning-section">
                                <h3>📄 SSS Contribution</h3>
                                <ul>
                                    <li>Each employee's <strong>SSS Contribution</strong> is correct.</li>
                                </ul>
                            </div>

                            <div class="warning-section">
                                <h3>💰 Cash Advances</h3>
                                <ul>
                                    <li>All Cash Advances to be deducted this payroll are already <strong>Approved</strong>.</li>
                                </ul>
                            </div>

                            <div class="warning-section">
                                <h3>👤 Employee Status</h3>
                                <ul>
                                    <li><strong>Separated or Terminated</strong> employees have updated status.</li>
                                </ul>
                            </div>

                            <div class="warning-section settings-span">
                                <h3>⚙️ Payroll Settings</h3>
                                <div class="settings-list">
                                    <div class="setting-item">
                                        <strong>Work Start Time</strong>
                                        <span>Determines lateness.</span>
                                    </div>
                                    <div class="setting-item">
                                        <strong>Grace Period</strong>
                                        <span>Default 15 min before shift start.</span>
                                    </div>
                                    <div class="setting-item">
                                        <strong>Late Deduction (Per Min)</strong>
                                        <span>Used for late deductions.</span>
                                    </div>
                                    <div class="setting-item">
                                        <strong>Overtime Premium Rate</strong>
                                        <span>Applied after scheduled shift end.</span>
                                    </div>
                                    <div class="setting-item">
                                        <strong>Overtime Multiplier</strong>
                                        <span>For special days, e.g. Sunday. <code>1.3</code> = 30% premium.</span>
                                    </div>
                                    <div class="setting-item">
                                        <strong>Company Name</strong>
                                        <span>Used on the generated payslip.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="deduction-notice settings-span">
                                <div class="notice-icon">ℹ</div>
                                <div>
                                    <strong>Important</strong>
                                    <p>Generating payroll will create and link the corresponding deductions automatically.</p>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>
            <template v-else>

                <div class="period-bar">
                    <div class="period-info">
                        <div class="period-label">
                            Pay period
                        </div>
                    </div>
                    <div class="period-actions">
                        <span class="period-tag">
                            {{ paidCount }} of {{ activeEmployeeCount }} paid
                        </span>
                        <button class="add-btn" @click="markAllPaid">
                            Mark all as paid
                        </button>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-lg-3">
                        <div class="punch-card">
                            <div class="stamp green">
                                STAFF
                            </div>
                            <div class="stat-label">
                                Active Employees
                            </div>
                            <div class="stat-period">
                                This pay period
                            </div>
                            <div class="stat-value">
                                {{ activeEmployeeCount }}
                            </div>
                            <div class="stat-delta stat-delta--slate">
                                Included in this payroll run
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="punch-card">
                            <div class="stamp gold">
                                NET
                            </div>
                            <div class="stat-label">
                                Total Payroll
                            </div>

                            <div class="stat-period">
                                This pay period
                            </div>

                            <div class="stat-value stat-value-money">
                                {{ formatCurrency(totalNetPayroll) }}
                            </div>

                            <div class="stat-delta stat-delta--gold">
                                Across all active employees
                            </div>

                        </div>

                    </div>

                    <div class="col-6 col-lg-3">

                        <div class="punch-card">

                            <div class="stamp green">
                                PAID
                            </div>

                            <div class="stat-label">
                                Paid Out
                            </div>

                            <div class="stat-period">
                                This pay period
                            </div>

                            <div class="stat-value stat-value-money">
                                {{ formatCurrency(totalPaidAmount) }}
                            </div>

                            <div class="stat-delta text-success">
                                {{ paidCount }} employees paid
                            </div>

                        </div>

                    </div>

                    <div class="col-6 col-lg-3">

                        <div class="punch-card">

                            <div class="stamp blue">
                                DUE
                            </div>

                            <div class="stat-label">
                                Still Pending
                            </div>

                            <div class="stat-period">
                                This pay period
                            </div>

                            <div class="stat-value stat-value-money">
                                {{ formatCurrency(totalPendingAmount) }}
                            </div>

                            <div class="stat-delta stat-delta--blue">
                                {{ pendingCount }} employees pending
                            </div>

                        </div>

                    </div>

                </div>

                <div class="panel">

                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                        <div>

                            <div class="section-title mb-0">
                                Employee payouts
                            </div>

                            <div class="panel-sub">
                                Mark each employee as paid once their salary is released
                            </div>

                        </div>

                        <div class="d-flex gap-2 flex-wrap">


                            <div class="search-box">

                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="7" />
                                    <path d="m20 20-3-3" />
                                </svg>

                                <input v-model="searchQuery" type="text" placeholder="Search employee..." />

                            </div>

                        </div>

                    </div>

                    <div class="filter-row">

                        <button v-for="filter in statusFilters" :key="filter.key" class="filter-pill" :class="{
                            active: paymentFilter === filter.key
                        }" @click="paymentFilter = filter.key">
                            {{ filter.label }}
                        </button>

                    </div>
                    <div class="table-responsive">
                        <table class="table-ledger salary-table" v-if="filteredPayrollData.length">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Basic Salary</th>
                                    <th>Total Att.
                                        <span title="Total Attendance" style="color: lightseagreen;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M12 16v-4"></path>
                                                <path d="M12 8h.01"></path>
                                            </svg>
                                        </span>
                                    </th>
                                    <th>Gross Pay</th>
                                    <th>Deductions</th>
                                    <th>Net Salary</th>
                                    <th>Payment Status</th>
                                    <th>Paid On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="employee in filteredPayrollData" :key="employee.id">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-sm">
                                                <img v-if="employee.image" :src="storageImage(employee.image)" :alt="employee.employeeName" />
                                                <span v-else>
                                                    {{ employee.initials }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="emp-name">
                                                    {{ employee.employeeName }}
                                                </div>
                                                <div class="emp-role">
                                                    Employee #{{
                                                        employee.employeeId
                                                            .toString()
                                                            .padStart(4, '0')
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="department">{{ formatCurrency(employee.basicSalary) }}</span>
                                    </td>
                                    <td class="money net-pay">
                                        {{ employee.totalAttendance }}{{ employee.totalAttendance >= 1 ? ' days' : 'day' }}
                                    </td>
                                    <td class="money net-pay">
                                        {{ formatCurrency(employee.totalAttendance * employee.basicSalary) }}
                                    </td>
                                    <td class="money net-pay" style="color: #FF7F7F;">
                                        {{ formatCurrency(employee.deductions) }}
                                    </td>
                                    <td class="money net-pay">
                                        {{ formatCurrency((employee.totalAttendance * employee.basicSalary) - employee.deductions) }}
                                    </td>
                                    <td>
                                        <span class="badge-status" :class="employee.paid ? 'badge-active' : 'badge-pending'">
                                            {{ employee.paid ? 'PAID' : 'PENDING' }}
                                        </span>
                                    </td>
                                    <td class="money">
                                        {{ employee.paidDate ? formatDate(employee.paidDate) : '—' }}
                                    </td>
                                    <td>
                                        <div class="action-group">
                                            <button v-if="!employee.paid" class="action-btn pay-btn" @click="openPayModal(employee)">
                                                Mark as Paid
                                            </button>
                                            <button v-else class="action-btn undo-btn" @click="unmarkPaid(employee)">
                                                Undo
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-else class="empty-state">
                            No employees match your search or filter.
                        </div>
                    </div>
                </div>

            </template>

        </div>

        <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
            <div class="salary-modal">
                <div class="modal-header">
                    <div>
                        <div class="modal-eyebrow">
                            RELEASE PAYMENT
                        </div>
                        <div class="modal-title">
                            Pay {{ payForm.employeeName }}
                        </div>
                        <div class="modal-sub">
                            Confirm payout for {{ payPeriod || 'this pay period' }}
                        </div>
                    </div>
                    <button class="close-btn" @click="closeModal" aria-label="Close">
                        ×
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>
                                Payment Method
                            </label>
                            <select v-model="payForm.paymentMethod" class="form-control">
                                <option value="Bank Transfer">
                                    Bank Transfer
                                </option>
                                <option value="Cash">
                                    Cash
                                </option>
                                <option value="Check">
                                    Check
                                </option>
                                <option value="GCash">
                                    GCash
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>
                                Payment Date
                            </label>
                            <input v-model="payForm.paidDate" type="date" class="form-control" />
                        </div>
                    </div>
                    <div class="salary-preview">
                        <div>
                            <div class="preview-label">
                                AMOUNT TO RELEASE
                            </div>
                            <div class="preview-value">
                                {{ formatCurrency(payForm.amount) }}
                            </div>
                        </div>
                        <div class="preview-equation">
                            {{ payForm.employeeName }}
                            <br />
                            {{ formatSalaryType(payForm.salaryType) }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" @click="closeModal">
                        Cancel
                    </button>
                    <button class="save-btn" @click="confirmPayment">
                        Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { usePayrollStore } from '@/stores/usePayroll'
import { storageImage } from '@/utils/image'
import { showConfirm } from '@/utils/Swals'
import { computed, onMounted, onBeforeUnmount, ref } from 'vue'
defineOptions({
    name: 'PayrollPage'
})

defineEmits([
    'toggle-sidebar'
])

const liveClock = ref('--:--:--')

const now = ref(new Date())
const payrollStore = usePayrollStore();

let clockTimer = null

function tickClock() {
    const current = new Date()
    now.value = current
    liveClock.value =
        current.toLocaleTimeString(
            'en-US',
            {
                hour12: true
            }
        )
}

const isSaturday = computed(() => now.value.getDay() === 5)
const payrollGenerated = ref(false)
const generating = ref(false)
const generateError = ref('')
const showGenerateOnly = computed(() => isSaturday.value && !payrollGenerated.value)

async function checkPayrollGenerated(data) {
    try {
        const response = await payrollStore.payrollList({ ...data });
        if (response.data.data.length === 0) {
            payrollGenerated.value = false
            return
        }

        const result = await response.data

        payrollData.value = response.data.data;

        payrollGenerated.value = Boolean(result?.data?.length)
    } catch (err) {
        console.error('Failed to check existing payroll', err)
    }
}

async function handleGeneratePayroll() {
    generating.value = true
    generateError.value = ''

    try {
        await payrollStore.generatePayroll();
        await checkPayrollGenerated({
            "per_page": 100
        })
        // payrollGenerated.value = true
    } catch (err) {
        generateError.value = err.message || 'Something went wrong while generating payroll.'
    } finally {
        generating.value = false
    }
}


const todayLabel =
    new Date().toLocaleDateString(
        'en-US',
        {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }
    )

const payPeriod = ref('')
const payrollData = ref([]);
const searchQuery = ref('')
const paymentFilter = ref('all')
const statusFilters = [
    {
        key: 'all',
        label: 'All'
    },
    {
        key: 'paid',
        label: 'Paid'
    },
    {
        key: 'pending',
        label: 'Pending'
    }
]

const filteredPayrollData = computed(() => {

    const search =
        searchQuery.value
            .trim()
            .toLowerCase()

    return payrollData.value

        .filter(
            employee =>
                employee.status !== 'Separated/Terminated'
        )

        .filter(employee => {
            const matchesSearch =
                !search ||
                employee.employeeName
                    .toLowerCase()
                    .includes(search) ||
                employee.department
                    .toLowerCase()
                    .includes(search)

            const matchesPayment =
                paymentFilter.value === 'all' ||
                (paymentFilter.value === 'paid' && employee.paid) ||
                (paymentFilter.value === 'pending' && !employee.paid)

            return matchesSearch && matchesPayment
        })
})

const activeEmployees = computed(() => {

    return payrollData.value.filter(
        employee => employee.status !== 'Separated/Terminated'
    )

})


const activeEmployeeCount = computed(() => {

    return activeEmployees.value.length

})


const paidCount = computed(() => {

    return activeEmployees.value.filter(
        employee => employee.paid
    ).length

})


const pendingCount = computed(() => {

    return activeEmployeeCount.value - paidCount.value

})


const totalNetPayroll = computed(() => {

    return activeEmployees.value.reduce(
        (total, employee) =>
            total + calculateNet(employee),
        0
    )

})


const totalPaidAmount = computed(() => {

    return activeEmployees.value
        .filter(employee => employee.paid)
        .reduce(
            (total, employee) =>
                total + calculateNet(employee),
            0
        )

})


const totalPendingAmount = computed(() => {
    return totalNetPayroll.value - totalPaidAmount.value
})

const showModal = ref(false)


function createEmptyPayForm() {

    return {

        id: null,

        employeeName: '',

        salaryType: 'monthly',

        amount: 0,

        paymentMethod: 'bank_transfer',

        paidDate: new Date()
            .toISOString()
            .slice(0, 10),

        reference: ''

    }

}


const payForm = ref(
    createEmptyPayForm()
)


function openPayModal(employee) {

    payForm.value = {
        id: employee.id,
        employeeName: employee.employeeName,
        salaryType: employee.salaryType,
        amount: calculateNet(employee),
        paymentMethod: 'Bank Transfer',
        paidDate: new Date()
            .toISOString()
            .slice(0, 10),
        reference: ''
    }

    showModal.value = true

}


function closeModal() {

    showModal.value = false

}

const confirmPayment = async () => {
    const data = {
        'payroll_id': payForm.value.id,
        'payment_date': payForm.value.paidDate,
        'payment_method': payForm.value.paymentMethod,
        'status': 'Paid',
    }

    await payrollStore.updatePayroll(data);

    await checkPayrollGenerated({
        "per_page": 100
    })

    closeModal()
}


const unmarkPaid = async (employee) => {

    const data = {
        'payroll_id': employee.id,
        'payment_date': employee.paidDate,
        'payment_method': employee.paymentMethod,
        'status': 'Draft',
    }

    const isConfirmed = await showConfirm('Undo Payroll', `Undo payment for ${employee.employeeName}? This will mark them as pending again.`, 'Yes, Undo');

    if (isConfirmed) {
        await payrollStore.updatePayroll(data);
    }

    await checkPayrollGenerated({
        "per_page": 100
    })

    closeModal()
}


const markAllPaid = async () => {
    if (pendingCount.value !== 0) {
        const isConfirmed = await showConfirm('Confirmation', 'There are still unpaid status. Are you sure want to continue', 'Yes, Continue');

        if (isConfirmed) {
            await payrollStore.exportPayslips();
        }
    } else {
        await payrollStore.exportPayslips();
    }

    await checkPayrollGenerated({
        "per_page": 100
    })
}

function calculateNet(employee) {

    return (
        (Number(employee.basicSalary || 0) *
            Number(employee.totalAttendance || 0)) -
        Number(employee.deductions || 0)
    )

}

function formatCurrency(amount) {

    return new Intl.NumberFormat(
        'en-PH',
        {
            style: 'currency',
            currency: 'PHP',
            minimumFractionDigits: 2
        }
    ).format(
        Number(amount || 0)
    )

}


function formatSalaryType(type) {

    const labels = {

        monthly: 'MONTHLY',

        weekly: 'WEEKLY',

        daily: 'DAILY',

        hourly: 'HOURLY'

    }


    return (
        labels[type] ||
        (type || '').toUpperCase()
    )

}

function formatDate(dateString) {

    if (!dateString) {
        return '—'
    }


    return new Date(dateString)
        .toLocaleDateString(
            'en-US',
            {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            }
        )

}

onMounted(async () => {
    tickClock()
    clockTimer =
        setInterval(
            tickClock,
            1000
        )

    // if (isSaturday.value) {
    await checkPayrollGenerated({
        "per_page": 100
    })
    // }

})

onBeforeUnmount(() => {

    clearInterval(
        clockTimer
    )

})

</script>


<style scoped>
/* =====================================================
   BASE
===================================================== */

.main {
    flex: 1;
    min-width: 0;
}


/* =====================================================
   TOPBAR
===================================================== */

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


.d-lg-none {
    display: flex;
}


@media (min-width: 992px) {

    .d-lg-none {
        display: none;
    }

}


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


/* =====================================================
   CONTENT
===================================================== */

.content {
    padding: 1.75rem;
}


/* =====================================================
   PERIOD BAR
===================================================== */

.period-bar {
    background: var(--paper-2, #FBFAF6);
    border: 1px solid var(--line, #DCD8CB);
    border-radius: 10px;
    padding: 1rem 1.3rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}


.period-label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .64rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--slate, #6B7280);
    font-weight: 600;
    margin-bottom: .3rem;
}


.period-input {
    border: none;
    background: transparent;
    outline: none;
    font-family: 'Fraunces', serif;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--ink, #1C2B4A);
    min-width: 240px;
}


.period-actions {
    display: flex;
    align-items: center;
    gap: .75rem;
}


.period-tag {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .72rem;
    color: var(--slate, #6B7280);
    white-space: nowrap;
}


/* =====================================================
   PANELS
===================================================== */

.panel {
    background: var(--paper-2, #FBFAF6);
    border: 1px solid var(--line, #DCD8CB);
    border-radius: 10px;
    padding: 1.3rem 1.4rem;
}


.panel-title {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.05rem;
    margin-bottom: .1rem;
}


.section-title {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.05rem;
}


.panel-sub {
    font-size: .78rem;
    color: var(--slate, #6B7280);
}


/* =====================================================
   STAT CARDS
===================================================== */

.punch-card {
    background: var(--paper-2, #FBFAF6);
    border: 1px solid var(--line, #DCD8CB);
    border-radius: 10px;
    position: relative;
    padding: 1.25rem 1.3rem 1.1rem;
    min-height: 160px;
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
    max-width: 75%;
}


.stat-period {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .65rem;
    color: var(--slate, #6B7280);
    margin-top: .2rem;
    margin-bottom: .15rem;
}


.stat-value {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 2.1rem;
    line-height: 1.15;
    margin-top: .15rem;
}


.stat-value-money {
    font-size: 1.65rem;
    padding-top: .2rem;
}


.stat-delta {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .72rem;
    margin-top: .2rem;
}


.stat-delta--slate {
    color: var(--slate, #6B7280);
}


.stat-delta--gold {
    color: var(--gold-dark, #9C7726);
}


.stat-delta--blue {
    color: #426B8F;
}


.text-success {
    color: var(--green, #2F8F5B);
}


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


.stamp.green {
    color: var(--green, #2F8F5B);
    border-color: var(--green, #2F8F5B);
}


.stamp.gold {
    color: var(--gold-dark, #9C7726);
    border-color: var(--gold, #C79A3D);
}


.stamp.blue {
    color: #426B8F;
    border-color: #6D94B6;
}


/* =====================================================
   SEARCH / FILTERS / ADD BUTTON
===================================================== */

.search-box {
    display: flex;
    align-items: center;
    gap: .45rem;
    border: 1px solid var(--line, #DCD8CB);
    border-radius: 6px;
    background: var(--paper-2, #FBFAF6);
    padding: .4rem .65rem;
    min-width: 210px;
}


.search-box svg {
    color: var(--slate, #6B7280);
    flex-shrink: 0;
}


.search-box input {
    border: none;
    outline: none;
    background: transparent;
    width: 100%;
    font-size: .75rem;
    color: var(--ink, #1C2B4A);
}


.search-box input::placeholder {
    color: var(--slate, #6B7280);
}


.filter-row {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}


.filter-pill {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .7rem;
    font-weight: 600;
    padding: .32rem .65rem;
    border-radius: 20px;
    letter-spacing: .03em;
    border: 1px solid var(--line, #DCD8CB);
    background: var(--paper-2, #FBFAF6);
    color: var(--slate, #6B7280);
    cursor: pointer;
    transition: all .15s ease;
}


.filter-pill.active {
    background: var(--ink, #1C2B4A);
    color: #F3DFA6;
    border-color: var(--ink, #1C2B4A);
}


.filter-pill:hover:not(.active) {
    background: var(--paper, #F2F1EA);
}


.add-btn {
    border: 1px solid var(--ink, #1C2B4A);
    background: var(--ink, #1C2B4A);
    color: #F3DFA6;
    border-radius: 6px;
    padding: .42rem .8rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: .7rem;
    font-weight: 600;
    cursor: pointer;
}


.add-btn:hover {
    background: #28395E;
}


.add-btn:disabled {
    opacity: .4;
    cursor: not-allowed;
}


/* =====================================================
   TABLE
===================================================== */

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
    background: transparent;
    text-align: left;
    white-space: nowrap;
}


.table-ledger tbody td {
    padding: .75rem .5rem;
    border-bottom: 1px dashed var(--line, #DCD8CB);
    vertical-align: middle;
    font-size: .84rem;
}


.table-ledger tbody tr:last-child td {
    border-bottom: none;
}


.avatar-sm {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    overflow: hidden;
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


.avatar-sm img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}


.emp-name {
    font-weight: 600;
}


.emp-role {
    font-size: .7rem;
    color: var(--slate, #6B7280);
}


.department {
    color: var(--ink-2, #28395E);
    font-size: .8rem;
}


.money {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .76rem;
    color: var(--ink-2, #28395E);
    white-space: nowrap;
}


.net-pay {
    font-weight: 600;
    color: var(--ink, #1C2B4A);
}


.salary-type {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .63rem;
    color: var(--slate, #6B7280);
    letter-spacing: .03em;
}


.badge-status {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .64rem;
    font-weight: 600;
    padding: .3rem .55rem;
    border-radius: 5px;
    letter-spacing: .03em;
    display: inline-block;
    white-space: nowrap;
}


.badge-active {
    background: var(--green-bg, #E5F2EA);
    color: var(--green, #2F8F5B);
}


.badge-pending {
    background: var(--amber-bg, #F6EEDB);
    color: var(--gold-dark, #9C7726);
}


.action-group {
    display: flex;
    gap: .35rem;
}


.action-btn {
    border: 1px solid;
    border-radius: 6px;
    padding: .35rem .55rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: .63rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
}


.pay-btn {
    border-color: #9FCBAF;
    background: var(--green-bg, #E5F2EA);
    color: var(--green, #2F8F5B);
}


.pay-btn:hover {
    background: var(--green, #2F8F5B);
    color: white;
}


.undo-btn {
    border-color: var(--line, #DCD8CB);
    background: var(--paper-2, #FBFAF6);
    color: var(--slate, #6B7280);
}


.undo-btn:hover {
    background: var(--paper, #F2F1EA);
    color: var(--ink, #1C2B4A);
}


.empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: var(--slate, #6B7280);
    font-size: .85rem;
}


/* =====================================================
   MODAL
===================================================== */

.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(28, 43, 74, .45);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 9;
}


.salary-modal {
    width: min(560px, 100%);
    max-height: 90vh;
    overflow-y: auto;
    background: var(--paper-2, #FBFAF6);
    border: 1px solid var(--line, #DCD8CB);
    border-radius: 12px;
    box-shadow: 0 20px 50px rgba(28, 43, 74, .2);
}


.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem 1.4rem;
    border-bottom: 1px solid var(--line, #DCD8CB);
}


.modal-eyebrow {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .62rem;
    letter-spacing: .1em;
    color: var(--gold-dark, #9C7726);
    font-weight: 600;
}


.modal-title {
    font-family: 'Fraunces', serif;
    font-size: 1.35rem;
    font-weight: 600;
    color: var(--ink, #1C2B4A);
    margin-top: .1rem;
}


.modal-sub {
    color: var(--slate, #6B7280);
    font-size: .75rem;
    margin-top: .1rem;
}


.close-btn {
    width: 32px;
    height: 32px;
    border: 1px solid var(--line, #DCD8CB);
    background: transparent;
    color: var(--slate, #6B7280);
    border-radius: 6px;
    font-size: 1.3rem;
    line-height: 1;
    cursor: pointer;
}


.close-btn:hover {
    background: var(--paper, #F2F1EA);
    color: var(--ink, #1C2B4A);
}


.modal-body {
    padding: 1.4rem;
}


.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}


.form-group {
    margin-bottom: 1rem;
}


.form-group label {
    display: block;
    font-family: 'IBM Plex Mono', monospace;
    font-size: .64rem;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--slate, #6B7280);
    font-weight: 600;
    margin-bottom: .4rem;
}


.form-control {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid var(--line, #DCD8CB);
    background: var(--paper-2, #FBFAF6);
    color: var(--ink, #1C2B4A);
    border-radius: 7px;
    padding: .58rem .7rem;
    font-size: .8rem;
    outline: none;
}


.form-control:focus {
    border-color: var(--gold, #C79A3D);
    box-shadow: 0 0 0 3px rgba(199, 154, 61, .12);
}


.salary-preview {
    margin-top: .4rem;
    padding: 1rem;
    border: 1px solid #D8E6DC;
    background: var(--green-bg, #E5F2EA);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}


.preview-label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .6rem;
    letter-spacing: .08em;
    color: var(--green, #2F8F5B);
    font-weight: 600;
}


.preview-value {
    font-family: 'Fraunces', serif;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--ink, #1C2B4A);
    margin-top: .1rem;
}


.preview-equation {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .62rem;
    color: var(--slate, #6B7280);
    text-align: right;
    line-height: 1.4;
}


.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: .6rem;
    padding: 1rem 1.4rem;
    border-top: 1px solid var(--line, #DCD8CB);
}


.cancel-btn {
    border: 1px solid var(--line, #DCD8CB);
    background: var(--paper-2, #FBFAF6);
    color: var(--ink-2, #28395E);
    border-radius: 6px;
    padding: .48rem .8rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: .7rem;
    cursor: pointer;
}


.cancel-btn:hover {
    background: var(--paper, #F2F1EA);
}


.save-btn {
    border: 1px solid var(--ink, #1C2B4A);
    background: var(--ink, #1C2B4A);
    color: #F3DFA6;
    border-radius: 6px;
    padding: .48rem .9rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: .7rem;
    font-weight: 600;
    cursor: pointer;
}


.save-btn:hover {
    background: #28395E;
}


/* =====================================================
   BUTTONS / LAYOUT HELPERS
===================================================== */

.btn {
    border-radius: 6px;
    padding: .45rem .9rem;
    border: 1px solid transparent;
    cursor: pointer;
}


.btn-outline-ledger {
    border: 1px solid var(--line, #DCD8CB);
    color: var(--ink-2, #28395E);
    font-size: .85rem;
    font-weight: 500;
    background: var(--paper-2, #FBFAF6);
}


.btn-outline-ledger:hover {
    background: var(--paper, #F2F1EA);
}


.btn-sm {
    font-size: .82rem;
    padding: .4rem .8rem;
}


.d-flex {
    display: flex;
}


.align-items-center {
    align-items: center;
}


.align-items-start {
    align-items: flex-start;
}


.justify-content-between {
    justify-content: space-between;
}


.flex-wrap {
    flex-wrap: wrap;
}


.gap-2 {
    gap: .5rem;
}


.gap-3 {
    gap: 1rem;
}


.mb-0 {
    margin-bottom: 0;
}


.mb-3 {
    margin-bottom: 1rem;
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


@media (max-width: 576px) {

    .content {
        padding: 1rem;
    }


    .topbar {
        padding: 1rem;
    }


    .col-6 {
        width: 100%;
    }


    .form-row {
        grid-template-columns: 1fr;
    }


    .search-box {
        min-width: 100%;
    }


    .period-bar {
        flex-direction: column;
        align-items: flex-start;
    }


    .salary-preview {
        flex-direction: column;
        align-items: flex-start;
    }


    .preview-equation {
        text-align: left;
    }


    .modal-footer {
        justify-content: stretch;
    }


    .cancel-btn,
    .save-btn {
        flex: 1;
    }

}

.generate-gate {
    width: 100%;
    height: 100%;
    display: flex;
    padding: 0;
    margin: 0;
}

.generate-card {
    position: relative;
    width: 100%;
    height: 100%;
    display: grid;
    grid-template-columns: minmax(260px, 340px) 1fr;
    gap: 0;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
}

/* ===== LEFT: intro + button ===== */
.generate-main {
    position: relative;
    display: flex;
    flex-direction: column;
    padding: 2rem 1.75rem;
    border-right: 1px solid #eef2f6;
    background: #fbfaf6;
}

.stamp {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 2px dashed var(--gold, #C79A3D);
    color: var(--gold-dark, #9C7726);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'IBM Plex Mono', monospace;
    font-size: .58rem;
    font-weight: 600;
    transform: rotate(-8deg);
}

.generate-title {
    margin-top: .5rem;
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.35rem;
    color: #1e293b;
}

.generate-sub {
    margin-top: .4rem;
    color: #64748b;
    font-size: .85rem;
    line-height: 1.5;
}

.generate-main .warning-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    margin-top: 1.5rem;
    border: 1px solid #fed7aa;
    border-radius: 10px;
    background: #fff7ed;
}

.warning-icon {
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #c2410c;
    background: #ffedd5;
    border-radius: 50%;
}

.generate-main .warning-header h2 {
    margin: 0;
    font-size: .9rem;
    font-weight: 700;
    color: #1e293b;
}

.generate-main .warning-header p {
    margin: 2px 0 0;
    font-size: .72rem;
    color: #9a3412;
}

.generate-error {
    margin-top: 1rem;
    padding: 10px 14px;
    color: #b91c1c;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    font-size: .8rem;
}

.generate-btn {
    margin-top: auto;
    padding-top: 1.5rem;
}

.generate-btn {
    width: 100%;
    padding: 12px 18px;
    border: none;
    border-radius: 8px;
    background: #16a34a;
    color: #fff;
    font-size: .85rem;
    font-weight: 700;
    cursor: pointer;
    transition: .2s;
}

.generate-btn:hover:not(:disabled) {
    background: #15803d;
    transform: translateY(-1px);
}

.generate-btn:disabled {
    opacity: .6;
    cursor: not-allowed;
}

/* ===== RIGHT: checklist, fills remaining width/height ===== */
.generate-checklist {
    display: flex;
    flex-direction: column;
    min-height: 0;
    padding: 1.5rem 1.75rem;
    overflow-y: auto;
}

.checklist-heading {
    font-size: .8rem;
    font-weight: 700;
    color: #9a3412;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.checklist-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem 2rem;
    text-align: left;
}

.settings-span {
    grid-column: 1 / -1;
}

.warning-section h3 {
    margin: 0 0 8px;
    font-size: .82rem;
    font-weight: 700;
    color: #1e293b;
}

.warning-section ul {
    margin: 0;
    padding-left: 18px;
}

.warning-section li {
    margin-bottom: 5px;
    font-size: .78rem;
    line-height: 1.5;
    color: #475569;
}

.warning-section strong {
    color: #1e293b;
}

.settings-list {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.setting-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 8px 10px;
    background: #f8fafc;
    border: 1px solid #eef2f6;
    border-radius: 8px;
}

.setting-item strong {
    font-size: .74rem;
    color: #334155;
}

.setting-item span {
    font-size: .68rem;
    line-height: 1.4;
    color: #64748b;
}

.setting-item code {
    padding: 1px 5px;
    border-radius: 4px;
    background: #ccfbf1;
    color: #0f766e;
    font-weight: 700;
}

.deduction-notice {
    display: flex;
    gap: 10px;
    padding: 12px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
}

.notice-icon {
    width: 24px;
    height: 24px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #dbeafe;
    color: #1d4ed8;
    font-weight: 700;
    font-size: .75rem;
}

.deduction-notice strong {
    color: #1e40af;
    font-size: .78rem;
}

.deduction-notice p {
    margin: 3px 0 0;
    font-size: .74rem;
    line-height: 1.5;
    color: #475569;
}

@media (max-width: 800px) {
    .generate-card {
        grid-template-columns: 1fr;
        grid-template-rows: auto 1fr;
    }

    .generate-main {
        border-right: none;
        border-bottom: 1px solid #eef2f6;
    }

    .checklist-grid {
        grid-template-columns: 1fr;
    }

    .settings-list {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .settings-list {
        grid-template-columns: 1fr;
    }
}
</style>