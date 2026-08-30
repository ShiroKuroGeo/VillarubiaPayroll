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
                        Saturday, August 29, 2026
                    </div>

                    <h1>
                        Attendance Management
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


                <input v-model="selectedDate" type="date" class="date-chip" />


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
                            IN
                        </div>

                        <div class="stat-label">
                            Present Today
                        </div>

                        <div class="stat-period">
                            {{ formattedSelectedDate }}
                        </div>

                        <div class="stat-value">
                            {{ presentCount }}
                        </div>

                        <div class="stat-delta stat-delta--slate">
                            Out of {{ totalEmployeeCount }} employees
                        </div>

                    </div>

                </div>

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp gold">
                            LATE
                        </div>

                        <div class="stat-label">
                            Late Arrivals
                        </div>

                        <div class="stat-period">
                            {{ formattedSelectedDate }}
                        </div>

                        <div class="stat-value">
                            {{ lateCount }}
                        </div>

                        <div class="stat-delta stat-delta--gold">
                            Clocked in after 9:00 AM
                        </div>

                    </div>

                </div>

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp blue">
                            HRS
                        </div>

                        <div class="stat-label">
                            Total Hours Logged
                        </div>

                        <div class="stat-period">
                            {{ formattedSelectedDate }}
                        </div>

                        <div class="stat-value stat-value-money">
                            {{ totalHoursLogged }}
                        </div>

                        <div class="stat-delta stat-delta--blue">
                            Across all employees
                        </div>

                    </div>

                </div>


                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp red">
                            OUT
                        </div>

                        <div class="stat-label">
                            Absent / On Leave
                        </div>

                        <div class="stat-period">
                            {{ formattedSelectedDate }}
                        </div>

                        <div class="stat-value">
                            {{ absentCount + leaveCount }}
                        </div>

                        <div class="stat-delta" style="color: var(--red, #C24D3B);">
                            {{ absentCount }} absent · {{ leaveCount }} on leave
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
                                    Attendance overview
                                </div>

                                <div class="panel-sub">
                                    Daily check-in / check-out summary
                                </div>

                            </div>


                            <span class="chip">
                                {{ formattedSelectedDate }}
                            </span>

                        </div>


                        <div class="salary-overview">


                            <div class="salary-overview-main">

                                <div class="overview-label">
                                    ATTENDANCE RATE
                                </div>

                                <div class="overview-value">
                                    {{ attendanceRate }}%
                                </div>

                                <div class="overview-sub">
                                    {{ presentCount + lateCount }} of {{ totalEmployeeCount }} employees checked in
                                </div>

                            </div>


                            <div class="salary-breakdown">

                                <div class="breakdown-item">

                                    <span class="breakdown-dot green"></span>

                                    <div>

                                        <div class="breakdown-label">
                                            On Time
                                        </div>

                                        <div class="breakdown-value">
                                            {{ onTimeCount }} employees
                                        </div>

                                    </div>

                                </div>


                                <div class="breakdown-item">

                                    <span class="breakdown-dot gold"></span>

                                    <div>

                                        <div class="breakdown-label">
                                            Late
                                        </div>

                                        <div class="breakdown-value">
                                            {{ lateCount }} employees
                                        </div>

                                    </div>

                                </div>


                                <div class="breakdown-item">

                                    <span class="breakdown-dot red"></span>

                                    <div>

                                        <div class="breakdown-label">
                                            Absent / Leave
                                        </div>

                                        <div class="breakdown-value">
                                            {{ absentCount + leaveCount }} employees
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
                            Today's status
                        </div>

                        <div class="panel-sub mb-3">
                            Breakdown by attendance status
                        </div>


                        <div class="summary-list">


                            <div class="summary-row">

                                <div class="summary-label">

                                    <span class="summary-dot green"></span>

                                    Present

                                </div>

                                <div class="summary-value">
                                    {{ presentCount }}
                                </div>

                            </div>


                            <div class="summary-row">

                                <div class="summary-label">

                                    <span class="summary-dot gold"></span>

                                    Late

                                </div>

                                <div class="summary-value">
                                    {{ lateCount }}
                                </div>

                            </div>


                            <div class="summary-row">

                                <div class="summary-label">

                                    <span class="summary-dot blue"></span>

                                    On Leave

                                </div>

                                <div class="summary-value">
                                    {{ leaveCount }}
                                </div>

                            </div>


                            <div class="summary-row">

                                <div class="summary-label">

                                    <span class="summary-dot red"></span>

                                    Absent

                                </div>

                                <div class="summary-value">
                                    {{ absentCount }}
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
                            Daily attendance log
                        </div>

                        <div class="panel-sub">
                            Track employee check-ins, check-outs, and hours worked
                        </div>

                    </div>

                    <div class="d-flex gap-2 flex-wrap">


                        <div class="search-box">

                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-3-3" />
                            </svg>

                            <input v-model="searchQuery" type="text" placeholder="Search employee..." />

                        </div>


                        <button class="add-btn" @click="openAddModal">
                            + Add Record
                        </button>

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


                <!-- TABLE -->

                <div class="table-responsive">


                    <table class="table-ledger salary-table" v-if="paginatedAttendanceData.length">

                        <thead>

                            <tr>

                                <th>
                                    Employee
                                </th>

                                <th>
                                    Department
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Time In
                                </th>

                                <th>
                                    Time Out
                                </th>

                                <th>
                                    Hours Worked
                                </th>

                                <th>
                                    Overtime
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


                            <tr v-for="record in paginatedAttendanceData" :key="record.id">

                                <td>

                                    <div class="d-flex align-items-center gap-2">


                                        <div class="avatar-sm">

                                            <img v-if="record.image" :src="record.image" :alt="record.employeeName" />

                                            <span v-else>
                                                {{ record.initials }}
                                            </span>

                                        </div>


                                        <div>

                                            <div class="emp-name">
                                                {{ record.employeeName }}
                                            </div>

                                            <div class="emp-role">
                                                Employee #{{
                                                    record.employeeId
                                                        .toString()
                                                        .padStart(4, '0')
                                                }}
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <!-- Department -->

                                <td>

                                    <span class="department">
                                        {{ record.department }}
                                    </span>

                                </td>


                                <!-- Date -->

                                <td class="money">
                                    {{ record.date }}
                                </td>


                                <!-- Time In -->

                                <td class="money" :class="{ deduction: record.status === 'late' }">
                                    {{ record.timeIn || '—' }}
                                </td>


                                <!-- Time Out -->

                                <td class="money">
                                    {{ record.timeOut || '—' }}
                                </td>


                                <!-- Hours -->

                                <td class="money allowance">
                                    {{ hoursWorked(record) }} hrs
                                </td>


                                <!-- Overtime -->

                                <td class="money">
                                    {{ record.overtimeHours || 0 }} hrs
                                </td>


                                <!-- Status -->

                                <td>

                                    <span class="badge-status" :class="badgeClass(record.status)">
                                        {{ formatStatus(record.status) }}
                                    </span>

                                </td>


                                <!-- Action -->

                                <td>

                                    <div class="action-group">

                                        <button class="action-btn edit-btn" @click="openEditModal(record)">
                                            Edit
                                        </button>


                                        <button class="action-btn delete-btn" @click="deleteAttendance(record)">
                                            Delete
                                        </button>

                                    </div>

                                </td>

                            </tr>


                        </tbody>

                    </table>


                    <div v-else class="empty-state">
                        No attendance records match your search or filter.
                    </div>

                </div>


                <!-- PAGINATION -->

                <div class="pagination-bar" v-if="filteredAttendanceData.length">

                    <div class="pagination-info">
                        Showing {{ paginationStart }}–{{ paginationEnd }} of {{ filteredAttendanceData.length }}
                    </div>


                    <div class="pagination-controls">

                        <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">
                            Prev
                        </button>


                        <button v-for="page in pageNumbers" :key="page" class="page-btn"
                            :class="{ active: page === currentPage }" @click="currentPage = page">
                            {{ page }}
                        </button>


                        <button class="page-btn" :disabled="currentPage === totalPages" @click="currentPage++">
                            Next
                        </button>


                        <select v-model.number="pageSize" class="page-size-select">
                            <option :value="5">5 / page</option>
                            <option :value="10">10 / page</option>
                            <option :value="25">25 / page</option>
                            <option :value="50">50 / page</option>
                        </select>

                    </div>

                </div>

            </div>

        </div>

        <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">

            <div class="salary-modal">

                <div class="modal-header">

                    <div>

                        <div class="modal-eyebrow">
                            {{ editingAttendance ? 'EDIT RECORD' : 'NEW RECORD' }}
                        </div>

                        <div class="modal-title">
                            {{ editingAttendance ? 'Edit Attendance' : 'Add Attendance' }}
                        </div>

                        <div class="modal-sub">
                            Log employee check-in and check-out
                        </div>

                    </div>

                    <button class="close-btn" @click="closeModal" aria-label="Close">
                        ×
                    </button>

                </div>


                <!-- Modal Body -->

                <div class="modal-body">


                    <!-- Employee -->

                    <div class="form-group">

                        <label>
                            Employee
                        </label>

                        <select v-model="attendanceForm.employeeId" class="form-control">

                            <option value="" disabled>
                                Select employee
                            </option>

                            <option v-for="employee in employeeOptions" :key="employee.id" :value="employee.id">
                                {{ employee.name }}
                            </option>

                        </select>

                    </div>


                    <!-- Date / Status -->

                    <div class="form-row">


                        <div class="form-group">

                            <label>
                                Date
                            </label>

                            <input v-model="attendanceForm.date" type="date" class="form-control" />

                        </div>


                        <div class="form-group">

                            <label>
                                Status
                            </label>

                            <select v-model="attendanceForm.status" class="form-control">

                                <option value="present">
                                    Present
                                </option>

                                <option value="late">
                                    Late
                                </option>

                                <option value="absent">
                                    Absent
                                </option>

                                <option value="leave">
                                    On Leave
                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- Time In / Out -->

                    <div class="form-row">


                        <div class="form-group">

                            <label>
                                Time In
                            </label>

                            <input v-model="attendanceForm.timeIn" type="time" class="form-control"
                                :disabled="attendanceForm.status === 'absent' || attendanceForm.status === 'leave'" />

                        </div>


                        <div class="form-group">

                            <label>
                                Time Out
                            </label>

                            <input v-model="attendanceForm.timeOut" type="time" class="form-control"
                                :disabled="attendanceForm.status === 'absent' || attendanceForm.status === 'leave'" />

                        </div>

                    </div>


                    <div class="form-row">

                        <div class="form-group">
                            <label>
                                Overtime (hours)
                            </label>
                            <input v-model.number="attendanceForm.overtimeHours" type="number" min="0" step="0.25"
                                class="form-control" placeholder="0" />
                        </div>

                        <div class="form-group">
                            <label>
                                Notes
                            </label>
                            <input v-model="attendanceForm.notes" type="text" class="form-control"
                                placeholder="Optional remarks" />
                        </div>

                    </div>


                    <!-- Preview -->

                    <div class="salary-preview">


                        <div>

                            <div class="preview-label">
                                HOURS WORKED
                            </div>

                            <div class="preview-value">
                                {{ formHoursWorked }} hrs
                            </div>

                        </div>


                        <div class="preview-equation">

                            {{ attendanceForm.timeIn || '--:--' }}

                            →

                            {{ attendanceForm.timeOut || '--:--' }}

                            (+{{ attendanceForm.overtimeHours || 0 }} OT)

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button class="cancel-btn" @click="closeModal">
                        Cancel
                    </button>


                    <button class="save-btn" @click="saveAttendance">
                        {{ editingAttendance ? 'Save Changes' : 'Add Record' }}
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
    ref,
    watch
} from 'vue'


