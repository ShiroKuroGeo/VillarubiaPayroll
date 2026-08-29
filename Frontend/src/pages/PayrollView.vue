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
                        Saturday, August 29, 2026
                    </div>

                    <h1>
                        Payroll
                    </h1>
                </div>

            </div>

            <div class="d-flex align-items-center gap-3">

                <div class="clock-chip">
                    <span class="dot"></span>
                    <span>{{ liveClock }}</span>
                </div>

                <button class="btn btn-outline-ledger btn-sm" @click="exportCsv">
                    Export
                </button>

            </div>

        </div>


        <div class="content">

            <!-- Salary Date Selector -->
            <div class="panel salary-date-panel">

                <div class="date-selector-content">

                    <div>
                        <div class="panel-title">
                            Salary date
                        </div>

                        <div class="panel-sub">
                            Select a Saturday to view employee payroll
                        </div>
                    </div>

                    <div class="date-control">

                        <label for="salaryDate">
                            Salary Date
                        </label>

                        <VueDatePicker v-model="selectedSalaryDate" :disabled-dates="isNotSaturday"
                            format="MMMM d, yyyy" :enable-time-picker="false" auto-apply
                            placeholder="Select payroll Saturday" />

                        <!-- <div v-if="!isSaturday" class="date-error">
                            Please select a Saturday.
                        </div> -->

                    </div>

                </div>

            </div>


            <!-- Summary Cards -->
            <div class="row g-3">

                <!-- Employees -->
                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp green">
                            STAFF
                        </div>

                        <div class="stat-label">
                            Total Employees
                        </div>

                        <div class="stat-period">
                            {{ formattedSelectedDate }}
                        </div>

                        <div class="stat-value">
                            {{ totalEmployees }}
                        </div>

                        <div class="stat-delta stat-delta--slate">
                            Active employees
                        </div>

                    </div>

                </div>


                <!-- Pending -->
                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp gold">
                            WAIT
                        </div>

                        <div class="stat-label">
                            Pending Payroll
                        </div>

                        <div class="stat-period">
                            {{ formattedSelectedDate }}
                        </div>

                        <div class="stat-value">
                            {{ pendingCount }}
                        </div>

                        <div class="stat-delta stat-delta--gold">
                            Requires processing
                        </div>

                    </div>

                </div>


                <!-- Completed -->
                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp green">
                            DONE
                        </div>

                        <div class="stat-label">
                            Completed
                        </div>

                        <div class="stat-period">
                            {{ formattedSelectedDate }}
                        </div>

                        <div class="stat-value">
                            {{ completedCount }}
                        </div>

                        <div class="stat-delta text-success">
                            Ready for payment
                        </div>

                    </div>

                </div>


                <!-- Paid -->
                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp blue">
                            PAID
                        </div>

                        <div class="stat-label">
                            Paid Employees
                        </div>

                        <div class="stat-period">
                            {{ formattedSelectedDate }}
                        </div>

                        <div class="stat-value">
                            {{ paidCount }}
                        </div>

                        <div class="stat-delta stat-delta--blue">
                            Salary released
                        </div>

                    </div>

                </div>

            </div>


            <!-- Payroll Overview -->
            <div class="row g-3 mb-3">

                <!-- Total Payroll -->
                <div class="col-lg-8">

                    <div class="panel payroll-total-panel">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>
                                <div class="panel-title">
                                    Payroll overview
                                </div>

                                <div class="panel-sub">
                                    Employee salary for {{ formattedSelectedDate }}
                                </div>
                            </div>

                            <span class="chip">
                                Saturday payroll
                            </span>

                        </div>


                        <div class="payroll-overview">

                            <div class="payroll-total">

                                <div class="total-label">
                                    TOTAL NET PAYROLL
                                </div>

                                <div class="total-value">
                                    {{ formatCurrency(totalPayroll) }}
                                </div>

                                <div class="total-sub">
                                    {{ payrollRecords.length }} employees
                                </div>

                            </div>


                            <div class="payroll-breakdown">

                                <div class="breakdown-item">
                                    <span class="breakdown-dot gold"></span>

                                    <div>
                                        <div class="breakdown-label">
                                            Pending
                                        </div>

                                        <div class="breakdown-value">
                                            {{ formatCurrency(pendingAmount) }}
                                        </div>
                                    </div>
                                </div>


                                <div class="breakdown-item">
                                    <span class="breakdown-dot green"></span>

                                    <div>
                                        <div class="breakdown-label">
                                            Completed
                                        </div>

                                        <div class="breakdown-value">
                                            {{ formatCurrency(completedAmount) }}
                                        </div>
                                    </div>
                                </div>


                                <div class="breakdown-item">
                                    <span class="breakdown-dot blue"></span>

                                    <div>
                                        <div class="breakdown-label">
                                            Paid
                                        </div>

                                        <div class="breakdown-value">
                                            {{ formatCurrency(paidAmount) }}
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-lg-4">

                    <div class="panel payment-summary-panel">

                        <div class="panel-title">
                            Payment summary
                        </div>

                        <div class="panel-sub mb-3">
                            {{ formattedSelectedDate }}
                        </div>


                        <div class="summary-list">

                            <div class="summary-row">

                                <div class="summary-label">
                                    <span class="summary-dot gold"></span>
                                    Pending
                                </div>

                                <div class="summary-value">
                                    {{ pendingCount }}
                                </div>

                            </div>


                            <div class="summary-row">

                                <div class="summary-label">
                                    <span class="summary-dot green"></span>
                                    Completed
                                </div>

                                <div class="summary-value">
                                    {{ completedCount }}
                                </div>

                            </div>

                            <div class="summary-row">

                                <div class="summary-label">
                                    <span class="summary-dot blue"></span>
                                    Paid
                                </div>

                                <div class="summary-value">
                                    {{ paidCount }}
                                </div>

                            </div>

                            <div class="summary-row">

                                <div class="summary-label">
                                    <span class="summary-dot red"></span>
                                    On hold
                                </div>

                                <div class="summary-value">
                                    {{ onHoldCount }}
                                </div>

                            </div>

                        </div>


                        <div class="payment-rate">

                            <div>
                                <div class="rate-label">
                                    PAYMENT COMPLETION
                                </div>

                                <div class="rate-value">
                                    {{ paymentRate }}%
                                </div>
                            </div>

                            <div class="rate-ring" :class="{ complete: paymentRate === '100.0' }">
                                {{ paymentRate }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                    <div>

                        <div class="section-title mb-0">
                            Employee payroll
                        </div>

                        <div class="panel-sub">
                            Salary records · {{ formattedSelectedDate }}
                        </div>

                    </div>


                    <div class="d-flex gap-2 flex-wrap">

                        <button v-for="f in statusFilters" :key="f.key" class="filter-pill" :class="{
                            active: statusFilter === f.key
                        }" @click="statusFilter = f.key">
                            {{ f.label }}
                        </button>

                    </div>

                </div>


                <div class="table-responsive">

                    <table class="table-ledger payroll-table" v-if="filteredPayroll.length">

                        <thead>

                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Basic Salary</th>
                                <th>Overtime</th>
                                <th>Deductions</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                            </tr>

                        </thead>


                        <tbody>

                            <tr v-for="p in filteredPayroll" :key="p.id">

                                <td>

                                    <div class="d-flex align-items-center gap-2">

                                        <div class="avatar-sm">
                                            {{ p.initials }}
                                        </div>

                                        <div>

                                            <div class="emp-name">
                                                {{ p.name }}
                                            </div>

                                            <div class="emp-role">
                                                Employee #{{ p.id.toString().padStart(4, '0') }}
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <td>
                                    <span class="department">
                                        {{ p.role }}
                                    </span>
                                </td>


                                <td class="money">
                                    {{ formatCurrency(p.basicSalary) }}
                                </td>


                                <td class="money">
                                    {{ formatCurrency(p.overtime) }}
                                </td>


                                <td class="money deduction">
                                    -{{ formatCurrency(p.deductions) }}
                                </td>


                                <td class="money net-pay">
                                    {{ formatCurrency(p.netSalary) }}
                                </td>


                                <td>

                                    <span class="badge-status" :class="badgeClass(p.status)">
                                        {{ formatStatus(p.status) }}
                                    </span>

                                </td>

                            </tr>

                        </tbody>

                    </table>


                    <div class="empty-state" v-else>
                        No payroll records match this filter.
                    </div>

                </div>

            </div>

        </div>

    </div>
