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


        <div class="content">

            <!-- =============================================
             STATISTICS
        ============================================== -->
            <div class="row g-3 mb-3">

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
            <div class="panel mb-3">

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

                        <div class="search-box">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7" />

                                <path d="m20 20-3-3" />
                            </svg>

                            <input v-model="recordsSearchQuery" type="text" placeholder="Search employee..." />
                        </div>


                        <button class="add-btn" @click="openAddContributionModal">
                            + Add Contribution
                        </button>
                    </div>
                </div>

                <div class="table-responsive">

                    <table v-if="paginatedContributionRecords.length" class="table-ledger">

                        <thead>
                            <tr>
                                <th>
                                    Employee
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
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm">
                                            <img :src="storageImage(record.employee.image)" style="object-fit: cover; border-radius: 50%; border: 1px dashed gray;" width="45" height="45" alt="">
                                        </div>
                                        <div>
                                            <div class="emp-name">
                                                {{ record.employee.last_name }}, {{ record.employee.first_name }}
                                            </div>
                                            <div class="emp-role">
                                                {{ record.employee.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="money allowance">
                                    {{ formatCurrency(record.amount) }}
                                </td>
                                <td class="money">
                                    {{ formatDate(record.date) }}
                                </td>
                                <td>
                                    <span class="badge-status" :class="badgeClass(
                                        record.status
                                    )
                                        ">
                                        {{
                                            record.status
                                        }}
                                    </span>
                                </td>


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


                    <div v-else class="empty-state">
                        No SSS contribution records found.
                    </div>

                </div>


                <!-- RECORDS PAGINATION -->
                <div v-if="
                    filteredContributionRecords.length
                " class="pagination-bar">

                    <div class="pagination-info">
                        Showing
                        {{ paginationStartRecords }}
                        –
                        {{ paginationEndRecords }}
                        of
                        {{
                            filteredContributionRecords.length
                        }}
                    </div>


                    <div class="pagination-controls">

                        <button class="page-btn" :disabled="recordsCurrentPage === 1
                            " @click="
                                recordsCurrentPage--
                                ">
                            Prev
                        </button>


                        <button v-for="
page in
    pageNumbersRecords
                        " :key="page" class="page-btn" :class="{
                            active:
                                page ===
                                recordsCurrentPage
                        }" @click="
                            recordsCurrentPage =
                            page
                            ">
                            {{ page }}
                        </button>


                        <button class="page-btn" :disabled="recordsCurrentPage ===
                            totalPagesRecords
                            " @click="
                                recordsCurrentPage++
                                ">
                            Next
                        </button>


                        <select v-model.number="recordsPageSize
                            " class="page-size-select">
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

            <div class="panel">

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                    <div>
                        <div class="section-title mb-0">
                            SSS Contribution History
                        </div>

                        <div class="panel-sub">
                            Contributions generated from payroll records
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">

                        <div class="search-box">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7" />

                                <path d="m20 20-3-3" />
                            </svg>

                            <input v-model="historiesSearchQuery
                                " type="text" placeholder="Search employee..." />

                        </div>

                    </div>

                </div>

                <div class="table-responsive">
                    <table v-if="paginatedContributionHistories.length" class="table-ledger">

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

                            </tr>

                        </thead>

                        <tbody>

                            <tr v-for="record in paginatedContributionHistories" :key="record.id">
                                <td>
                                    <div class="d-flex align-items-center gap-2">

                                        <div class="avatar-sm">
                                            <span>
                                                {{
                                                    record.initials
                                                }}
                                            </span>
                                        </div>


                                        <div>

                                            <div class="emp-name">
                                                {{
                                                    record.employeeName
                                                }}
                                            </div>

                                            <div class="emp-role">
                                                {{
                                                    record.department
                                                }}
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <td class="money">
                                    {{
                                        getPayrollLabel(
                                            record.payroll_id
                                        )
                                    }}
                                </td>


                                <td class="money allowance">
                                    {{
                                        formatCurrency(
                                            record.amount
                                        )
                                    }}
                                </td>


                                <td class="money">
                                    {{
                                        formatDate(
                                            record.date
                                        )
                                    }}
                                </td>


                                <td>

                                    <span class="badge-status" :class="badgeClass(
                                        record.status
                                    )
                                        ">
                                        {{
                                            record.status
                                        }}
                                    </span>

                                </td>

                            </tr>

                        </tbody>

                    </table>


                    <div v-else class="empty-state">
                        No SSS contribution history found.
                    </div>

                </div>


                <!-- HISTORY PAGINATION -->
                <div v-if="
                    filteredContributionHistories.length
                " class="pagination-bar">

                    <div class="pagination-info">

                        Showing
                        {{
                            paginationStartHistories
                        }}
                        –
                        {{
                            paginationEndHistories
                        }}
                        of
                        {{
                            filteredContributionHistories.length
                        }}

                    </div>


                    <div class="pagination-controls">

                        <button class="page-btn" :disabled="historiesCurrentPage === 1
                            " @click="
                                historiesCurrentPage--
                                ">
                            Prev
                        </button>


                        <button v-for="