defineOptions({
    name: 'AttendanceManagementPage'
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
// SELECTED DATE (drives the stat cards)
// =====================================================

const selectedDate = ref('2026-08-29')


const formattedSelectedDate = computed(() => {

    if (!selectedDate.value) {
        return ''
    }

    const date = new Date(`${selectedDate.value}T00:00:00`)

    return date.toLocaleDateString(
        'en-US',
        {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        }
    )

})


// =====================================================
// EMPLOYEE DATA
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
    },

    {
        id: 5,
        name: 'Nadia Ang',
        department: 'Marketing'
    },

    {
        id: 6,
        name: 'Erik Villar',
        department: 'Warehouse'
    }

])


// =====================================================
// ATTENDANCE DATA
// =====================================================

const attendanceData = ref([

    {
        id: 1,

        employeeId: 1,
        employeeName: 'Jonas Diaz',
        initials: 'JD',

        department: 'Warehouse',

        date: '2026-08-29',

        timeIn: '08:02',
        timeOut: '17:05',

        overtimeHours: 0.5,

        status: 'present',

        notes: '',

        image: null
    },


    {
        id: 2,

        employeeId: 2,
        employeeName: 'Carla Santos',
        initials: 'CS',

        department: 'Accounting',

        date: '2026-08-29',

        timeIn: '09:24',
        timeOut: '18:10',

        overtimeHours: 0,

        status: 'late',

        notes: 'Traffic delay',

        image: null
    },


    {
        id: 3,

        employeeId: 3,
        employeeName: 'Ramon Tan',
        initials: 'RT',

        department: 'Logistics',

        date: '2026-08-29',

        timeIn: '07:55',
        timeOut: '17:00',

        overtimeHours: 1,

        status: 'present',

        notes: '',

        image: null
    },


    {
        id: 4,

        employeeId: 4,
        employeeName: 'Paulo Lim',
        initials: 'PL',

        department: 'Customer Care',

        date: '2026-08-29',

        timeIn: '',
        timeOut: '',

        overtimeHours: 0,

        status: 'absent',

        notes: 'No call, no show',

        image: null
    },


    {
        id: 5,

        employeeId: 5,
        employeeName: 'Nadia Ang',
        initials: 'NA',

        department: 'Marketing',

        date: '2026-08-29',

        timeIn: '',
        timeOut: '',

        overtimeHours: 0,

        status: 'leave',

        notes: 'Approved vacation leave',

        image: null
    },


    {
        id: 6,

        employeeId: 6,
        employeeName: 'Erik Villar',
        initials: 'EV',

        department: 'Warehouse',

        date: '2026-08-29',

        timeIn: '08:10',
        timeOut: '17:15',

        overtimeHours: 0,

        status: 'present',

        notes: '',

        image: null
    }

])