</template>


<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue'

import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

defineOptions({
    name: 'PayrollPage',
})


defineEmits([
    'toggle-sidebar'
])


// ─────────────────────────────────────────────
// Clock
// ─────────────────────────────────────────────

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

const selectedSalaryDate = ref(new Date('2026-08-29'))

function isNotSaturday(date) {
    return date.getDay() !== 6
}

function formatDate(date) {
    if (!date) return ''

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`
}

const formattedSelectedDate = computed(() => {

    if (!selectedSalaryDate.value) {
        return 'No date selected'
    }

    return selectedSalaryDate.value.toLocaleDateString(
        'en-US',
        {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        }
    )

})

const totalEmployees = ref(248)

const payrollData = ref([
    {
        id: 1,
        initials: 'JD',
        name: 'Jonas Diaz',
        role: 'Warehouse',

        basicSalary: 25000,
        overtime: 1200,
        deductions: 500,
        netSalary: 25700,

        date: '2026-08-29',
        status: 'pending'
    },

    {
        id: 2,
        initials: 'CS',
        name: 'Carla Santos',
        role: 'Accounting',

        basicSalary: 28000,
        overtime: 1500,
        deductions: 800,
        netSalary: 28700,

        date: '2026-08-29',
        status: 'paid'
    },

    {
        id: 3,
        initials: 'RT',
        name: 'Ramon Tan',
        role: 'Logistics',

        basicSalary: 24000,
        overtime: 1000,
        deductions: 400,
        netSalary: 24600,

        date: '2026-08-22',
        status: 'completed'
    },

    {
        id: 4,
        initials: 'PL',
        name: 'Paulo Lim',
        role: 'Customer Care',

        basicSalary: 26000,
        overtime: 800,
        deductions: 600,
        netSalary: 26200,

        date: '2026-08-22',
        status: 'pending'
    }
])

const statusFilters = [

    {
        key: 'all',
        label: 'All'
    },

    {
        key: 'pending',
        label: 'Pending'
    },

    {
        key: 'processing',
        label: 'Processing'
    },

    {
        key: 'completed',
        label: 'Completed'
    },

    {
        key: 'paid',
        label: 'Paid'
    },

    {
        key: 'on-hold',
        label: 'On hold'
    }

]


const statusFilter = ref('all')


const payrollRecords = computed(() => {

    const selectedDate = formatDate(
        selectedSalaryDate.value
    )

    return payrollData.value.filter(
        p => p.date === selectedDate
    )

})

const filteredPayroll = computed(() => {

    if (statusFilter.value === 'all') {
        return payrollRecords.value
    }

    return payrollRecords.value.filter(
        employee =>
            employee.status === statusFilter.value
    )

})


// ─────────────────────────────────────────────
// Statistics
// ─────────────────────────────────────────────

const pendingCount = computed(() => {

    return payrollRecords.value.filter(
        p =>
            p.status === 'pending'
    ).length

})


const completedCount = computed(() => {

    return payrollRecords.value.filter(
        p =>
            p.status === 'completed'
    ).length

})


const paidCount = computed(() => {

    return payrollRecords.value.filter(
        p =>
            p.status === 'paid'
    ).length

})


const onHoldCount = computed(() => {

    return payrollRecords.value.filter(
        p =>
            p.status === 'on-hold'
    ).length

})


const processingCount = computed(() => {

    return payrollRecords.value.filter(
        p =>
            p.status === 'processing'
    ).length

})


const totalPayroll = computed(() => {

    return payrollRecords.value.reduce(
        (total, p) =>
            total + p.netSalary,
        0
    )

})


const pendingAmount = computed(() => {

    return payrollRecords.value
        .filter(p => p.status === 'pending')
        .reduce(
            (total, p) =>
                total + p.netSalary,
            0
        )

})


const completedAmount = computed(() => {

    return payrollRecords.value
        .filter(p => p.status === 'completed')
        .reduce(
            (total, p) =>
                total + p.netSalary,
            0
        )

})


const paidAmount = computed(() => {

    return payrollRecords.value
        .filter(p => p.status === 'paid')
        .reduce(
            (total, p) =>
                total + p.netSalary,
            0
        )

})


const paymentRate = computed(() => {

    if (!payrollRecords.value.length) {
        return '0.0'
    }

    return (
        paidCount.value /
        payrollRecords.value.length *
        100
    ).toFixed(1)

})


// ─────────────────────────────────────────────
// Formatting
// ─────────────────────────────────────────────

function formatCurrency(amount) {

    return new Intl.NumberFormat(
        'en-PH',
        {
            style: 'currency',
            currency: 'PHP',
            minimumFractionDigits: 2
        }
    ).format(amount)

}


function formatStatus(status) {

    const labels = {

        pending: 'PENDING',

        processing: 'PROCESSING',

        completed: 'COMPLETED',

        paid: 'PAID',

        'on-hold': 'ON HOLD'

    }

    return labels[status] || status.toUpperCase()

}


function badgeClass(status) {

    return {

        pending: 'badge-pending',

        processing: 'badge-processing',

        completed: 'badge-completed',

        paid: 'badge-paid',

        'on-hold': 'badge-on-hold'

    }[status]

}


// ─────────────────────────────────────────────
// Export
// ─────────────────────────────────────────────

function exportCsv() {

    const rows = [

        [
            'Employee',
            'Department',
            'Basic Salary',
            'Overtime',
            'Deductions',
            'Net Salary',
            'Status',
            'Paid Date'
        ]

    ]


    payrollRecords.value.forEach(p => {

        rows.push([

            p.name,

            p.role,

            p.basicSalary,

            p.overtime,

            p.deductions,

            p.netSalary,

            p.status,

            p.paidAt || ''

        ])

    })


    const csv =
        rows
            .map(row =>
                row
                    .map(cell =>
                        `"${cell}"`
                    )
                    .join(',')
            )
            .join('\n')


    const blob =
        new Blob(
            [csv],
            {
                type: 'text/csv'
            }
        )


    const url =
        URL.createObjectURL(blob)


    const a =
        document.createElement('a')


    a.href = url

    a.download =
        `payroll-${selectedSalaryDate.value}.csv`

    a.click()


    URL.revokeObjectURL(url)

}


// ─────────────────────────────────────────────
// Lifecycle
// ─────────────────────────────────────────────

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
.main {
    flex: 1;
    min-width: 0;
}


/* ─────────────────────────────────────────────
   Topbar
───────────────────────────────────────────── */

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


/* ─────────────────────────────────────────────
   Clock
───────────────────────────────────────────── */

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

    box-shadow:
        0 0 0 3px rgba(47, 143, 91, .25);
}


/* ─────────────────────────────────────────────
   Content
───────────────────────────────────────────── */

.content {
    padding: 1.75rem;
}


/* ─────────────────────────────────────────────
   Date Selector
───────────────────────────────────────────── */

.salary-date-panel {
    padding: 1.2rem 1.4rem;
}


.date-selector-content {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 1.5rem;
    flex-wrap: wrap;
}


.date-control {
    min-width: 250px;
}


.date-control label {
    display: block;

    font-family: 'IBM Plex Mono', monospace;

    font-size: .62rem;

    text-transform: uppercase;

    letter-spacing: .08em;

    color: var(--slate, #6B7280);

    font-weight: 600;

    margin-bottom: .35rem;
}


.date-picker {
    width: 100%;

    box-sizing: border-box;

    border: 1px solid var(--line, #DCD8CB);

    background: var(--paper-2, #FBFAF6);

    color: var(--ink, #1C2B4A);

    border-radius: 7px;

    padding: .55rem .7rem;

    font-family: 'IBM Plex Mono', monospace;

    font-size: .78rem;

    outline: none;
}


.date-picker:focus {
    border-color: var(--gold, #C79A3D);

    box-shadow:
        0 0 0 3px rgba(199, 154, 61, .12);
}


.date-picker.invalid {
    border-color: var(--red, #C24D3B);
}


.date-error {
    color: var(--red, #C24D3B);

    font-size: .7rem;

    margin-top: .3rem;
}


/* ─────────────────────────────────────────────
   Stats
───────────────────────────────────────────── */

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

    background-image:
        radial-gradient(circle,
            var(--paper, #F2F1EA) 3px,
            transparent 3.2px);

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


/* ─────────────────────────────────────────────
   Stamps
───────────────────────────────────────────── */

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


/* ─────────────────────────────────────────────
   Panels
───────────────────────────────────────────── */

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


.panel-sub {
    font-size: .78rem;

    color: var(--slate, #6B7280);
}


/* ─────────────────────────────────────────────
   Payroll Overview
───────────────────────────────────────────── */

.payroll-total-panel {
    height: 100%;
}


.payroll-overview {
    margin-top: 1.4rem;

    display: flex;

    align-items: stretch;

    gap: 2rem;
}


.payroll-total {
    flex: 1;

    padding-right: 2rem;

    border-right: 1px solid var(--line, #DCD8CB);
}


.total-label {
    font-family: 'IBM Plex Mono', monospace;

    font-size: .64rem;

    letter-spacing: .08em;

    color: var(--slate, #6B7280);

    font-weight: 600;
}


.total-value {
    font-family: 'Fraunces', serif;

    font-size: 2.3rem;

    font-weight: 600;

    color: var(--ink, #1C2B4A);

    margin-top: .2rem;
}


.total-sub {
    color: var(--slate, #6B7280);

    font-size: .75rem;

    margin-top: .15rem;
}


.payroll-breakdown {
    flex: 1;

    display: flex;

    flex-direction: column;

    justify-content: center;

    gap: .85rem;
}


.breakdown-item {
    display: flex;

    align-items: center;

    gap: .65rem;
}


.breakdown-dot {
    width: 8px;
    height: 8px;

    border-radius: 50%;

    flex-shrink: 0;
}


.breakdown-dot.gold {
    background: var(--gold, #C79A3D);
}


.breakdown-dot.green {
    background: var(--green, #2F8F5B);
}


.breakdown-dot.blue {
    background: #426B8F;
}


.breakdown-label {
    font-size: .7rem;

    color: var(--slate, #6B7280);
}


.breakdown-value {
    font-family: 'IBM Plex Mono', monospace;

    font-size: .78rem;

    font-weight: 600;

    color: var(--ink-2, #28395E);
}


/* ─────────────────────────────────────────────
   Payment Summary
───────────────────────────────────────────── */

.payment-summary-panel {
    height: 100%;
}


.summary-list {
    border-top: 1px solid var(--line, #DCD8CB);
}


.summary-row {
    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: .78rem .1rem;

    border-bottom: 1px dashed var(--line, #DCD8CB);
}


.summary-label {
    display: flex;

    align-items: center;

    gap: .6rem;

    font-size: .84rem;
}


.summary-value {
    font-family: 'IBM Plex Mono', monospace;

    font-weight: 600;

    color: var(--ink-2, #28395E);
}


.summary-dot {
    width: 8px;
    height: 8px;

    border-radius: 50%;
}


.summary-dot.gold {
    background: var(--gold, #C79A3D);
}


.summary-dot.green {
    background: var(--green, #2F8F5B);
}


.summary-dot.blue {
    background: #426B8F;
}


.summary-dot.red {
    background: var(--red, #C24D3B);
}


.payment-rate {
    display: flex;

    align-items: center;

    justify-content: space-between;

    padding-top: 1rem;

    margin-top: .3rem;
}


.rate-label {
    font-family: 'IBM Plex Mono', monospace;

    font-size: .62rem;

    letter-spacing: .08em;

    color: var(--slate, #6B7280);

    font-weight: 600;
}


.rate-value {
    font-family: 'Fraunces', serif;

    font-size: 1.8rem;

    font-weight: 600;

    color: var(--ink, #1C2B4A);

    margin-top: .15rem;
}


.rate-ring {
    width: 62px;
    height: 62px;

    border-radius: 50%;

    border: 5px solid #6D94B6;

    display: flex;

    align-items: center;

    justify-content: center;

    font-family: 'IBM Plex Mono', monospace;

    font-size: .7rem;

    font-weight: 600;

    color: #426B8F;

    background: #E8EEF3;
}


.rate-ring.complete {
    border-color: var(--green, #2F8F5B);

    color: var(--green, #2F8F5B);

    background: var(--green-bg, #E5F2EA);
}


/* ─────────────────────────────────────────────
   Table
───────────────────────────────────────────── */

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

    border-bottom:
        1px solid var(--line, #DCD8CB);

    font-weight: 600;

    padding:
        .5rem .5rem .65rem;

    background: transparent;

    text-align: left;

    white-space: nowrap;
}


.table-ledger tbody td {
    padding: .75rem .5rem;

    border-bottom:
        1px dashed var(--line, #DCD8CB);

    vertical-align: middle;

    font-size: .84rem;
}


.table-ledger tbody tr:last-child td {
    border-bottom: none;
}


.money {
    font-family: 'IBM Plex Mono', monospace;

    font-size: .76rem;

    color: var(--ink-2, #28395E);

    white-space: nowrap;
}


.deduction {
    color: var(--red, #C24D3B);
}


.net-pay {
    font-weight: 600;

    color: var(--ink, #1C2B4A);
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


/* ─────────────────────────────────────────────
   Avatar
───────────────────────────────────────────── */

.avatar-sm {
    width: 30px;
    height: 30px;

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


/* ─────────────────────────────────────────────
   Status
───────────────────────────────────────────── */

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


.badge-pending {
    background: var(--amber-bg, #F6EEDB);

    color: var(--gold-dark, #9C7726);
}


.badge-processing {
    background: #E8EEF3;

    color: #426B8F;
}


.badge-completed {
    background: var(--green-bg, #E5F2EA);

    color: var(--green, #2F8F5B);
}


.badge-paid {
    background: #E8EEF3;

    color: #426B8F;
}


.badge-on-hold {
    background: var(--red-bg, #F7E9E6);

    color: var(--red, #C24D3B);
}


/* ─────────────────────────────────────────────
   Filters
───────────────────────────────────────────── */

.filter-pill {
    font-family: 'IBM Plex Mono', monospace;

    font-size: .7rem;

    font-weight: 600;

    padding: .32rem .65rem;

    border-radius: 20px;

    letter-spacing: .03em;

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper-2, #FBFAF6);

    color:
        var(--slate, #6B7280);

    cursor: pointer;

    transition: all .15s ease;
}


.filter-pill.active {
    background:
        var(--ink, #1C2B4A);

    color: #F3DFA6;

    border-color:
        var(--ink, #1C2B4A);
}


.filter-pill:hover:not(.active) {
    background:
        var(--paper, #F2F1EA);
}

/* ─────────────────────────────────────────────
   Warning
───────────────────────────────────────────── */

.warning-box {
    display: flex;

    align-items: center;

    gap: .8rem;

    padding: 1rem;

    border: 1px solid #E5B7AE;

    background: var(--red-bg, #F7E9E6);

    border-radius: 8px;
}


.warning-icon {
    width: 28px;
    height: 28px;

    border-radius: 50%;

    background: var(--red, #C24D3B);

    color: #fff;

    display: flex;

    align-items: center;

    justify-content: center;

    font-weight: 700;
}


.warning-title {
    color: var(--red, #C24D3B);

    font-weight: 600;

    font-size: .82rem;
}


.warning-text {
    color: var(--slate, #6B7280);

    font-size: .75rem;

    margin-top: .1rem;
}


/* ─────────────────────────────────────────────
   Section
───────────────────────────────────────────── */

.section-title {
    font-family: 'Fraunces', serif;

    font-weight: 600;

    font-size: 1.1rem;

    margin-bottom: 1rem;
}


.chip {
    font-size: .72rem;

    padding: .28rem .6rem;

    border-radius: 6px;

    font-weight: 600;

    background: var(--amber-bg, #F6EEDB);

    color: var(--gold-dark, #9C7726);

    white-space: nowrap;
}


/* ─────────────────────────────────────────────
   Buttons
───────────────────────────────────────────── */

.btn {
    border-radius: 6px;

    padding: .45rem .9rem;

    border: 1px solid transparent;

    cursor: pointer;
}


.btn-outline-ledger {
    border:
        1px solid var(--line, #DCD8CB);

    color:
        var(--ink-2, #28395E);

    font-size: .85rem;

    font-weight: 500;

    background:
        var(--paper-2, #FBFAF6);
}


.btn-outline-ledger:hover {
    background:
        var(--paper, #F2F1EA);
}


.btn-sm {
    font-size: .82rem;

    padding: .4rem .8rem;
}


/* ─────────────────────────────────────────────
   Empty
───────────────────────────────────────────── */

.empty-state {
    text-align: center;

    padding: 2rem 1rem;

    color: var(--slate, #6B7280);

    font-size: .85rem;
}


/* ─────────────────────────────────────────────
   Layout Helpers
───────────────────────────────────────────── */

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


.mb-4 {
    margin-bottom: 1.5rem;
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

    .col-lg-4 {
        width: 33.3333%;
    }

    .col-lg-8 {
        width: 66.6667%;
    }

}


@media (max-width: 991px) {

    .col-6 {
        width: 100%;
    }

    .payroll-overview {
        flex-direction: column;

        gap: 1.25rem;
    }

    .payroll-total {
        border-right: none;

        border-bottom: 1px solid var(--line, #DCD8CB);

        padding-right: 0;

        padding-bottom: 1.25rem;
    }

}


@media (max-width: 576px) {

    .content {
        padding: 1rem;
    }

    .topbar {
        padding: 1rem;
    }

    .date-control {
        width: 100%;

        min-width: 0;
    }

    .total-value {
        font-size: 1.9rem;
    }

}
</style>
