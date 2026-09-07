
<template>
    <div class="main">

        <!-- =====================================================
             TOPBAR
        ====================================================== -->

        <div class="topbar">

            <div class="d-flex align-items-center gap-2">

                <button class="btn-menu d-lg-none" @click="$emit('toggle-sidebar')" aria-label="Toggle menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12h18M3 6h18M3 18h18" />
                    </svg>
                </button>


                <div>

                    <div class="eyebrow">
                        {{ currentDate }}
                    </div>

                    <h1>
                        SSS Contribution Management
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


        <!-- =====================================================
             CONTENT
        ====================================================== -->

        <div class="content">


            <!-- =====================================================
                 STATISTICS
            ====================================================== -->

            <div class="row g-3 mb-3">


                <!-- TOTAL RECORDS -->

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp green">
                            REC
                        </div>

                        <div class="stat-label">
                            Total Records
                        </div>

                        <div class="stat-period">
                            All SSS contributions
                        </div>

                        <div class="stat-value">
                            {{ contributionRecords.length }}
                        </div>

                        <div class="stat-delta stat-delta--slate">
                            Contribution entries
                        </div>

                    </div>

                </div>


                <!-- PENDING -->

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp gold">
                            PEN
                        </div>

                        <div class="stat-label">
                            Pending
                        </div>

                        <div class="stat-period">
                            Awaiting posting
                        </div>

                        <div class="stat-value">
                            {{ pendingCount }}
                        </div>

                        <div class="stat-delta stat-delta--gold">
                            Pending contributions
                        </div>

                    </div>

                </div>


                <!-- POSTED -->

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp blue">
                            PST
                        </div>

                        <div class="stat-label">
                            Posted
                        </div>

                        <div class="stat-period">
                            Completed records
                        </div>

                        <div class="stat-value">
                            {{ postedCount }}
                        </div>

                        <div class="stat-delta stat-delta--blue">
                            Successfully posted
                        </div>

                    </div>

                </div>


                <!-- TOTAL AMOUNT -->

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp green">
                            PHP
                        </div>

                        <div class="stat-label">
                            Total Contribution
                        </div>

                        <div class="stat-period">
                            Current records
                        </div>

                        <div class="stat-value stat-value-money">
                            {{ formatCurrency(totalAmount) }}
                        </div>

                        <div class="stat-delta text-success">
                            Total SSS amount
                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 SSS CONTRIBUTION RECORDS
            ====================================================== -->

            <div class="panel">


                <!-- HEADER -->

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                    <div>

                        <div class="section-title mb-0">
                            SSS Contribution Records
                        </div>

                        <div class="panel-sub">
                            Manually record SSS contribution amounts per employee
                        </div>

                    </div>


                    <div class="d-flex gap-2 flex-wrap">


                        <!-- SEARCH -->

                        <div class="search-box">

                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7" />

                                <path d="m20 20-3-3" />
                            </svg>


                            <input v-model="searchQuery" type="text" placeholder="Search employee..." />

                        </div>


                        <!-- ADD -->

                        <button class="add-btn" @click="openAddContributionModal">
                            + Add Contribution
                        </button>

                    </div>

                </div>


                <!-- =====================================================
                     FILTERS
                ====================================================== -->

                <div class="filter-row">


                    <button v-for="filter in statusFilters" :key="filter.key" class="filter-pill" :class="{
                        active: statusFilter === filter.key
                    }" @click="statusFilter = filter.key">
                        {{ filter.label }}
                    </button>


                    <!-- PAYROLL FILTER -->

                    <select v-model="payrollFilter" class="filter-select">

                        <option value="all">
                            All Payrolls
                        </option>

                        <option v-for="payroll in payrollOptions" :key="payroll.id" :value="payroll.id">
                            {{ payroll.label }}
                        </option>

                    </select>

                </div>


                <!-- =====================================================
                     TABLE
                ====================================================== -->

                <div class="table-responsive">


                    <table v-if="paginatedContributionRecords.length" class="table-ledger">

                        <thead>

                            <tr>

                                <th>
                                    Employee
                                </th>

                                <th>
                                    Payroll
                                </th>

                                <th>
                                    SSS Amount
                                </th>

                                <th>
                                    Date
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

                            <tr v-for="record in paginatedContributionRecords" :key="record.id">


                                <!-- EMPLOYEE -->

                                <td>

                                    <div class="d-flex align-items-center gap-2">

                                        <div class="avatar-sm">

                                            <span>
                                                {{ record.initials }}
                                            </span>

                                        </div>


                                        <div>

                                            <div class="emp-name">
                                                {{ record.employeeName }}
                                            </div>

                                            <div class="emp-role">
                                                {{ record.department }}
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <!-- PAYROLL -->

                                <td class="money">

                                    {{
                                        getPayrollLabel(
                                            record.payroll_id
                                        )
                                    }}

                                </td>


                                <!-- AMOUNT -->

                                <td class="money allowance">

                                    {{
                                        formatCurrency(
                                            record.amount
                                        )
                                    }}

                                </td>


                                <!-- DATE -->

                                <td class="money">

                                    {{
                                        formatDate(
                                            record.date
                                        )
                                    }}

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <span class="badge-status" :class="badgeClass(record.status)">
                                        {{ record.status }}
                                    </span>

                                </td>


                                <!-- ACTION -->

                                <td>

                                    <div class="action-group">


                                        <button class="action-btn edit-btn" @click="
                                            openEditContributionModal(
                                                record
                                            )
                                            ">
                                            Edit
                                        </button>


                                        <button class="action-btn delete-btn" @click="
                                            deleteContribution(
                                                record
                                            )
                                            ">
                                            Delete
                                        </button>


                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>


                    <!-- EMPTY -->

                    <div v-else class="empty-state">
                        No SSS contribution records found.
                    </div>

                </div>


                <!-- =====================================================
                     PAGINATION
                ====================================================== -->

                <div v-if="filteredContributionRecords.length" class="pagination-bar">

                    <div class="pagination-info">

                        Showing
                        {{ paginationStart }}
                        –
                        {{ paginationEnd }}

                        of

                        {{
                            filteredContributionRecords.length
                        }}

                    </div>


                    <div class="pagination-controls">


                        <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">
                            Prev
                        </button>


                        <button v-for="page in pageNumbers" :key="page" class="page-btn" :class="{
                            active:
                                page === currentPage
                        }" @click="currentPage = page">
                            {{ page }}
                        </button>


                        <button class="page-btn" :disabled="currentPage === totalPages
                            " @click="currentPage++">
                            Next
                        </button>


                        <select v-model.number="pageSize" class="page-size-select">

                            <option :value="5">
                                5 / page
                            </option>

                            <option :value="10">
                                10 / page
                            </option>

                            <option :value="25">
                                25 / page
                            </option>

                        </select>


                    </div>

                </div>


            </div>

        </div>


        <!-- =====================================================
             ADD / EDIT MODAL
        ====================================================== -->

        <div v-if="showContributionModal" class="modal-backdrop" @click.self="closeContributionModal">

            <div class="salary-modal">


                <!-- MODAL HEADER -->

                <div class="modal-header">

                    <div>

                        <div class="modal-eyebrow">

                            {{
                                editingContribution
                                    ? 'EDIT CONTRIBUTION'
                                    : 'NEW CONTRIBUTION'
                            }}

                        </div>


                        <div class="modal-title">

                            {{
                                editingContribution
                                    ? 'Edit SSS Contribution'
                                    : 'Add SSS Contribution'
                            }}

                        </div>


                        <div class="modal-sub">
                            Enter the contribution amount manually
                        </div>

                    </div>


                    <button class="close-btn" @click="closeContributionModal" aria-label="Close">
                        ×
                    </button>

                </div>


                <!-- =====================================================
                     MODAL BODY
                ====================================================== -->

                <div class="modal-body">


                    <!-- EMPLOYEE -->

                    <div class="form-group">

                        <label>
                            Employee
                        </label>


                        <select v-model="contributionForm.employee_id" class="form-control" :disabled="editingContribution">

                            <option value="" disabled>
                                Select employee
                            </option>


                            <option v-for="employee in employeeOptions" :key="employee.id" :value="employee.id">
                                {{ employee.name }}
                            </option>

                        </select>

                    </div>


                    <!-- PAYROLL -->

                    <div class="form-group">

                        <label>
                            Payroll
                        </label>


                        <select v-model="contributionForm.payroll_id" class="form-control">

                            <option value="">
                                No payroll selected
                            </option>


                            <option v-for="payroll in payrollOptions" :key="payroll.id" :value="payroll.id">
                                {{ payroll.label }}
                            </option>

                        </select>

                    </div>


                    <!-- AMOUNT + DATE -->

                    <div class="form-row">


                        <!-- AMOUNT -->

                        <div class="form-group">

                            <label>
                                SSS Amount
                            </label>


                            <div class="input-money has-prefix">

                                <span>
                                    ₱
                                </span>


                                <input v-model.number="contributionForm.amount
                                    " type="number" min="0" step="0.01" class="form-control" placeholder="0.00" />

                            </div>

                        </div>


                        <!-- DATE -->

                        <div class="form-group">

                            <label>
                                Date
                            </label>


                            <input v-model="contributionForm.date" type="date" class="form-control" />

                        </div>

                    </div>


                    <!-- STATUS -->

                    <div class="form-group">

                        <label>
                            Status
                        </label>


                        <select v-model="contributionForm.status
                            " class="form-control">

                            <option value="Pending">
                                Pending
                            </option>

                            <option value="Posted">
                                Posted
                            </option>

                        </select>

                    </div>


                    <!-- PREVIEW -->

                    <div class="salary-preview">

                        <div>

                            <div class="preview-label">
                                SSS CONTRIBUTION AMOUNT
                            </div>

                            <div class="preview-value">

                                {{
                                    formatCurrency(
                                        contributionForm.amount
                                    )
                                }}

                            </div>

                        </div>


                        <div class="preview-equation">

                            {{
                                contributionForm.status
                            }}

                            ·

                            {{
                                contributionForm.date
                                    ? formatDate(
                                        contributionForm.date
                                    )
                                    : 'No date selected'
                            }}

                        </div>

                    </div>


                </div>


                <!-- =====================================================
                     MODAL FOOTER
                ====================================================== -->

                <div class="modal-footer">


                    <button class="cancel-btn" @click="closeContributionModal">
                        Cancel
                    </button>


                    <button class="save-btn" @click="saveContribution">

                        {{
                            editingContribution
                                ? 'Save Changes'
                                : 'Save Contribution'
                        }}

                    </button>


                </div>


            </div>

        </div>

    </div>