// =====================================================
// FILTERS
// =====================================================

const searchQuery = ref('')

const statusFilter = ref('all')


const statusFilters = [

    {
        key: 'all',
        label: 'All'
    },

    {
        key: 'present',
        label: 'Present'
    },

    {
        key: 'late',
        label: 'Late'
    },

    {
        key: 'leave',
        label: 'On Leave'
    },

    {
        key: 'absent',
        label: 'Absent'
    }

]


const recordsForSelectedDate = computed(() => {

    return attendanceData.value.filter(
        record =>
            record.date === selectedDate.value
    )

})


const filteredAttendanceData = computed(() => {

    const search =
        searchQuery.value
            .trim()
            .toLowerCase()


    return recordsForSelectedDate.value.filter(record => {

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
            record.status === statusFilter.value


        return matchesSearch && matchesStatus

    })

})


// =====================================================
// PAGINATION
// =====================================================

const currentPage = ref(1)

const pageSize = ref(10)


const totalPages = computed(() => {

    return Math.max(
        1,
        Math.ceil(filteredAttendanceData.value.length / pageSize.value)
    )

})


const paginatedAttendanceData = computed(() => {

    const start =
        (currentPage.value - 1) * pageSize.value

    return filteredAttendanceData.value.slice(
        start,
        start + pageSize.value
    )

})


