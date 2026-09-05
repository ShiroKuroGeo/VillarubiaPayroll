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
                        Salary Management
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
                <div class="col-12 col-lg-4">
                    <div class="punch-card">
                        <div class="stamp green">
                            STAFF
                        </div>
                        <div class="stat-label">
                            Active Employees
                        </div>
                        <div class="stat-period">
                            Current salary records
                        </div>
                        <div class="stat-value">
                            {{ activeEmployeeCount }}
                        </div>
                        <div class="stat-delta stat-delta--slate">
                            Employees with active salary
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="punch-card">
                        <div class="stamp gold">
                            BASE
                        </div>
                        <div class="stat-label">
                            Total Basic Salary
                        </div>
                        <div class="stat-period">
                            Weekly
                        </div>
                        <div class="stat-value stat-value-money">
                            {{ formatCurrency(totalBasicSalary) }}
                        </div>
                        <div class="stat-delta stat-delta--gold">
                            Active employees
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">

                    <div class="punch-card">

                        <div class="stamp green">
                            NET
                        </div>

                        <div class="stat-label">
                            Estimated Net Payroll
                        </div>

                        <div class="stat-period">
                            Monthly
                        </div>

                        <div class="stat-value stat-value-money">
                            {{ formatCurrency(estimatedNetPayroll) }}
                        </div>

                        <div class="stat-delta text-success">
                            Before payroll processing
                        </div>

                    </div>
                </div>
            </div>
            <div class="panel mt-3">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <div class="section-title mb-0">
                            Employee salaries
                        </div>
                        <div class="panel-sub">
                            Manage employee compensation and salary settings
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
                        <button class="add-btn" @click="openAddModal">
                            + Add Salary
                        </button>
                    </div>
                </div>
                <div class="filter-row">
                    <button v-for="filter in statusFilters" :key="filter.key" class="filter-pill" :class="{
                        active: statusFilter === filter.key
                    }" @click="statusFilter = filter.key">
                        {{ filter.label }}
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table-ledger salary-table" v-if="filteredSalaryData.length">
                        <thead>
                            <tr>
                                <th>
                                    Employee
                                </th>
                                <th>
                                    location
                                </th>
                                <th>
                                    Salary Type
                                </th>
                                <th>
                                    Basic Salary
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
                            <tr v-for="employee in filteredSalaryData" :key="employee.id">
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
                                    <span class="location">
                                        {{ employee.location }}
                                    </span>

                                </td>
                                <td>
                                    <span class="salary-type">
                                        {{ formatSalaryType(employee.salaryType) }}
                                    </span>
                                </td>
                                <td class="money">
                                    {{ formatCurrency(employee.basicSalary) }}
                                </td>
                                <td>
                                    <span class="badge-status" :class="badgeClass(employee.status)">
                                        {{ formatStatus(employee.status) }}
                                    </span>
                                </td>
                                <td>

                                    <div class="action-group">

                                        <button class="action-btn edit-btn" @click="openEditModal(employee)">
                                            Edit
                                        </button>


                                        <button class="action-btn delete-btn" @click="deleteSalary(employee)">
                                            Delete
                                        </button>

                                    </div>

                                </td>

                            </tr>


                        </tbody>

                    </table>


                    <div v-else class="empty-state">
                        No salary records match your search or filter.
                    </div>

                </div>

            </div>

        </div>

        <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">

            <div class="salary-modal">
                <div class="modal-header">
                    <div>
                        <div class="modal-eyebrow">
                            {{ editingSalary ? 'EDIT RECORD' : 'NEW RECORD' }}
                        </div>
                        <div class="modal-title">
                            {{ editingSalary ? 'Edit Salary' : 'Add Salary' }}
                        </div>
                        <div class="modal-sub">
                            Configure employee compensation
                        </div>
                    </div>
                    <button class="close-btn" @click="closeModal" aria-label="Close">
                        ×
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>
                            Employee
                        </label>
                        <select v-model="salaryForm.employeeId" class="form-control" :disabled="editingSalary">
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
                                Salary Type
                            </label>
                            <select v-model="salaryForm.salaryType" class="form-control">
                                <option :value="s.value" v-for="s in salaryType" :key="value">
                                    {{ s.label }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>
                                Status
                            </label>
                            <select v-model="salaryForm.status" class="form-control">
                                <option value="true">
                                    Active
                                </option>
                                <option value="false">
                                    Inactive
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Basic Salary</label>
                            <div class="input-money">
                                <input v-model.number="salaryForm.basicSalary" type="number" min="0" step="0.01" class="form-control" placeholder="0.00" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Effective Date</label>
                            <input v-model="salaryForm.effectiveDate" type="date" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" @click="closeModal">
                        Cancel
                    </button>
                    <button class="save-btn" @click="saveSalary">
                        {{ editingSalary ? 'Save Changes' : 'Add Salary' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>


<script setup>

import { useEmployeeStore } from '@/stores/useEmployee'
import { useSalaryStore } from '@/stores/useSalary';
import { storageImage } from '@/utils/image';
import { showStatusAlert } from '@/utils/Swals';

import {
    computed,
    onMounted,
    onBeforeUnmount,
    ref
} from 'vue'

const employeeStore = useEmployeeStore();
const salaryStore = useSalaryStore();

defineOptions({
    name: 'SalaryManagementPage'
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

const employeeOptions = ref([]);

const salaryData = ref([])

const salaryType = ref([
    {
        value: 'Weekly',
        label: 'Weekly',
    },
    {
        value: 'Monthly',
        label: 'Monthly',
    },
    {
        value: 'Semi-Monthly',
        label: 'Semi-Monthly',
    },
    {
        value: 'Daily',
        label: 'Daily',
    },
    {
        value: 'Hourly',
        label: 'Hourly',
    },
    {
        value: 'Piece-Rate',
        label: 'Piece-Rate',
    },
])

const searchQuery = ref('')

const statusFilter = ref('all')

const statusFilters = [

    {
        key: 'all',
        label: 'All'
    },

    {
        key: true,
        label: 'Active'
    },

    {
        key: false,
        label: 'Inactive'
    }

]

const filteredSalaryData = computed(() => {

    const search =
        searchQuery.value
            .trim()
            .toLowerCase()


    return salaryData.value.filter(employee => {

        const matchesSearch =
            !search ||
            employee.employeeName
                .toLowerCase()
                .includes(search) ||
            employee.location
                .toLowerCase()
                .includes(search)


        const matchesStatus =
            statusFilter.value === 'all' ||
            employee.status === statusFilter.value

        return matchesSearch && matchesStatus

    })

})

const activeEmployeeCount = computed(() => {

    return salaryData.value.filter(
        employee =>
            employee.status === true
    ).length

})

const inactiveCount = computed(() => {

    return salaryData.value.filter(
        employee =>
            employee.status === 'inactive'
    ).length

})

const totalBasicSalary = computed(() => {

    return salaryData.value

        .filter(
            employee =>
                employee.status === true
        )

        .reduce(
            (total, employee) =>
                total + Number(employee.basicSalary || 0),
            0
        )

})

const totalAttendance = computed(() => {

    return salaryData.value

        .filter(
            employee =>
                employee.status === true
        )

        .reduce(
            (total, employee) =>
                total * Number(employee.totalAttendance || 0),
            0
        )

})

const totalDeductions = computed(() => {

    return salaryData.value

        .filter(
            employee =>
                employee.status === 'active'
        )

        .reduce(
            (total, employee) =>
                total + Number(employee.deductions || 0),
            0
        )

})

const estimatedNetPayroll = computed(() => {

    return (
        (totalBasicSalary.value *
            totalAttendance.value) -
        totalDeductions.value
    )

})

const monthlyCount = computed(() => {

    return salaryData.value.filter(
        employee =>
            employee.status === 'active' &&
            employee.salaryType === 'monthly'
    ).length

})

const dailyCount = computed(() => {

    return salaryData.value.filter(
        employee =>
            employee.status === 'active' &&
            employee.salaryType === 'daily'
    ).length

})

const hourlyCount = computed(() => {

    return salaryData.value.filter(
        employee =>
            employee.status === 'active' &&
            employee.salaryType === 'hourly'
    ).length

})

const showModal = ref(false)

const editingSalary = ref(false)

const salaryForm = ref(
    createEmptyForm()
)

function createEmptyForm() {
    return {
        id: null,
        employeeId: '',
        salaryType: 'Weekly',
        basicSalary: 0,
        effectiveDate: '2026-01-01',
        status: 'active'
    }
}

const formatStatus = (status) => {
    const data = {
        true: 'Active',
        false: 'Inactive',
    }

    if (!status) return 'No status yet'

    return data[status]
}

function openAddModal() {

    editingSalary.value = false

    salaryForm.value =
        createEmptyForm()

    showModal.value = true

}

function openEditModal(employee) {

    editingSalary.value = true

    salaryForm.value = {

        id: employee.id,
        employeeId: employee.employeeId,
        salaryType: employee.salaryType,
        basicSalary: employee.basicSalary,
        effectiveDate: employee.effectiveDate,
        status: employee.status
    }
    showModal.value = true
}

function closeModal() {

    showModal.value = false

}

const saveSalary = async () => {
    if (!salaryForm.value.employeeId) {
        showStatusAlert(409, 'Please select an employee.')
        return
    }

    if (Number(salaryForm.value.basicSalary) <= 0) {
        showStatusAlert(409, 'Please enter a valid basic salary.')
        return
    }
    if (editingSalary.value) {
        closeModal()
        await salaryStore.updateSalary({
            "salary_id": salaryForm.value.id,
            "salary_type": salaryForm.value.salaryType,
            "basic_salary": salaryForm.value.basicSalary,
            "effective_date": salaryForm.value.effectiveDate
        });
        listEmployee();
        salaries();
    } else {
        closeModal()
        await salaryStore.createSalary({
            "employee_id": salaryForm.value.employeeId,
            "salary_type": salaryForm.value.salaryType,
            "basic_salary": salaryForm.value.basicSalary,
            "effective_date": salaryForm.value.effectiveDate
        });
        listEmployee();
        salaries();
    }

}

function deleteSalary(employee) {

    const confirmed =
        window.confirm(
            `Delete salary record for ${employee.employeeName}?`
        )


    if (!confirmed) {
        return
    }


    salaryData.value =
        salaryData.value.filter(
            item =>
                item.id !== employee.id
        )

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
        weekly: 'WEEKLY',
        monthly: 'MONTHLY',
        daily: 'DAILY',
        hourly: 'HOURLY',
    }

    if (!type) return 'No Salary Yet'

    return labels[type] || type.toUpperCase()
}

function badgeClass(status) {

    return {

        active: 'badge-active',

        inactive: 'badge-inactive'

    }[status]

}

function exportCsv() {

    const rows = [

        [
            'Employee',
            'location',
            'Salary Type',
            'Basic Salary',
            'Overtime Rate',
            'totalAttendance',
            'Deductions',
            'Net Salary',
            'Effective Date',
            'Status'
        ]

    ]


    filteredSalaryData.value.forEach(employee => {

        rows.push([

            employee.employeeName,
            employee.location,
            formatSalaryType(
                employee.salaryType
            ),
            employee.basicSalary,
            calculateNet(employee),
            employee.effectiveDate,
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
        'salary-management.csv'


    document.body.appendChild(a)

    a.click()

    document.body.removeChild(a)


    URL.revokeObjectURL(url)

}

const listEmployee = async () => {
    const listEmployees = await employeeStore.allEmployees();
    employeeOptions.value = listEmployees.data.data;
}

const salaries = async () => {
    const resultSalaries = await salaryStore.listSalaries();
    salaryData.value = resultSalaries.data;
}

onMounted(() => {

    tickClock()

    clockTimer =
        setInterval(
            tickClock,
            1000
        )

    listEmployee();
    salaries();

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

    background:
        var(--paper-2, #FBFAF6);

    border-bottom:
        1px solid var(--line, #DCD8CB);

    padding:
        1rem 1.75rem;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        1rem;

    flex-wrap:
        wrap;
}


.topbar h1 {

    font-family:
        'Fraunces',
        serif;

    font-weight:
        600;

    font-size:
        1.4rem;

    margin:
        0;
}


.eyebrow {

    font-size:
        .72rem;

    letter-spacing:
        .1em;

    text-transform:
        uppercase;

    color:
        var(--slate, #6B7280);

    font-weight:
        600;
}


/* =====================================================
   MENU
===================================================== */

.btn-menu {

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper-2, #FBFAF6);

    color:
        var(--ink, #1C2B4A);

    border-radius:
        8px;

    width:
        36px;

    height:
        36px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    cursor:
        pointer;
}


.d-lg-none {
    display: flex;
}


@media (min-width: 992px) {

    .d-lg-none {
        display: none;
    }

}


/* =====================================================
   CLOCK
===================================================== */

.clock-chip {

    font-family:
        'IBM Plex Mono',
        monospace;

    background:
        var(--ink, #1C2B4A);

    color:
        #F3DFA6;

    border-radius:
        8px;

    padding:
        .5rem .9rem;

    font-size:
        .82rem;

    display:
        flex;

    align-items:
        center;

    gap:
        .5rem;
}


.clock-chip .dot {

    width:
        6px;

    height:
        6px;

    border-radius:
        50%;

    background:
        var(--green, #2F8F5B);

    box-shadow:
        0 0 0 3px rgba(47, 143, 91, .25);
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

    background:
        var(--paper-2, #FBFAF6);

    border:
        1px solid var(--line, #DCD8CB);

    border-radius:
        10px;

    padding:
        1.3rem 1.4rem;
}


.panel-title {

    font-family:
        'Fraunces',
        serif;

    font-weight:
        600;

    font-size:
        1.05rem;

    margin-bottom:
        .1rem;
}


.panel-sub {

    font-size:
        .78rem;

    color:
        var(--slate, #6B7280);
}


/* =====================================================
   STAT CARDS
===================================================== */

.punch-card {

    background:
        var(--paper-2, #FBFAF6);

    border:
        1px solid var(--line, #DCD8CB);

    border-radius:
        10px;

    phoneNumber:
        relative;

    padding:
        1.25rem 1.3rem 1.1rem;

    min-height:
        160px;
}


.punch-card::before {

    content:
        "";

    phoneNumber:
        absolute;

    top:
        -1px;

    left:
        14px;

    right:
        14px;

    height:
        1px;

    background-image:
        radial-gradient(circle,
            var(--paper, #F2F1EA) 3px,
            transparent 3.2px);

    background-size:
        16px 16px;

    background-phoneNumber:
        0 -8px;

    background-repeat:
        repeat-x;
}


.stat-label {

    font-size:
        .72rem;

    text-transform:
        uppercase;

    letter-spacing:
        .08em;

    color:
        var(--slate, #6B7280);

    font-weight:
        600;

    max-width:
        75%;
}


.stat-period {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .65rem;

    color:
        var(--slate, #6B7280);

    margin-top:
        .2rem;

    margin-bottom:
        .15rem;
}


.stat-value {

    font-family:
        'Fraunces',
        serif;

    font-weight:
        600;

    font-size:
        2.1rem;

    line-height:
        1.15;

    margin-top:
        .15rem;
}


.stat-value-money {

    font-size:
        1.65rem;

    padding-top:
        .2rem;
}


.stat-delta {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .72rem;

    margin-top:
        .2rem;
}


.stat-delta--slate {
    color:
        var(--slate, #6B7280);
}


.stat-delta--gold {
    color:
        var(--gold-dark, #9C7726);
}


.stat-delta--blue {
    color:
        #426B8F;
}


.text-success {
    color:
        var(--green, #2F8F5B);
}


/* =====================================================
   STAMPS
===================================================== */

.stamp {

    phoneNumber:
        absolute;

    top:
        14px;

    right:
        14px;

    width:
        44px;

    height:
        44px;

    border-radius:
        50%;

    border:
        2px dashed;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .58rem;

    font-weight:
        600;

    transform:
        rotate(-8deg);
}


.stamp.green {

    color:
        var(--green, #2F8F5B);

    border-color:
        var(--green, #2F8F5B);
}


.stamp.gold {

    color:
        var(--gold-dark, #9C7726);

    border-color:
        var(--gold, #C79A3D);
}


.stamp.blue {

    color:
        #426B8F;

    border-color:
        #6D94B6;
}


/* =====================================================
   SALARY OVERVIEW
===================================================== */

.salary-overview {

    margin-top:
        1.4rem;

    display:
        flex;

    align-items:
        stretch;

    gap:
        2rem;
}


.salary-overview-main {

    flex:
        1;

    padding-right:
        2rem;

    border-right:
        1px solid var(--line, #DCD8CB);
}


.overview-label {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .64rem;

    letter-spacing:
        .08em;

    color:
        var(--slate, #6B7280);

    font-weight:
        600;
}


.overview-value {

    font-family:
        'Fraunces',
        serif;

    font-size:
        2.3rem;

    font-weight:
        600;

    color:
        var(--ink, #1C2B4A);

    margin-top:
        .2rem;
}


.overview-sub {

    color:
        var(--slate, #6B7280);

    font-size:
        .75rem;

    margin-top:
        .15rem;
}


.salary-breakdown {

    flex:
        1;

    display:
        flex;

    flex-direction:
        column;

    justify-content:
        center;

    gap:
        .85rem;
}


.breakdown-item {

    display:
        flex;

    align-items:
        center;

    gap:
        .65rem;
}


.breakdown-dot {

    width:
        8px;

    height:
        8px;

    border-radius:
        50%;

    flex-shrink:
        0;
}


.breakdown-dot.gold {
    background:
        var(--gold, #C79A3D);
}


.breakdown-dot.green {
    background:
        var(--green, #2F8F5B);
}


.breakdown-dot.red {
    background:
        var(--red, #C24D3B);
}


.breakdown-label {

    font-size:
        .7rem;

    color:
        var(--slate, #6B7280);
}


.breakdown-value {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .78rem;

    font-weight:
        600;

    color:
        var(--ink-2, #28395E);
}


/* =====================================================
   SUMMARY
===================================================== */

.summary-list {

    border-top:
        1px solid var(--line, #DCD8CB);
}


.summary-row {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    padding:
        .78rem .1rem;

    border-bottom:
        1px dashed var(--line, #DCD8CB);
}


.summary-label {

    display:
        flex;

    align-items:
        center;

    gap:
        .6rem;

    font-size:
        .84rem;
}


.summary-value {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-weight:
        600;

    color:
        var(--ink-2, #28395E);
}


.summary-dot {

    width:
        8px;

    height:
        8px;

    border-radius:
        50%;
}


.summary-dot.gold {
    background:
        var(--gold, #C79A3D);
}


.summary-dot.green {
    background:
        var(--green, #2F8F5B);
}


.summary-dot.blue {
    background:
        #426B8F;
}


.summary-dot.red {
    background:
        var(--red, #C24D3B);
}


/* =====================================================
   CHIP
===================================================== */

.chip {

    font-size:
        .72rem;

    padding:
        .28rem .6rem;

    border-radius:
        6px;

    font-weight:
        600;

    background:
        var(--amber-bg, #F6EEDB);

    color:
        var(--gold-dark, #9C7726);

    white-space:
        nowrap;
}


/* =====================================================
   SEARCH
===================================================== */

.search-box {

    display:
        flex;

    align-items:
        center;

    gap:
        .45rem;

    border:
        1px solid var(--line, #DCD8CB);

    border-radius:
        6px;

    background:
        var(--paper-2, #FBFAF6);

    padding:
        .4rem .65rem;

    min-width:
        210px;
}


.search-box svg {

    color:
        var(--slate, #6B7280);

    flex-shrink:
        0;
}


.search-box input {

    border:
        none;

    outline:
        none;

    background:
        transparent;

    width:
        100%;

    font-size:
        .75rem;

    color:
        var(--ink, #1C2B4A);
}


.search-box input::placeholder {

    color:
        var(--slate, #6B7280);
}


/* =====================================================
   FILTERS
===================================================== */

.filter-row {

    display:
        flex;

    gap:
        .5rem;

    flex-wrap:
        wrap;

    margin-bottom:
        1rem;
}


.filter-pill {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .7rem;

    font-weight:
        600;

    padding:
        .32rem .65rem;

    border-radius:
        20px;

    letter-spacing:
        .03em;

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper-2, #FBFAF6);

    color:
        var(--slate, #6B7280);

    cursor:
        pointer;

    transition:
        all .15s ease;
}


.filter-pill.active {

    background:
        var(--ink, #1C2B4A);

    color:
        #F3DFA6;

    border-color:
        var(--ink, #1C2B4A);
}


.filter-pill:hover:not(.active) {

    background:
        var(--paper, #F2F1EA);
}


/* =====================================================
   ADD BUTTON
===================================================== */

.add-btn {

    border:
        1px solid var(--ink, #1C2B4A);

    background:
        var(--ink, #1C2B4A);

    color:
        #F3DFA6;

    border-radius:
        6px;

    padding:
        .42rem .8rem;

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .7rem;

    font-weight:
        600;

    cursor:
        pointer;
}


.add-btn:hover {

    background:
        #28395E;
}


/* =====================================================
   TABLE
===================================================== */

.table-responsive {

    overflow-x:
        auto;
}


.table-ledger {

    width:
        100%;

    border-collapse:
        collapse;

    margin-bottom:
        0;
}


.table-ledger thead th {

    font-size:
        .68rem;

    text-transform:
        uppercase;

    letter-spacing:
        .09em;

    color:
        var(--slate, #6B7280);

    border-bottom:
        1px solid var(--line, #DCD8CB);

    font-weight:
        600;

    padding:
        .5rem .5rem .65rem;

    background:
        transparent;

    text-align:
        left;

    white-space:
        nowrap;
}


.table-ledger tbody td {

    padding:
        .75rem .5rem;

    border-bottom:
        1px dashed var(--line, #DCD8CB);

    vertical-align:
        middle;

    font-size:
        .84rem;
}


.table-ledger tbody tr:last-child td {
    border-bottom:
        none;
}


/* =====================================================
   AVATAR
===================================================== */

.avatar-sm {

    width:
        34px;

    height:
        34px;

    border-radius:
        50%;

    overflow:
        hidden;

    background:
        var(--amber-bg, #F6EEDB);

    color:
        var(--gold-dark, #9C7726);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    font-family:
        'Fraunces',
        serif;

    font-weight:
        600;

    font-size:
        .78rem;

    flex-shrink:
        0;
}


.avatar-sm img {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;
}


/* =====================================================
   EMPLOYEE
===================================================== */

.emp-name {
    font-weight:
        600;
}


.emp-role {

    font-size:
        .7rem;

    color:
        var(--slate, #6B7280);
}


.location {

    color:
        var(--ink-2, #28395E);

    font-size:
        .8rem;
}


/* =====================================================
   MONEY
===================================================== */

.money {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .76rem;

    color:
        var(--ink-2, #28395E);

    white-space:
        nowrap;
}


.money-sub {

    font-size:
        .6rem;

    color:
        var(--slate, #6B7280);
}


.allowance {

    color:
        var(--green, #2F8F5B);
}


.deduction {

    color:
        var(--red, #C24D3B);
}


.net-pay {

    font-weight:
        600;

    color:
        var(--ink, #1C2B4A);
}


/* =====================================================
   SALARY TYPE
===================================================== */

.salary-type {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .63rem;

    color:
        var(--slate, #6B7280);

    letter-spacing:
        .03em;
}


/* =====================================================
   STATUS
===================================================== */

.badge-status {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .64rem;

    font-weight:
        600;

    padding:
        .3rem .55rem;

    border-radius:
        5px;

    letter-spacing:
        .03em;

    display:
        inline-block;

    white-space:
        nowrap;
}


.badge-active {

    background:
        var(--green-bg, #E5F2EA);

    color:
        var(--green, #2F8F5B);
}


.badge-inactive {

    background:
        var(--red-bg, #F7E9E6);

    color:
        var(--red, #C24D3B);
}


/* =====================================================
   ACTIONS
===================================================== */

.action-group {

    display:
        flex;

    gap:
        .35rem;
}


.action-btn {

    border:
        1px solid;

    border-radius:
        6px;

    padding:
        .35rem .55rem;

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .63rem;

    font-weight:
        600;

    cursor:
        pointer;

    white-space:
        nowrap;
}


.edit-btn {

    border-color:
        #6D94B6;

    background:
        #E8EEF3;

    color:
        #426B8F;
}


.edit-btn:hover {

    background:
        #426B8F;

    color:
        white;
}


.delete-btn {

    border-color:
        #E5B7AE;

    background:
        var(--red-bg, #F7E9E6);

    color:
        var(--red, #C24D3B);
}


.delete-btn:hover {

    background:
        var(--red, #C24D3B);

    color:
        white;
}


/* =====================================================
   EMPTY
===================================================== */

.empty-state {

    text-align:
        center;

    padding:
        2rem 1rem;

    color:
        var(--slate, #6B7280);

    font-size:
        .85rem;
}



.modal-backdrop {

    position:
        fixed;

    inset:
        0;

    background:
        rgba(28, 43, 74, .45);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        1rem;

    z-index:
        9999;
}


.salary-modal {

    width:
        min(620px, 100%);

    max-height:
        90vh;

    overflow-y:
        auto;

    background:
        var(--paper-2, #FBFAF6);

    border:
        1px solid var(--line, #DCD8CB);

    border-radius:
        12px;

    box-shadow:
        0 20px 50px rgba(28, 43, 74, .2);
}

.modal-header {

    display:
        flex;

    align-items:
        flex-start;

    justify-content:
        space-between;

    gap:
        1rem;

    padding:
        1.25rem 1.4rem;

    border-bottom:
        1px solid var(--line, #DCD8CB);
}


.modal-eyebrow {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .62rem;

    letter-spacing:
        .1em;

    color:
        var(--gold-dark, #9C7726);

    font-weight:
        600;
}


.modal-title {

    font-family:
        'Fraunces',
        serif;

    font-size:
        1.35rem;

    font-weight:
        600;

    color:
        var(--ink, #1C2B4A);

    margin-top:
        .1rem;
}


.modal-sub {

    color:
        var(--slate, #6B7280);

    font-size:
        .75rem;

    margin-top:
        .1rem;
}


.close-btn {

    width:
        32px;

    height:
        32px;

    border:
        1px solid var(--line, #DCD8CB);

    background:
        transparent;

    color:
        var(--slate, #6B7280);

    border-radius:
        6px;

    font-size:
        1.3rem;

    line-height:
        1;

    cursor:
        pointer;
}


.close-btn:hover {

    background:
        var(--paper, #F2F1EA);

    color:
        var(--ink, #1C2B4A);
}


/* =====================================================
   MODAL BODY
===================================================== */

.modal-body {

    padding:
        1.4rem;
}


.form-row {

    display:
        grid;

    grid-template-columns:
        1fr 1fr;

    gap:
        1rem;
}


.form-group {

    margin-bottom:
        1rem;
}


.form-group label {

    display:
        block;

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .64rem;

    text-transform:
        uppercase;

    letter-spacing:
        .07em;

    color:
        var(--slate, #6B7280);

    font-weight:
        600;

    margin-bottom:
        .4rem;
}


.form-control {

    width:
        100%;

    box-sizing:
        border-box;

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper-2, #FBFAF6);

    color:
        var(--ink, #1C2B4A);

    border-radius:
        7px;

    padding:
        .58rem .7rem;

    font-size:
        .8rem;

    outline:
        none;
}


.form-control:focus {

    border-color:
        var(--gold, #C79A3D);

    box-shadow:
        0 0 0 3px rgba(199, 154, 61, .12);
}


.form-control:disabled {

    background:
        var(--paper, #F2F1EA);

    color:
        var(--slate, #6B7280);

    cursor:
        not-allowed;
}


/* =====================================================
   MONEY INPUT
===================================================== */

.input-money {

    phoneNumber:
        relative;
}


.input-money>span {

    phoneNumber:
        absolute;

    left:
        .7rem;

    top:
        50%;

    transform:
        translateY(-50%);

    font-family:
        'IBM Plex Mono',
        monospace;

    color:
        var(--slate, #6B7280);

    font-size:
        .8rem;

    pointer-events:
        none;
}


.input-money .form-control {

    padding-left:
        1.55rem;
}


/* =====================================================
   SALARY PREVIEW
===================================================== */

.salary-preview {

    margin-top:
        .4rem;

    padding:
        1rem;

    border:
        1px solid #D8E6DC;

    background:
        var(--green-bg, #E5F2EA);

    border-radius:
        8px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        1rem;
}


.preview-label {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .6rem;

    letter-spacing:
        .08em;

    color:
        var(--green, #2F8F5B);

    font-weight:
        600;
}


.preview-value {

    font-family:
        'Fraunces',
        serif;

    font-size:
        1.5rem;

    font-weight:
        600;

    color:
        var(--ink, #1C2B4A);

    margin-top:
        .1rem;
}


.preview-equation {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .62rem;

    color:
        var(--slate, #6B7280);

    text-align:
        right;
}


/* =====================================================
   MODAL FOOTER
===================================================== */

.modal-footer {

    display:
        flex;

    align-items:
        center;

    justify-content:
        flex-end;

    gap:
        .6rem;

    padding:
        1rem 1.4rem;

    border-top:
        1px solid var(--line, #DCD8CB);
}


.cancel-btn {

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper-2, #FBFAF6);

    color:
        var(--ink-2, #28395E);

    border-radius:
        6px;

    padding:
        .48rem .8rem;

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .7rem;

    cursor:
        pointer;
}


.cancel-btn:hover {

    background:
        var(--paper, #F2F1EA);
}


.save-btn {

    border:
        1px solid var(--ink, #1C2B4A);

    background:
        var(--ink, #1C2B4A);

    color:
        #F3DFA6;

    border-radius:
        6px;

    padding:
        .48rem .9rem;

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .7rem;

    font-weight:
        600;

    cursor:
        pointer;
}


.save-btn:hover {

    background:
        #28395E;
}


/* =====================================================
   BUTTONS
===================================================== */

.btn {

    border-radius:
        6px;

    padding:
        .45rem .9rem;

    border:
        1px solid transparent;

    cursor:
        pointer;
}


.btn-outline-ledger {

    border:
        1px solid var(--line, #DCD8CB);

    color:
        var(--ink-2, #28395E);

    font-size:
        .85rem;

    font-weight:
        500;

    background:
        var(--paper-2, #FBFAF6);
}


.btn-outline-ledger:hover {

    background:
        var(--paper, #F2F1EA);
}


.btn-sm {

    font-size:
        .82rem;

    padding:
        .4rem .8rem;
}


/* =====================================================
   LAYOUT HELPERS
===================================================== */

.d-flex {
    display:
        flex;
}


.align-items-center {
    align-items:
        center;
}


.align-items-start {
    align-items:
        flex-start;
}


.justify-content-between {
    justify-content:
        space-between;
}


.flex-wrap {
    flex-wrap:
        wrap;
}


.gap-2 {
    gap:
        .5rem;
}


.gap-3 {
    gap:
        1rem;
}


.mb-0 {
    margin-bottom:
        0;
}


.mb-3 {
    margin-bottom:
        1rem;
}


.mb-4 {
    margin-bottom:
        1.5rem;
}


.row {

    display:
        flex;

    flex-wrap:
        wrap;

    margin:
        0 -.5rem;
}


.row>[class*="col-"] {

    padding:
        0 .5rem;
}


.g-3>* {

    padding:
        .5rem;
}


.col-6 {
    width:
        50%;
}


@media (min-width: 992px) {

    .col-lg-3 {
        width:
            25%;
    }

    .col-lg-4 {
        width:
            33.3333%;
    }

    .col-lg-8 {
        width:
            66.6667%;
    }

}


@media (max-width: 991px) {

    .salary-overview {

        flex-direction:
            column;

        gap:
            1.25rem;
    }


    .salary-overview-main {

        border-right:
            none;

        border-bottom:
            1px solid var(--line, #DCD8CB);

        padding-right:
            0;

        padding-bottom:
            1.25rem;
    }

}


@media (max-width: 576px) {

    .content {
        padding:
            1rem;
    }


    .topbar {
        padding:
            1rem;
    }


    .col-6 {
        width:
            100%;
    }


    .form-row {

        grid-template-columns:
            1fr;
    }


    .search-box {

        min-width:
            100%;
    }


    .salary-preview {

        flex-direction:
            column;

        align-items:
            flex-start;
    }


    .preview-equation {

        text-align:
            left;
    }


    .modal-footer {

        justify-content:
            stretch;
    }


    .cancel-btn,
    .save-btn {

        flex:
            1;
    }

}
</style>