</template>


<script setup>
import '@/assets/sssStyle.css'

import {
    ref,
    computed,
    watch,
    onMounted,
    onBeforeUnmount
} from 'vue'


defineOptions({
    name: 'SssContributionManagement'
})


defineEmits([
    'toggle-sidebar'
])


// =====================================================
// DATE + CLOCK
// =====================================================

const liveClock = ref('--:--:--')


const currentDate = computed(() => {

    return new Date().toLocaleDateString(
        'en-US',
        {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        }
    )

})


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
// SAMPLE EMPLOYEES
// Replace with API employees
// =====================================================

const employeeOptions = ref([

    {
        id: 1,
        name: 'Jonas Diaz',
        department: 'Warehouse'
    },

    {
        id: 2,
        name: 'Carla Santos',
        department: 'Accounting'
    },

    {
        id: 3,
        name: 'Ramon Tan',
        department: 'Logistics'
    },

    {
        id: 4,
        name: 'Paulo Lim',
        department: 'Customer Care'
    }

])


// =====================================================
// SAMPLE PAYROLLS
// Replace with API payrolls
// =====================================================

const payrollOptions = ref([

    {
        id: 1,
        label: 'Payroll #001'
    },

    {
        id: 2,
        label: 'Payroll #002'
    },

    {
        id: 3,
        label: 'Payroll #003'
    }

])