const paginationStart = computed(() => {

    if (!filteredAttendanceData.value.length) {
        return 0
    }

    return (currentPage.value - 1) * pageSize.value + 1

})


const paginationEnd = computed(() => {

    return Math.min(
        currentPage.value * pageSize.value,
        filteredAttendanceData.value.length
    )

})


const pageNumbers = computed(() => {

    const pages = []

    for (let page = 1; page <= totalPages.value; page++) {
        pages.push(page)
    }

    return pages

})


// Reset to page 1 whenever the visible record set changes shape
// (date, search, or status filter), so pagination never points
// at an empty page after a filter change.

watch(
    [selectedDate, searchQuery, statusFilter, pageSize],
    () => {
        currentPage.value = 1
    }
)


watch(
    totalPages,
    (newTotal) => {
        if (currentPage.value > newTotal) {
            currentPage.value = newTotal
        }
    }
)


// =====================================================
// STATISTICS (based on selected date)
// =====================================================

const totalEmployeeCount = computed(() => {

    return employeeOptions.value.length

})


const presentCount = computed(() => {

    return recordsForSelectedDate.value.filter(
        record =>
            record.status === 'present'
    ).length

})


const onTimeCount = computed(() => presentCount.value)


const lateCount = computed(() => {

    return recordsForSelectedDate.value.filter(
        record =>
            record.status === 'late'
    ).length

})


