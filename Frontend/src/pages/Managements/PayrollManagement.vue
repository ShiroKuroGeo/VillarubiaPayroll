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


                <button class="btn btn-outline-ledger btn-sm" @click="exportCsv">
                    Export
                </button>

            </div>

        </div>

        <div class="content">

            <!-- PERIOD BAR -->

            <div class="period-bar">

                <div class="period-info">

                    <div class="period-label">
                        Pay period
                    </div>

                    <input v-model="payPeriod" type="text" class="period-input" placeholder="e.g. Aug 25 – Aug 29, 2026" />

                </div>


                <div class="period-actions">

                    <span class="period-tag">
                        {{ paidCount }} of {{ activeEmployeeCount }} paid
                    </span>

                    <button class="add-btn" :disabled="!pendingCount" @click="markAllPaid">
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


                <!-- FILTERS -->

                <div class="filter-row">

                    <button v-for="filter in statusFilters" :key="filter.key" class="filter-pill" :class="{
                        active: paymentFilter === filter.key
                    }" @click="paymentFilter = filter.key">
                        {{ filter.label }}
                    </button>

                </div>


                <!-- TABLE -->

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
                                            <img v-if="employee.image" :src="employee.image" :alt="employee.employeeName" />
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
                                <option value="bank_transfer">
                                    Bank Transfer
                                </option>
                                <option value="cash">
                                    Cash
                                </option>
                                <option value="check">
                                    Check
                                </option>
                                <option value="gcash">
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
                    <div class="form-group">
                        <label>
                            Reference / Notes
                        </label>
                        <input v-model="payForm.reference" type="text" class="form-control" placeholder="Optional reference number or note" />
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
import { computed, onMounted, onBeforeUnmount, ref } from 'vue'


