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
                        Deductions
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

            <div class="row g-3">

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp green">
                            STAFF
                        </div>

                        <div class="stat-label">
                            Active Employees
                        </div>

                        <div class="stat-period">
                            With deduction records
                        </div>

                        <div class="stat-value">
                            {{ activeEmployeeCount }}
                        </div>

                        <div class="stat-delta stat-delta--slate">
                            Included this period
                        </div>

                    </div>

                </div>

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp gold">
                            GOV'T
                        </div>

                        <div class="stat-label">
                            Statutory Contributions
                        </div>

                        <div class="stat-period">
                            SSS + Cash Advance
                        </div>

                        <div class="stat-value stat-value-money">
                            {{ formatCurrency(totalStatutory) }}
                        </div>

                        <div class="stat-delta stat-delta--gold">
                            Employee share, monthly
                        </div>

                    </div>

                </div>

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp blue">
                            TAX
                        </div>

                        <div class="stat-label">
                            Withholding Tax
                        </div>

                        <div class="stat-period">
                            Monthly
                        </div>

                        <div class="stat-value stat-value-money">
                            {{ formatCurrency(totalTax) }}
                        </div>

                        <div class="stat-delta stat-delta--blue">
                            BIR withheld at source
                        </div>

                    </div>

                </div>

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp red">
                            TOTAL
                        </div>

                        <div class="stat-label">
                            Total Deductions
                        </div>

                        <div class="stat-period">
                            Monthly
                        </div>

                        <div class="stat-value stat-value-money">
                            {{ formatCurrency(totalDeductions) }}
                        </div>

                        <div class="stat-delta text-danger">
                            Across all active employees
                        </div>

                    </div>

                </div>

            </div>

            <div class="row g-3 mb-3">

                <div class="col-lg-8">

                    <div class="panel h-100">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>

                                <div class="panel-title">
                                    Deduction overview
                                </div>

                                <div class="panel-sub">
                                    Where deductions are coming from this period
                                </div>

                            </div>


                            <span class="chip">
                                Monthly
                            </span>

                        </div>


                        <div class="salary-overview">


                            <div class="salary-overview-main">

                                <div class="overview-label">
                                    TOTAL DEDUCTIONS
                                </div>

                                <div class="overview-value">
                                    {{ formatCurrency(totalDeductions) }}
                                </div>

                                <div class="overview-sub">
                                    {{ activeEmployeeCount }} active employees
                                </div>

                            </div>


                            <div class="salary-breakdown">

                                <div class="breakdown-item">

                                    <span class="breakdown-dot gold"></span>

                                    <div>

                                        <div class="breakdown-label">
                                            SSS
                                        </div>

                                        <div class="breakdown-value">
                                            {{ formatCurrency(totalSss) }}
                                        </div>

                                    </div>

                                </div>


                                <!-- <div class="breakdown-item">

                                    <span class="breakdown-dot green"></span>

                                    <div>

                                        <div class="breakdown-label">
                                            PhilHealth
                                        </div>

                                        <div class="breakdown-value">
                                            {{ formatCurrency(totalPhilhealth) }}
                                        </div>

                                    </div>

                                </div> -->


                                <div class="breakdown-item">

                                    <span class="breakdown-dot blue"></span>

                                    <div>

                                        <div class="breakdown-label">
                                            Cash Advance
                                        </div>

                                        <div class="breakdown-value">
                                            {{ formatCurrency(totalCA) }}
                                        </div>

                                    </div>

                                </div>


                                <div class="breakdown-item">

                                    <span class="breakdown-dot red"></span>

                                    <div>

                                        <div class="breakdown-label">
                                            Tax + Other
                                        </div>

                                        <div class="breakdown-value">
                                            {{ formatCurrency(totalTax + totalOther) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="panel salary-type-panel">
                        <div class="panel-title">
                            Coverage
                        </div>
                        <div class="panel-sub mb-3">
                            Employees with each deduction active
                        </div>
                        <div class="summary-list">
                            <div class="summary-row">
                                <div class="summary-label">
                                    <span class="summary-dot gold"></span>
                                    SSS
                                </div>
                                <div class="summary-value">
                                    {{ countWith('sss') }}
                                </div>
                            </div>
                            <div class="summary-row">
                                <div class="summary-label">
                                    <span class="summary-dot blue"></span>
                                    Cash Advance
                                </div>

                                <div class="summary-value">
                                    {{ countWith('ca') }}
                                </div>
                            </div>
                            <div class="summary-row">
                                <div class="summary-label">
                                    <span class="summary-dot red"></span>
                                    Loans / Other
                                </div>
                                <div class="summary-value">
                                    {{ countWithOther }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <div class="section-title mb-0">
                            Employee deductions
                        </div>
                        <div class="panel-sub">
                            Manage statutory contributions, tax, and other deductions per employee
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
                        active: statusFilter === filter.key
                    }" @click="statusFilter = filter.key">
                        {{ filter.label }}
                    </button>

                </div>
                <div class="table-responsive">
                    <table class="table-ledger salary-table" v-if="filteredData.length">
                        <thead>
                            <tr>
                                <th>Employee</th>

                                <th>Department</th>

                                <th>SSS</th>

                                <th>
                                    Cash Advance
                                </th>
                                <th>
                                    Other
                                </th>
                                <th>
                                    Total
                                </th>
                                <th>
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="employee in filteredData" :key="employee.id">
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

                                    <span class="department">
                                        {{ employee.department }}
                                    </span>

                                </td>


                                <td class="money deduction">
                                    {{ formatCurrency(employee.sss) }}
                                </td>

                                <td class="money deduction">
                                    {{ formatCurrency(employee.ca) }}
                                </td>


                                <td class="money">

                                    <span v-if="otherTotal(employee)">
                                        {{ formatCurrency(otherTotal(employee)) }}
                                    </span>

                                    <span v-else class="muted">
                                        —
                                    </span>

                                </td>


                                <td class="money net-pay">
                                    {{ formatCurrency(totalFor(employee)) }}
                                </td>


                                <td>

                                    <div class="action-group">

                                        <button class="action-btn edit-btn" @click="openEditModal(employee)">
                                            Edit
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
                            EDIT RECORD
                        </div>

                        <div class="modal-title">
                            {{ form.employeeName }}
                        </div>

                        <div class="modal-sub">
                            Configure deductions for this employee
                        </div>

                    </div>

                    <button class="close-btn" @click="closeModal" aria-label="Close">
                        ×
                    </button>

                </div>


                <div class="modal-body">
                    <div class="form-section-label">
                        Statutory Contributions
                    </div>


                    <div class="form-row">

                        <div class="form-group">

                            <label>
                                SSS
                            </label>

                            <div class="input-money">
                                <span>₱</span>
                                <input v-model.number="form.sss" type="number" min="0" step="0.01" class="form-control" placeholder="0.00" />
                            </div>

                        </div>


                        <div class="form-group">

                            <label>
                                Withholding Tax
                            </label>

                            <div class="input-money">
                                <span>₱</span>
                                <input v-model.number="form.ca" type="number" min="0" step="0.01" class="form-control" placeholder="0.00" />
                            </div>

                        </div>

                    </div>

                    <div class="form-section-label form-section-label--spaced">
                        Loans &amp; Other Deductions
                    </div>

                    <div v-if="!form.otherDeductions.length" class="other-empty">
                        No additional deductions added.
                    </div>

                    <div v-for="(item, index) in form.otherDeductions" :key="item.key" class="other-row">
                        <input v-model="item.label" type="text" class="form-control other-label-input" placeholder="e.g. Salary loan, Uniform" />
                        <div class="input-money other-amount-input">
                            <span>₱</span>
                            <input v-model.number="item.amount" type="number" min="0" step="0.01" class="form-control" placeholder="0.00" />
                        </div>
                        <button class="remove-btn" @click="removeOther(index)" aria-label="Remove deduction">
                            ×
                        </button>
                    </div>

                    <button class="add-line-btn" @click="addOther">
                        + Add deduction
                    </button>

                    <div class="salary-preview">
                        <div>
                            <div class="preview-label">
                                TOTAL DEDUCTIONS
                            </div>
                            <div class="preview-value">
                                {{ formatCurrency(formTotal) }}
                            </div>
                        </div>
                        <div class="preview-equation">

                            SSS + Cash Advance
                            <br />
                            + Other

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button class="cancel-btn" @click="closeModal">
                        Cancel
                    </button>


                    <button class="save-btn" @click="saveDeductions">
                        Save Changes
                    </button>

                </div>

            </div>

        </div>

    </div>
</template>


<script setup>

import {
    computed,
    onMounted,
    onBeforeUnmount,
    ref
} from 'vue'


defineOptions({
    name: 'DeductionsPage'
})


defineEmits([
    'toggle-sidebar'
])

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

const deductionsData = ref([

    {
        id: 1,
        employeeId: 1,
        employeeName: 'Jonas Diaz',
        initials: 'JD',
        department: 'Warehouse',
        status: 'active',
        image: null,
        sss: 675,
        ca: 675,
        otherDeductions: []
    },

    {
        id: 2,
        employeeId: 2,
        employeeName: 'Carla Santos',
        initials: 'CS',
        department: 'Accounting',
        status: 'active',
        image: null,
        sss: 675,
        ca: 675,
        otherDeductions: [
            {
                key: 'k1',
                label: 'Salary loan',
                amount: 500
            }
        ]
    },

    {
        id: 3,
        employeeId: 3,
        employeeName: 'Ramon Tan',
        initials: 'RT',
        department: 'Logistics',
        status: 'active',
        image: null,
        sss: 675,
        ca: 450,
        otherDeductions: []
    },

    {
        id: 4,
        employeeId: 4,
        employeeName: 'Paulo Lim',
        initials: 'PL',
        department: 'Customer Care',
        status: 'active',
        image: null,
        sss: 675,
        ca: 300,

        otherDeductions: [
            {
                key: 'k2',
                label: 'Uniform',
                amount: 150
            }
        ]
    },

    {
        id: 5,
        employeeId: 5,
        employeeName: 'Nadia Ang',
        initials: 'NA',
        department: 'Marketing',
        status: 'active',
        image: null,
        sss: 675,
        ca: 450,
        otherDeductions: []
    },

    {
        id: 6,
        employeeId: 6,
        employeeName: 'Erik Villar',
        initials: 'EV',
        department: 'Warehouse',
        status: 'active',
        image: null,
        sss: 675,
        ca: 450,

        otherDeductions: []
    }

])

const searchQuery = ref('')

const statusFilter = ref('all')


const statusFilters = [

    {
        key: 'all',
        label: 'All'
    },

    {
        key: 'active',
        label: 'Active'
    },

    {
        key: 'inactive',
        label: 'Inactive'
    }

]


const filteredData = computed(() => {

    const search =
        searchQuery.value
            .trim()
            .toLowerCase()


    return deductionsData.value.filter(employee => {

        const matchesSearch =
            !search ||
            employee.employeeName
                .toLowerCase()
                .includes(search) ||
            employee.department
                .toLowerCase()
                .includes(search)


        const matchesStatus =
            statusFilter.value === 'all' ||
            employee.status === statusFilter.value


        return matchesSearch && matchesStatus

    })

})

function otherTotal(employee) {

    return (employee.otherDeductions || []).reduce(
        (total, item) =>
            total + Number(item.amount || 0),
        0
    )

}

function totalFor(employee) {

    return (
        Number(employee.sss || 0) +
        Number(employee.ca || 0) +
        otherTotal(employee)
    )

}

const activeEmployees = computed(() => {

    return deductionsData.value.filter(
        employee =>
            employee.status === 'active'
    )

})


const activeEmployeeCount = computed(() => {

    return activeEmployees.value.length

})


function sumActive(field) {

    return activeEmployees.value.reduce(
        (total, employee) =>
            total + Number(employee[field] || 0),
        0
    )

}


const totalSss = computed(() => sumActive('sss'))

const totalCA = computed(() => sumActive('ca'))

const totalTax = computed(() => sumActive('withholdingTax'))


const totalOther = computed(() => {

    return activeEmployees.value.reduce(
        (total, employee) =>
            total + otherTotal(employee),
        0
    )

})


const totalStatutory = computed(() => {

    return (
        totalSss.value +
        totalCA.value
    )

})


const totalDeductions = computed(() => {

    return (
        totalStatutory.value +
        totalOther.value
    )

})


function countWith(field) {

    return activeEmployees.value.filter(
        employee =>
            Number(employee[field] || 0) > 0
    ).length

}


const countWithOther = computed(() => {

    return activeEmployees.value.filter(
        employee =>
            otherTotal(employee) > 0
    ).length

})

const showModal = ref(false)

let otherKeyCounter = 0


function createFormFrom(employee) {

    return {
        id: employee.id,
        employeeName: employee.employeeName,
        sss: employee.sss,
        ca: employee.ca,
        otherDeductions:
            (employee.otherDeductions || []).map(item => ({
                ...item
            }))
    }
}


const form = ref(
    createFormFrom({
        id: null,
        employeeName: '',
        sss: 0,
        ca: 0,
        otherDeductions: []
    })
)


const formTotal = computed(() => {

    const otherSum =
        form.value.otherDeductions.reduce(
            (total, item) =>
                total + Number(item.amount || 0),
            0
        )


    return (
        Number(form.value.sss || 0) +
        Number(form.value.ca || 0) +
        otherSum
    )

})


function openEditModal(employee) {

    form.value = createFormFrom(employee)

    showModal.value = true

}


function closeModal() {

    showModal.value = false

}


function addOther() {

    otherKeyCounter += 1

    form.value.otherDeductions.push({

        key: `new-${otherKeyCounter}`,

        label: '',

        amount: 0

    })

}


function removeOther(index) {

    form.value.otherDeductions.splice(index, 1)

}


function saveDeductions() {

    const index =
        deductionsData.value.findIndex(
            employee =>
                employee.id === form.value.id
        )


    if (index === -1) {
        return
    }


    deductionsData.value[index] = {

        ...deductionsData.value[index],

        sss: Number(form.value.sss || 0),

        ca: Number(form.value.ca || 0),

        otherDeductions:
            form.value.otherDeductions

                .filter(
                    item => item.label.trim() !== '' || Number(item.amount) > 0
                )

                .map(item => ({
                    ...item
                }))

    }


    closeModal()

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


function exportCsv() {

    const rows = [

        [
            'Employee',
            'Department',
            'SSS',
            'ca',
            'Other Deductions',
            'Total Deductions',
            'Status'
        ]

    ]


    filteredData.value.forEach(employee => {

        rows.push([

            employee.employeeName,

            employee.department,

            employee.sss,

            employee.ca,

            otherTotal(employee),

            totalFor(employee),

            employee.status

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
        'deductions.csv'


    document.body.appendChild(a)

    a.click()

    document.body.removeChild(a)


    URL.revokeObjectURL(url)

}

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
    font-size: 1.55rem;
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


.text-danger {
    color: var(--red, #C24D3B);
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


.stamp.red {
    color: var(--red, #C24D3B);
    border-color: #E5B7AE;
}


/* =====================================================
   OVERVIEW
===================================================== */

.chip {
    font-size: .72rem;
    padding: .28rem .6rem;
    border-radius: 6px;
    font-weight: 600;
    background: var(--amber-bg, #F6EEDB);
    color: var(--gold-dark, #9C7726);
    white-space: nowrap;
}


.salary-overview {
    margin-top: 1.4rem;
    display: flex;
    align-items: stretch;
    gap: 2rem;
}


.salary-overview-main {
    flex: 1;
    padding-right: 2rem;
    border-right: 1px solid var(--line, #DCD8CB);
}


.overview-label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .64rem;
    letter-spacing: .08em;
    color: var(--slate, #6B7280);
    font-weight: 600;
}


.overview-value {
    font-family: 'Fraunces', serif;
    font-size: 2.3rem;
    font-weight: 600;
    color: var(--ink, #1C2B4A);
    margin-top: .2rem;
}


.overview-sub {
    color: var(--slate, #6B7280);
    font-size: .75rem;
    margin-top: .15rem;
}


.salary-breakdown {
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


.breakdown-dot.red {
    background: var(--red, #C24D3B);
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


/* =====================================================
   SUMMARY
===================================================== */

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


/* =====================================================
   SEARCH / FILTERS
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


.deduction {
    color: var(--red, #C24D3B);
}


.net-pay {
    font-weight: 600;
    color: var(--ink, #1C2B4A);
}


.muted {
    color: var(--slate, #6B7280);
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


.edit-btn {
    border-color: #6D94B6;
    background: #E8EEF3;
    color: #426B8F;
}


.edit-btn:hover {
    background: #426B8F;
    color: white;
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
    width: min(620px, 100%);
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


.form-section-label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .64rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--gold-dark, #9C7726);
    font-weight: 600;
    margin-bottom: .7rem;
}


.form-section-label--spaced {
    margin-top: 1.3rem;
    padding-top: 1.1rem;
    border-top: 1px dashed var(--line, #DCD8CB);
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


.input-money {
    position: relative;
}


.input-money>span {
    position: absolute;
    left: .7rem;
    top: 50%;
    transform: translateY(-50%);
    font-family: 'IBM Plex Mono', monospace;
    color: var(--slate, #6B7280);
    font-size: .8rem;
    pointer-events: none;
}


.input-money .form-control {
    padding-left: 1.55rem;
}


/* =====================================================
   OTHER DEDUCTIONS
===================================================== */

.other-empty {
    font-size: .78rem;
    color: var(--slate, #6B7280);
    padding: .6rem 0 1rem;
}


.other-row {
    display: grid;
    grid-template-columns: 1fr 140px auto;
    gap: .6rem;
    align-items: center;
    margin-bottom: .6rem;
}


.other-label-input {
    font-size: .8rem;
}


.other-amount-input .form-control {
    padding-left: 1.55rem;
}


.remove-btn {
    width: 32px;
    height: 32px;
    flex-shrink: 0;
    border: 1px solid #E5B7AE;
    background: var(--red-bg, #F7E9E6);
    color: var(--red, #C24D3B);
    border-radius: 7px;
    font-size: 1.1rem;
    line-height: 1;
    cursor: pointer;
}


.remove-btn:hover {
    background: var(--red, #C24D3B);
    color: white;
}


.add-line-btn {
    border: 1px dashed var(--line, #DCD8CB);
    background: transparent;
    color: var(--ink-2, #28395E);
    border-radius: 7px;
    padding: .5rem .8rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: .72rem;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    margin-bottom: 1.1rem;
}


.add-line-btn:hover {
    background: var(--paper, #F2F1EA);
    border-color: var(--gold, #C79A3D);
    color: var(--gold-dark, #9C7726);
}


/* =====================================================
   PREVIEW
===================================================== */

.salary-preview {
    margin-top: .4rem;
    padding: 1rem;
    border: 1px solid #E5B7AE;
    background: var(--red-bg, #F7E9E6);
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
    color: var(--red, #C24D3B);
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

    .col-lg-4 {
        width: 33.3333%;
    }

    .col-lg-8 {
        width: 66.6667%;
    }

}


@media (max-width: 991px) {

    .salary-overview {
        flex-direction: column;
        gap: 1.25rem;
    }


    .salary-overview-main {
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


    .col-6 {
        width: 100%;
    }


    .form-row {
        grid-template-columns: 1fr;
    }


    .other-row {
        grid-template-columns: 1fr;
    }


    .remove-btn {
        justify-self: end;
    }


    .search-box {
        min-width: 100%;
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