const absentCount = computed(() => {

    return recordsForSelectedDate.value.filter(
        record =>
            record.status === 'absent'
    ).length

})


const leaveCount = computed(() => {

    return recordsForSelectedDate.value.filter(
        record =>
            record.status === 'leave'
    ).length

})


const attendanceRate = computed(() => {

    if (!totalEmployeeCount.value) {
        return 0
    }

    const checkedIn =
        presentCount.value + lateCount.value

    return Math.round(
        (checkedIn / totalEmployeeCount.value) * 100
    )

})


function timeStringToHours(timeStr) {

    if (!timeStr) {
        return null
    }

    const [hours, minutes] = timeStr.split(':').map(Number)

    return hours + (minutes / 60)

}


function hoursWorked(record) {

    const start = timeStringToHours(record.timeIn)

    const end = timeStringToHours(record.timeOut)


    if (start === null || end === null) {
        return '0.0'
    }


    let diff = end - start

    if (diff < 0) {
        diff += 24
    }


    return diff.toFixed(1)

}


const totalHoursLogged = computed(() => {

    const total =
        recordsForSelectedDate.value.reduce(
            (sum, record) =>
                sum +
                Number(hoursWorked(record)) +
                Number(record.overtimeHours || 0),
            0
        )


    return total.toFixed(1)

})


// =====================================================
// MODAL
// =====================================================

const showModal = ref(false)

const editingAttendance = ref(false)


const attendanceForm = ref(
    createEmptyForm()
)


function createEmptyForm() {

    return {

        id: null,

        employeeId: '',

        date: selectedDate.value,

        timeIn: '',

        timeOut: '',

        overtimeHours: 0,

        status: 'present',

        notes: ''

    }

}


const formHoursWorked = computed(() => {

    const start = timeStringToHours(attendanceForm.value.timeIn)

    const end = timeStringToHours(attendanceForm.value.timeOut)


    if (start === null || end === null) {
        return '0.0'
    }


    let diff = end - start

    if (diff < 0) {
        diff += 24
    }


    return (diff + Number(attendanceForm.value.overtimeHours || 0)).toFixed(1)

})