// =====================================================
// SSS CONTRIBUTION RECORDS
//
// Matches:
//
// sss_contributions
// - employee_id
// - payroll_id
// - amount
// - date
// - status
// =====================================================

const contributionRecords = ref([

    {
        id: 1,

        employee_id: 1,

        employeeName: 'Jonas Diaz',

        initials: 'JD',

        department: 'Warehouse',

        payroll_id: 1,

        amount: 250.00,

        date: '2026-09-07',

        status: 'Pending'
    },

    {
        id: 2,

        employee_id: 2,

        employeeName: 'Carla Santos',

        initials: 'CS',

        department: 'Accounting',

        payroll_id: 1,

        amount: 300.00,

        date: '2026-09-07',

        status: 'Posted'
    },

    {
        id: 3,

        employee_id: 3,

        employeeName: 'Ramon Tan',

        initials: 'RT',

        department: 'Logistics',

        payroll_id: 2,

        amount: 225.00,

        date: '2026-09-14',

        status: 'Pending'
    }

])


// =====================================================
// FILTERS
// =====================================================

const searchQuery = ref('')

const statusFilter = ref('all')

const payrollFilter = ref('all')


const statusFilters = [

    {
        key: 'all',
        label: 'All'
    },

    {
        key: 'Pending',
        label: 'Pending'
    },

    {
        key: 'Posted',
        label: 'Posted'
    }

]