page in
    pageNumbersHistories
                        " :key="page" class="page-btn" :class="{
                            active:
                                page ===
                                historiesCurrentPage
                        }" @click="
                            historiesCurrentPage =
                            page
                            ">
                            {{ page }}
                        </button>


                        <button class="page-btn" :disabled="historiesCurrentPage ===
                            totalPagesHistories
                            " @click="
                                historiesCurrentPage++
                                ">
                            Next
                        </button>


                        <select v-model.number="historiesPageSize
                            " class="page-size-select">
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


        <div v-if="showContributionModal" class="modal-backdrop" @click.self="closeContributionModal">

            <div class="salary-modal">

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


                <div class="modal-body">
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
                    <div class="form-row">
                        <div class="form-group">
                            <label>
                                SSS Amount
                            </label>
                            <div class="input-money has-prefix">
                                <span>
                                    ₱
                                </span>
                                <input v-model.number="contributionForm.amount" type="number" min="0" step="0.01" class="form-control" placeholder="0.00" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>
                                Date
                            </label>
                            <input v-model="contributionForm.date" type="date" class="form-control" />
                        </div>
                    </div>
                    <div class="salary-preview">
                        <div>
                            <div class="preview-label">
                                SSS CONTRIBUTION AMOUNT
                            </div>
                            <div class="preview-value">
                                {{ formatCurrency(contributionForm.amount) }}
                            </div>
                        </div>
                        <div class="preview-equation">
                            {{ contributionForm.status }}
                            ·
                            {{ contributionForm.date ? formatDate(contributionForm.date) : 'No date selected' }}
                        </div>
                    </div>
                </div>
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
    ```

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

import {
    useEmployeeStore
} from '@/stores/useEmployee'

import {
    useSSSStore
} from '@/stores/useSSS'
import { storageImage } from '@/utils/image'
import { showConfirm } from '@/utils/Swals'

const employeeStore =
    useEmployeeStore()

const sssStore =
    useSSSStore()

defineOptions({
    name: 'SssContributionManagement'
})

defineEmits([
    'toggle-sidebar'
])


const liveClock =
    ref('--:--:--')

const currentDate =
    computed(() => {
        return new Date()
            .toLocaleDateString(
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
        new Date()
            .toLocaleTimeString(
                'en-US',
                {
                    hour12: true
                }
            )
}


/* =====================================================
   DATA
===================================================== */

const employeeOptions =
    ref([])

const payrollOptions =
    ref([
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

const contributionRecords =
    ref([])

const contributionHistories =
    ref([])


/* =====================================================
   STATUS FILTERS
===================================================== */

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


/* =====================================================
   RECORDS FILTERS
===================================================== */

const recordsSearchQuery =
    ref('')

const recordsStatusFilter =
    ref('all')


/* =====================================================
   HISTORIES FILTERS
===================================================== */

const historiesSearchQuery =
    ref('')

const historiesStatusFilter =
    ref('all')

const historiesPayrollFilter =
    ref('all')


/* =====================================================
   FILTERED RECORDS
===================================================== */

const filteredContributionRecords =
    computed(() => {

        const search =
            recordsSearchQuery.value
                .trim()
                .toLowerCase()

        return contributionRecords.value
            .filter(record => {

                const employeeName =
                    record.employeeName || ''

                const department =
                    record.department || ''

                const matchesSearch =
                    !search ||
                    employeeName
                        .toLowerCase()
                        .includes(search) ||
                    department
                        .toLowerCase()
                        .includes(search)

                const matchesStatus =
                    recordsStatusFilter.value ===
                    'all' ||
                    record.status ===
                    recordsStatusFilter.value

                return (
                    matchesSearch &&
                    matchesStatus
                )

            })
    })


/* =====================================================
   FILTERED HISTORIES
===================================================== */

const filteredContributionHistories =
    computed(() => {

        const search =
            historiesSearchQuery.value
                .trim()
                .toLowerCase()

        return contributionHistories.value
            .filter(record => {

                const employeeName =
                    record.employeeName || ''

                const department =
                    record.department || ''

                const matchesSearch =
                    !search ||
                    employeeName
                        .toLowerCase()
                        .includes(search) ||
                    department
                        .toLowerCase()
                        .includes(search)

                const matchesStatus =
                    historiesStatusFilter.value ===
                    'all' ||
                    record.status ===
                    historiesStatusFilter.value

                const matchesPayroll =
                    historiesPayrollFilter.value ===
                    'all' ||
                    Number(
                        record.payroll_id
                    ) ===
                    Number(
                        historiesPayrollFilter.value
                    )

                return (
                    matchesSearch &&
                    matchesStatus &&
                    matchesPayroll
                )

            })
    })


/* =====================================================
   RECORDS PAGINATION
===================================================== */

const recordsCurrentPage =
    ref(1)

const recordsPageSize =
    ref(10)


const totalPagesRecords =
    computed(() => {

        return Math.max(
            1,
            Math.ceil(
                filteredContributionRecords
                    .value
                    .length /
                recordsPageSize.value
            )
        )

    })


const paginatedContributionRecords =
    computed(() => {

        const start =
            (
                recordsCurrentPage.value -
                1
            ) *
            recordsPageSize.value

        return filteredContributionRecords
            .value
            .slice(
                start,
                start +
                recordsPageSize.value
            )

    })


const paginationStartRecords =
    computed(() => {

        if (
            !filteredContributionRecords
                .value
                .length
        ) {
            return 0
        }

        return (
            (
                recordsCurrentPage.value -
                1
            ) *
            recordsPageSize.value
        ) + 1

    })


const paginationEndRecords =
    computed(() => {

        return Math.min(
            recordsCurrentPage.value *
            recordsPageSize.value,

            filteredContributionRecords
                .value
                .length
        )

    })


const pageNumbersRecords =
    computed(() => {

        return Array.from(
            {
                length:
                    totalPagesRecords.value
            },
            (_, index) =>
                index + 1
        )

    })


const historiesCurrentPage =
    ref(1)

const historiesPageSize =
    ref(10)


const totalPagesHistories =
    computed(() => {

        return Math.max(
            1,
            Math.ceil(
                filteredContributionHistories
                    .value
                    .length /
                historiesPageSize.value
            )
        )

    })


const paginatedContributionHistories =
    computed(() => {
        const start = (historiesCurrentPage.value - 1) * historiesPageSize.value
        return filteredContributionHistories.value.slice(start, start + historiesPageSize.value)
    })


const paginationStartHistories =
    computed(() => {

        if (
            !filteredContributionHistories
                .value
                .length
        ) {
            return 0
        }

        return (
            (
                historiesCurrentPage.value -
                1
            ) *
            historiesPageSize.value
        ) + 1

    })


const paginationEndHistories =
    computed(() => {

        return Math.min(
            historiesCurrentPage.value *
            historiesPageSize.value,

            filteredContributionHistories
                .value
                .length
        )

    })


const pageNumbersHistories =
    computed(() => {

        return Array.from(
            {
                length:
                    totalPagesHistories.value
            },
            (_, index) =>
                index + 1
        )

    })


/* =====================================================
   RESET RECORDS PAGE
===================================================== */

watch(
    [
        recordsSearchQuery,
        recordsStatusFilter,
        recordsPageSize
    ],
    () => {
        recordsCurrentPage.value = 1
    }
)


/* =====================================================
   RESET HISTORIES PAGE
===================================================== */

watch(
    [
        historiesSearchQuery,
        historiesStatusFilter,
        historiesPayrollFilter,
        historiesPageSize
    ],
    () => {
        historiesCurrentPage.value = 1
    }
)


/* =====================================================
   STATISTICS
===================================================== */

const pendingCount =
    computed(() => {

        return contributionRecords
            .value
            .filter(
                record =>
                    record.status ===
                    'Pending'
            )
            .length

    })


const postedCount =
    computed(() => {

        return contributionRecords
            .value
            .filter(
                record =>
                    record.status ===
                    'Posted'
            )
            .length

    })


const totalAmount =
    computed(() => {

        return contributionRecords
            .value
            .reduce(
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


function getLocalDate() {
    const date = new Date();

    const year = date.getFullYear();
    const month = String(
        date.getMonth() + 1
    ).padStart(2, '0');

    const day = String(
        date.getDate()
    ).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function openAddContributionModal() {

    editingContribution.value =
        false

    contributionForm.value = {
        ...createEmptyContributionForm(),

        date: new Date()
            .toISOString()
            .split('T')[0]
    }

    showContributionModal.value =
        true

}


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

        date: record.date
            ? record.date.split('T')[0]
            : '',

        status:
            record.status
    }

    showContributionModal.value =
        true

}


function closeContributionModal() {

    showContributionModal.value =
        false

    editingContribution.value =
        false

    contributionForm.value =
        createEmptyContributionForm()

}

const saveContribution = async () => {

    try {

        if (editingContribution.value) {
            await sssStore.updateSSSDeduction({
                ...contributionForm.value
            })

            closeContributionModal();
        } else {


            await sssStore
                .createSSSContribution(
                    {
                        ...contributionForm.value
                    }
                )

            closeContributionModal();
        }

        await Promise.all([
            listRecords({
                per_page: 100
            }),

            listHistories({
                per_page: 100
            })
        ])

    } catch (error) {

        console.error(
            'Failed to save SSS contribution:',
            error
        )

    }

}

const deleteContribution = async (record) => {

    const confirmed = await showConfirm(`Delete SSS contribution`, `Are you sure want to delete SSS contribution record for ${record.employee.last_name}, ${record.employee.first_name}?`, 'Yes')

    if (confirmed !== true) {
        return
    }

    await sssStore.removeSSSDeduction({ id: record.id });

}

function getPayrollLabel(
    payrollId
) {

    if (!payrollId) {
        return '—'
    }

    const payroll =
        payrollOptions.value
            .find(
                item =>
                    Number(item.id) ===
                    Number(payrollId)
            )

    return payroll
        ? payroll.label
        : `Payroll #${payrollId}`

}