// =====================================================
// ADD
// =====================================================

function openAddModal() {

    editingAttendance.value = false

    attendanceForm.value =
        createEmptyForm()

    showModal.value = true

}


// =====================================================
// EDIT
// =====================================================

function openEditModal(record) {

    editingAttendance.value = true

    attendanceForm.value = {

        id: record.id,

        employeeId: record.employeeId,

        date: record.date,

        timeIn: record.timeIn,

        timeOut: record.timeOut,

        overtimeHours: record.overtimeHours,

        status: record.status,

        notes: record.notes

    }

    showModal.value = true

}


// =====================================================
// CLOSE
// =====================================================

function closeModal() {

    showModal.value = false

}


// =====================================================
// SAVE
// =====================================================

function saveAttendance() {

    if (!attendanceForm.value.employeeId) {

        alert('Please select an employee.')

        return

    }


    if (!attendanceForm.value.date) {

        alert('Please select a date.')

        return

    }


    const employee =
        employeeOptions.value.find(
            item =>
                Number(item.id) ===
                Number(attendanceForm.value.employeeId)
        )


    if (!employee) {

        alert('Employee not found.')

        return

    }


    const isAbsentOrLeave =
        attendanceForm.value.status === 'absent' ||
        attendanceForm.value.status === 'leave'


    if (editingAttendance.value) {

        const index =
            attendanceData.value.findIndex(
                item =>
                    item.id ===
                    attendanceForm.value.id
            )


        if (index !== -1) {

            attendanceData.value[index] = {

                ...attendanceData.value[index],

                employeeName: employee.name,

                employeeId:
                    Number(attendanceForm.value.employeeId),

                department:
                    employee.department,

                date:
                    attendanceForm.value.date,

                timeIn:
                    isAbsentOrLeave ? '' : attendanceForm.value.timeIn,

                timeOut:
                    isAbsentOrLeave ? '' : attendanceForm.value.timeOut,

                overtimeHours:
                    Number(attendanceForm.value.overtimeHours) || 0,

                status:
                    attendanceForm.value.status,

                notes:
                    attendanceForm.value.notes

            }

        }

    } else {

        const exists =
            attendanceData.value.some(
                item =>
                    item.employeeId ===
                    Number(attendanceForm.value.employeeId) &&
                    item.date ===
                    attendanceForm.value.date
            )


        if (exists) {

            alert(
                'This employee already has an attendance record for this date. Please edit the existing record instead.'
            )

            return

        }


        const initials =
            employee.name
                .split(' ')
                .map(name => name.charAt(0))
                .join('')
                .substring(0, 2)
                .toUpperCase()


        attendanceData.value.push({

            id:
                Date.now(),

            employeeId:
                Number(attendanceForm.value.employeeId),

            employeeName:
                employee.name,

            initials,

            department:
                employee.department,

            date:
                attendanceForm.value.date,

            timeIn:
                isAbsentOrLeave ? '' : attendanceForm.value.timeIn,

            timeOut:
                isAbsentOrLeave ? '' : attendanceForm.value.timeOut,

            overtimeHours:
                Number(attendanceForm.value.overtimeHours) || 0,

            status:
                attendanceForm.value.status,

            notes:
                attendanceForm.value.notes,

            image:
                null

        })

    }


    closeModal()

}


// =====================================================
// DELETE
// =====================================================

function deleteAttendance(record) {

    const confirmed =
        window.confirm(
            `Delete attendance record for ${record.employeeName} on ${record.date}?`
        )


    if (!confirmed) {
        return
    }


    attendanceData.value =
        attendanceData.value.filter(
            item =>
                item.id !== record.id
        )

}


// =====================================================
// FORMATTING
// =====================================================