const filteredContributionRecords = computed(() => {

    const search =
        searchQuery.value
            .trim()
            .toLowerCase()


    return contributionRecords.value.filter(
        record => {

            const matchesSearch =

                !search ||

                record.employeeName
                    .toLowerCase()
                    .includes(search) ||

                record.department
                    .toLowerCase()
                    .includes(search)


            const matchesStatus =

                statusFilter.value === 'all' ||

                record.status ===
                statusFilter.value


            const matchesPayroll =

                payrollFilter.value === 'all' ||

                Number(record.payroll_id) ===
                Number(payrollFilter.value)


            return (
                matchesSearch &&
                matchesStatus &&
                matchesPayroll
            )

        }
    )

})


// =====================================================
// PAGINATION
// =====================================================

const currentPage = ref(1)

const pageSize = ref(10)


const totalPages = computed(() => {

    return Math.max(

        1,

        Math.ceil(
            filteredContributionRecords.value.length /
            pageSize.value
        )

    )

})


const paginatedContributionRecords = computed(() => {

    const start =

        (
            currentPage.value -
            1
        )

        *

        pageSize.value


    return filteredContributionRecords.value.slice(

        start,

        start +
        pageSize.value

    )

})


const paginationStart = computed(() => {

    if (
        !filteredContributionRecords
            .value
            .length
    ) {

        return 0

    }


    return (

        (
            currentPage.value -
            1
        )

        *

        pageSize.value

    )

        +

        1

})


const paginationEnd = computed(() => {

    return Math.min(

        currentPage.value *
        pageSize.value,

        filteredContributionRecords
            .value
            .length

    )

})


const pageNumbers = computed(() => {

    const pages = []


    for (

        let page = 1;

        page <= totalPages.value;

        page++

    ) {

        pages.push(page)

    }


    return pages

})


watch(

    [
        searchQuery,
        statusFilter,
        payrollFilter,
        pageSize
    ],

    () => {

        currentPage.value = 1

    }

)


// =====================================================
// STATISTICS
// =====================================================

const pendingCount = computed(() => {

    return contributionRecords.value
        .filter(
            record =>
                record.status ===
                'Pending'
        )
        .length

})


const postedCount = computed(() => {

    return contributionRecords.value
        .filter(
            record =>
                record.status ===
                'Posted'
        )
        .length

})


const totalAmount = computed(() => {

    return contributionRecords.value.reduce(

        (
            total,
            record
        ) => {

            return (

                total +

                Number(
                    record.amount ||
                    0
                )

            )

        },

        0

    )

})


// =====================================================
// MODAL
// =====================================================

const showContributionModal =
    ref(false)


const editingContribution =
    ref(false)


function createEmptyContributionForm() {

    return {

        id: null,

        employee_id: '',

        payroll_id: '',

        amount: 0,

        date: '',

        status: 'Pending'

    }

}


const contributionForm =
    ref(
        createEmptyContributionForm()
    )


// =====================================================
// ADD
// =====================================================

function openAddContributionModal() {

    editingContribution.value =
        false


    contributionForm.value = {

        ...createEmptyContributionForm(),

        date:
            new Date()
                .toISOString()
                .split('T')[0]

    }


    showContributionModal.value =
        true

}


// =====================================================
// EDIT
// =====================================================

function openEditContributionModal(
    record
) {

    editingContribution.value =
        true


    contributionForm.value = {

        id:
            record.id,

        employee_id:
            record.employee_id,

        payroll_id:
            record.payroll_id ??
            '',

        amount:
            Number(
                record.amount ||
                0
            ),

        date:
            record.date,

        status:
            record.status

    }


    showContributionModal.value =
        true

}


// =====================================================
// CLOSE
// =====================================================

function closeContributionModal() {

    showContributionModal.value =
        false

}


// =====================================================
// SAVE
// =====================================================