defineOptions({
    name: 'PayrollPage'
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
const payrollData = ref([

    {
        id: 1,
        employeeId: 1,
        employeeName: 'Jonas Diaz',
        initials: 'JD',
        department: 'Warehouse',
        basicSalary: 760,
        salaryType: 'weekly',
        totalAttendance: 7,
        deductions: 1500,
        status: 'active',
        image: null,

        paid: false,
        paidDate: null,
        paymentMethod: null,
        reference: ''
    },

    {
        id: 2,
        employeeId: 2,
        employeeName: 'Carla Santos',
        initials: 'CS',
        department: 'Accounting',
        basicSalary: 760,
        salaryType: 'weekly',
        totalAttendance: 6,
        deductions: 1800,
        status: 'active',
        image: null,

        paid: false,
        paidDate: null,
        paymentMethod: null,
        reference: ''
    },

    {
        id: 3,
        employeeId: 3,
        employeeName: 'Ramon Tan',
        initials: 'RT',
        department: 'Logistics',
        basicSalary: 760,
        salaryType: 'weekly',
        totalAttendance: 7,
        deductions: 1200,
        status: 'active',
        image: null,

        paid: false,
        paidDate: null,
        paymentMethod: null,
        reference: ''
    },

    {
        id: 4,
        employeeId: 4,
        employeeName: 'Paulo Lim',
        initials: 'PL',
        department: 'Customer Care',
        basicSalary: 760,
        salaryType: 'monthly',
        totalAttendance: 7,
        deductions: 1400,
        status: 'active',
        image: null,

        paid: false,
        paidDate: null,
        paymentMethod: null,
        reference: ''
    },

    {
        id: 5,
        employeeId: 5,
        employeeName: 'Nadia Ang',
        initials: 'NA',
        department: 'Marketing',
        basicSalary: 760,
        salaryType: 'monthly',
        totalAttendance: 7,
        deductions: 1600,
        status: 'active',
        image: null,

        paid: false,
        paidDate: null,
        paymentMethod: null,
        reference: ''
    },

    {
        id: 6,
        employeeId: 6,
        employeeName: 'Erik Villar',
        initials: 'EV',
        department: 'Warehouse',
        basicSalary: 760,
        salaryType: 'monthly',
        totalAttendance: 7,
        deductions: 1300,
        status: 'active',
        image: null,

        paid: false,
        paidDate: null,
        paymentMethod: null,
        reference: ''
    }

])


// =====================================================
// FILTERS
// =====================================================

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
                employee.status === 'active'
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


// =====================================================
// STATISTICS
// =====================================================

const activeEmployees = computed(() => {

    return payrollData.value.filter(
        employee =>
            employee.status === 'active'
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

        .filter(
            employee => employee.paid
        )

        .reduce(
            (total, employee) =>
                total + calculateNet(employee),
            0
        )

})


const totalPendingAmount = computed(() => {

    return totalNetPayroll.value - totalPaidAmount.value

})


// =====================================================
// PAYMENT MODAL
// =====================================================

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

        paymentMethod: 'bank_transfer',

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


function confirmPayment() {

    const index =
        payrollData.value.findIndex(
            employee =>
                employee.id === payForm.value.id
        )


    if (index === -1) {
        return
    }


    payrollData.value[index] = {

        ...payrollData.value[index],

        paid: true,

        paidDate: payForm.value.paidDate,

        paymentMethod: payForm.value.paymentMethod,

        reference: payForm.value.reference

    }


    closeModal()

}


function unmarkPaid(employee) {

    const confirmed =
        window.confirm(
            `Undo payment for ${employee.employeeName}? This will mark them as pending again.`
        )


    if (!confirmed) {
        return
    }


    const index =
        payrollData.value.findIndex(
            item =>
                item.id === employee.id
        )


    if (index !== -1) {

        payrollData.value[index] = {

            ...payrollData.value[index],

            paid: false,

            paidDate: null,

            paymentMethod: null,

            reference: ''

        }

    }

}


function markAllPaid() {

    const today =
        new Date()
            .toISOString()
            .slice(0, 10)


    const confirmed =
        window.confirm(
            `Mark all ${pendingCount.value} pending employees as paid via bank transfer today?`
        )


    if (!confirmed) {
        return
    }


    payrollData.value =
        payrollData.value.map(employee => {

            if (
                employee.status === 'active' &&
                !employee.paid
            ) {

                return {

                    ...employee,

                    paid: true,

                    paidDate: today,

                    paymentMethod: 'bank_transfer',

                    reference: 'Bulk payout'

                }

            }


            return employee

        })

}


// =====================================================
// CALCULATIONS
// =====================================================

function calculateNet(employee) {

    return (
        (Number(employee.basicSalary || 0) *
            Number(employee.totalAttendance || 0)) -
        Number(employee.deductions || 0)
    )

}


// =====================================================
// FORMATTING
// =====================================================

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


function formatMethod(method) {

    const labels = {

        bank_transfer: 'Bank Transfer',

        cash: 'Cash',

        check: 'Check',

        gcash: 'GCash'

    }


    return (
        labels[method] ||
        '—'
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


// =====================================================
// EXPORT
// =====================================================

function exportCsv() {

    const rows = [

        [
            'Employee',
            'Department',
            'Salary Type',
            'Net Salary',
            'Payment Status',
            'Paid On',
            'Payment Method',
            'Reference'
        ]

    ]


    filteredPayrollData.value.forEach(employee => {

        rows.push([

            employee.employeeName,

            employee.department,

            formatSalaryType(
                employee.salaryType
            ),

            calculateNet(employee),

            employee.paid ? 'PAID' : 'PENDING',

            employee.paidDate || '',

            formatMethod(employee.paymentMethod),

            employee.reference || ''

        ])

    })


    const csv =
        rows
            .map(row =>
                row
                    .map(cell =>
                        `"${String(cell ?? '').replace(/"/g, '""')}"`
                    )
                    .join(',')
            )
            .join('\n')


    const blob =
        new Blob(
            [csv],
            {
                type: 'text/csv;charset=utf-8;'
            }
        )


    const url =
        URL.createObjectURL(blob)


    const a =
        document.createElement('a')


    a.href = url

    a.download =
        'payroll.csv'


    document.body.appendChild(a)

    a.click()

    document.body.removeChild(a)


    URL.revokeObjectURL(url)

}


// =====================================================
// LIFECYCLE
// =====================================================

onMounted(() => {

    tickClock()

    clockTimer =
        setInterval(
            tickClock,
            1000
        )

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
    z-index: 9999;
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
</style>