function formatStatus(status) {

    const labels = {

        present: 'PRESENT',

        late: 'LATE',

        absent: 'ABSENT',

        leave: 'ON LEAVE'

    }


    return (
        labels[status] ||
        status.toUpperCase()
    )

}


function badgeClass(status) {

    return {

        present: 'badge-active',

        late: 'badge-late',

        absent: 'badge-inactive',

        leave: 'badge-leave'

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
            'Date',
            'Time In',
            'Time Out',
            'Hours Worked',
            'Overtime',
            'Status',
            'Notes'
        ]

    ]


    filteredAttendanceData.value.forEach(record => {

        rows.push([

            record.employeeName,

            record.department,

            record.date,

            record.timeIn,

            record.timeOut,

            hoursWorked(record),

            record.overtimeHours,

            formatStatus(record.status),

            record.notes

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
        `attendance-${selectedDate.value}.csv`


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
   DATE PICKER CHIP
===================================================== */

.date-chip {

    font-family:
        'IBM Plex Mono',
        monospace;

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper-2, #FBFAF6);

    color:
        var(--ink, #1C2B4A);

    border-radius:
        8px;

    padding:
        .45rem .7rem;

    font-size:
        .8rem;

    outline:
        none;
}


.date-chip:focus {

    border-color:
        var(--gold, #C79A3D);
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

    position:
        relative;

    padding:
        1.25rem 1.3rem 1.1rem;

    min-height:
        160px;
}


.punch-card::before {

    content:
        "";

    position:
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

    background-position:
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

    position:
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


.stamp.red {

    color:
        var(--red, #C24D3B);

    border-color:
        #E5B7AE;
}


/* =====================================================
   SALARY / ATTENDANCE OVERVIEW
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


.department {

    color:
        var(--ink-2, #28395E);

    font-size:
        .8rem;
}


/* =====================================================
   MONEY / DATA CELLS
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


.badge-late {

    background:
        var(--amber-bg, #F6EEDB);

    color:
        var(--gold-dark, #9C7726);
}


.badge-leave {

    background:
        #E8EEF3;

    color:
        #426B8F;
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


/* =====================================================
   PAGINATION
===================================================== */

.pagination-bar {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    flex-wrap:
        wrap;

    gap:
        .75rem;

    margin-top:
        1.1rem;

    padding-top:
        1rem;

    border-top:
        1px solid var(--line, #DCD8CB);
}


.pagination-info {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .7rem;

    color:
        var(--slate, #6B7280);
}


.pagination-controls {

    display:
        flex;

    align-items:
        center;

    gap:
        .35rem;

    flex-wrap:
        wrap;
}


.page-btn {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .7rem;

    font-weight:
        600;

    min-width:
        30px;

    padding:
        .35rem .5rem;

    border-radius:
        6px;

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper-2, #FBFAF6);

    color:
        var(--ink-2, #28395E);

    cursor:
        pointer;
}


.page-btn:hover:not(:disabled):not(.active) {

    background:
        var(--paper, #F2F1EA);
}


.page-btn.active {

    background:
        var(--ink, #1C2B4A);

    color:
        #F3DFA6;

    border-color:
        var(--ink, #1C2B4A);
}


.page-btn:disabled {

    opacity:
        .45;

    cursor:
        not-allowed;
}


.page-size-select {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size:
        .7rem;

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper-2, #FBFAF6);

    color:
        var(--ink-2, #28395E);

    border-radius:
        6px;

    padding:
        .35rem .5rem;

    margin-left:
        .3rem;

    outline:
        none;
}


@media (max-width: 576px) {

    .pagination-bar {

        flex-direction:
            column;

        align-items:
            flex-start;
    }

}


/* =====================================================
   MODAL
===================================================== */

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


/* =====================================================
   MODAL HEADER
===================================================== */

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
   SALARY / HOURS PREVIEW
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