function saveContribution() {

    if (
        !contributionForm.value
            .employee_id
    ) {

        alert(
            'Please select an employee.'
        )

        return

    }


    if (
        Number(
            contributionForm.value
                .amount
        ) <= 0
    ) {

        alert(
            'Please enter a valid SSS contribution amount.'
        )

        return

    }


    const employee =
        employeeOptions.value.find(

            item =>

                Number(item.id) ===

                Number(
                    contributionForm
                        .value
                        .employee_id
                )

        )


    if (!employee) {

        alert(
            'Employee not found.'
        )

        return

    }


    if (
        editingContribution.value
    ) {

        const index =
            contributionRecords.value
                .findIndex(

                    item =>

                        item.id ===

                        contributionForm
                            .value
                            .id

                )


        if (
            index !== -1
        ) {

            contributionRecords.value[
                index
            ] = {

                ...contributionRecords
                    .value[
                index
                ],

                payroll_id:

                    contributionForm.value
                        .payroll_id

                        ?

                        Number(
                            contributionForm.value
                                .payroll_id
                        )

                        :

                        null,


                amount:

                    Number(
                        contributionForm.value
                            .amount
                    ),


                date:

                    contributionForm.value
                        .date || null,


                status:

                    contributionForm.value
                        .status

            }

        }

    }

    else {

        const initials =
            employee.name

                .split(' ')

                .map(
                    name =>
                        name.charAt(0)
                )

                .join('')

                .substring(
                    0,
                    2
                )

                .toUpperCase()


        contributionRecords.value.push({

            id:
                Date.now(),

            employee_id:

                Number(
                    contributionForm.value
                        .employee_id
                ),


            employeeName:
                employee.name,


            initials,


            department:
                employee.department,


            payroll_id:

                contributionForm.value
                    .payroll_id

                    ?

                    Number(
                        contributionForm.value
                            .payroll_id
                    )

                    :

                    null,


            amount:

                Number(
                    contributionForm.value
                        .amount
                ),


            date:

                contributionForm.value
                    .date || null,


            status:

                contributionForm.value
                    .status

        })

    }


    closeContributionModal()

}


// =====================================================
// DELETE
// =====================================================

function deleteContribution(
    record
) {

    const confirmed =
        window.confirm(

            `Delete SSS contribution record for ${record.employeeName}?`

        )


    if (!confirmed) {
        return
    }


    contributionRecords.value =
        contributionRecords.value.filter(

            item =>

                item.id !==
                record.id

        )

}


// =====================================================
// PAYROLL LABEL
// =====================================================

function getPayrollLabel(
    payrollId
) {

    if (!payrollId) {

        return '—'

    }


    const payroll =
        payrollOptions.value.find(

            item =>

                Number(item.id) ===
                Number(payrollId)

        )


    return payroll
        ? payroll.label
        : `Payroll #${payrollId}`

}


// =====================================================
// FORMAT DATE
// =====================================================

function formatDate(
    date
) {

    if (!date) {

        return '—'

    }


    return new Date(
        `${date}T00:00:00`
    ).toLocaleDateString(

        'en-US',

        {

            month:
                'short',

            day:
                'numeric',

            year:
                'numeric'

        }

    )

}


// =====================================================
// FORMAT CURRENCY
// =====================================================

function formatCurrency(
    amount
) {

    return new Intl.NumberFormat(

        'en-PH',

        {

            style:
                'currency',

            currency:
                'PHP',

            minimumFractionDigits:
                2

        }

    ).format(

        Number(
            amount ||
            0
        )

    )

}


// =====================================================
// STATUS CLASS
// =====================================================

function badgeClass(
    status
) {

    return {

        Pending:
            'badge-pending',

        Posted:
            'badge-posted'

    }[status]

}


// =====================================================
// EXPORT
// =====================================================

function exportCsv() {

    const rows = [

        [

            'Employee',

            'Department',

            'Payroll',

            'SSS Amount',

            'Date',

            'Status'

        ]

    ]


    filteredContributionRecords.value
        .forEach(

            record => {

                rows.push([

                    record.employeeName,

                    record.department,

                    getPayrollLabel(
                        record.payroll_id
                    ),

                    record.amount,

                    record.date,

                    record.status

                ])

            }

        )


    const csv =

        rows

            .map(

                row =>

                    row

                        .map(

                            cell =>

                                `"${String(
                                    cell ?? ''
                                ).replace(
                                    /"/g,
                                    '""'
                                )}"`

                        )

                        .join(',')

            )

            .join('\n')


    const blob =
        new Blob(

            [csv],

            {

                type:
                    'text/csv;charset=utf-8;'

            }

        )


    const url =
        URL.createObjectURL(
            blob
        )


    const link =
        document.createElement(
            'a'
        )


    link.href =
        url


    link.download =
        'sss-contributions.csv'


    document.body
        .appendChild(link)


    link.click()


    document.body
        .removeChild(link)


    URL.revokeObjectURL(
        url
    )

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


<style >

.filter-select {

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper-2, #FBFAF6);

    color:
        var(--ink, #1C2B4A);

    border-radius:
        20px;

    padding:
        .32rem .65rem;

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .7rem;

    outline:
        none;

}


.badge-pending {

    background:
        var(--amber-bg, #F6EEDB);

    color:
        var(--gold-dark, #9C7726);

}


.badge-posted {

    background:
        var(--green-bg, #E5F2EA);

    color:
        var(--green, #2F8F5B);

}
</style>