function formatDate(date) {
    if (!date) {
        return '—'
    }
    return new Date(date)
        .toLocaleDateString('en-US',
            {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            }
        )

}

function formatCurrency(
    amount
) {

    return new Intl.NumberFormat(
        'en-PH',
        {
            style: 'currency',
            currency: 'PHP',
            minimumFractionDigits: 2
        }
    )
        .format(
            Number(
                amount || 0
            )
        )

}


/* =====================================================
   STATUS BADGE
===================================================== */

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


/* =====================================================
   EXPORT CSV
===================================================== */

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


    filteredContributionRecords
        .value
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


    link.href = url

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


/* =====================================================
   LOAD EMPLOYEES
===================================================== */

const listEmployee =
    async () => {

        try {

            const response =
                await employeeStore
                    .allEmployees()


            employeeOptions.value =
                response?.data?.data ||
                []

        } catch (error) {

            console.error(
                'Failed to load employees:',
                error
            )

            employeeOptions.value = []

        }

    }


/* =====================================================
   LOAD SSS HISTORIES
===================================================== */

const listHistories =
    async data => {

        try {

            const response =
                await sssStore
                    .getSSSDeductionsHistory(
                        data
                    )


            contributionHistories.value =
                response?.data?.data ||
                []

        } catch (error) {

            console.error(
                'Failed to load SSS histories:',
                error
            )

            contributionHistories.value =
                []

        }

    }


/* =====================================================
   LOAD SSS RECORDS
===================================================== */

const listRecords =
    async data => {
        try {
            const response = await sssStore.getSSSDeductionsRecords(data)
            contributionRecords.value = response?.data?.data || []
        } catch (error) {
            console.error('Failed to load SSS records:', error)
            contributionRecords.value = []

        }

    }


/* =====================================================
   ON MOUNTED
===================================================== */

onMounted(
    async () => {

        tickClock()

        clockTimer =
            setInterval(
                tickClock,
                1000
            )


        await Promise.all([

            listEmployee(),

            listRecords({
                per_page: 100
            }),

            listHistories({
                per_page: 100
            })

        ])

    }
)


/* =====================================================
   ON BEFORE UNMOUNT
===================================================== */

onBeforeUnmount(
    () => {

        clearInterval(
            clockTimer
        )

    }
)
</script>


<style>
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
