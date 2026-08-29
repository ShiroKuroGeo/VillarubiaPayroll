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
                        Employee administration
                    </div>

                    <h1>
                        Employee Management
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


                <button class="btn btn-primary-ledger" @click="openAddModal">
                    + Add Employee
                </button>

            </div>

        </div>


        <!-- CONTENT -->
        <div class="content">

            <!-- SUMMARY -->
            <div class="row g-3 mb-3">

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp green">
                            ALL
                        </div>

                        <div class="stat-label">
                            Total Employees
                        </div>

                        <div class="stat-value">
                            {{ employees.length }}
                        </div>

                        <div class="stat-delta stat-delta--slate">
                            Registered employees
                        </div>

                    </div>

                </div>


                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp green">
                            ON
                        </div>

                        <div class="stat-label">
                            Active
                        </div>

                        <div class="stat-value">
                            {{ activeCount }}
                        </div>

                        <div class="stat-delta text-success">
                            Currently employed
                        </div>

                    </div>

                </div>


                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp gold">
                            OFF
                        </div>

                        <div class="stat-label">
                            Inactive
                        </div>

                        <div class="stat-value">
                            {{ inactiveCount }}
                        </div>

                        <div class="stat-delta stat-delta--gold">
                            Not currently active
                        </div>

                    </div>

                </div>


                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp blue">
                            DEPT
                        </div>

                        <div class="stat-label">
                            Departments
                        </div>

                        <div class="stat-value">
                            {{ departmentCount }}
                        </div>

                        <div class="stat-delta stat-delta--blue">
                            Active departments
                        </div>

                    </div>

                </div>

            </div>


            <!-- EMPLOYEE LIST -->
            <div class="panel">

                <div class="section-header">

                    <div>

                        <div class="section-title">
                            Employee directory
                        </div>

                        <div class="panel-sub">
                            Manage employee records and employment status
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


                    <select v-model="departmentFilter" class="filter-select">
                        <option value="all">
                            All Departments
                        </option>

                        <option v-for="department in departments" :key="department" :value="department">
                            {{ department }}
                        </option>

                    </select>


                    <select v-model="statusFilter" class="filter-select">
                        <option value="all">
                            All Status
                        </option>

                        <option value="active">
                            Active
                        </option>

                        <option value="inactive">
                            Inactive
                        </option>

                    </select>

                </div>


                <!-- TABLE -->
                <div class="table-responsive">

                    <table v-if="filteredEmployees.length" class="table-ledger">

                        <thead>

                            <tr>

                                <th>
                                    Employee
                                </th>

                                <th>
                                    Employee ID
                                </th>

                                <th>
                                    Department
                                </th>

                                <th>
                                    Position
                                </th>

                                <th>
                                    Date Hired
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr v-for="employee in filteredEmployees" :key="employee.id">

                                <!-- EMPLOYEE -->
                                <td>

                                    <div class="employee-cell">

                                        <div class="avatar-sm">
                                            {{ employee.initials }}
                                        </div>

                                        <div>

                                            <div class="emp-name">
                                                {{ employee.name }}
                                            </div>

                                            <div class="emp-email">
                                                {{ employee.email }}
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <!-- ID -->
                                <td>

                                    <span class="employee-id">
                                        {{ employee.employeeId }}
                                    </span>

                                </td>


                                <!-- DEPARTMENT -->
                                <td>

                                    <span class="department">
                                        {{ employee.department }}
                                    </span>

                                </td>


                                <!-- POSITION -->
                                <td>

                                    <span class="position">
                                        {{ employee.position }}
                                    </span>

                                </td>


                                <!-- DATE -->
                                <td>

                                    <span class="date-text">
                                        {{ formatDisplayDate(employee.dateHired) }}
                                    </span>

                                </td>


                                <!-- STATUS -->
                                <td>

                                    <span class="badge-status" :class="statusClass(employee.status)">
                                        {{ employee.status.toUpperCase() }}
                                    </span>

                                </td>


                                <!-- ACTIONS -->
                                <td>

                                    <div class="action-group">

                                        <button class="icon-btn edit-btn" title="Edit employee"
                                            @click="openEditModal(employee)">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z" />
                                            </svg>
                                        </button>


                                        <button class="icon-btn delete-btn" title="Delete employee"
                                            @click="deleteEmployee(employee)">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18" />
                                                <path d="M8 6V4h8v2" />
                                                <path d="M19 6l-1 15H6L5 6" />
                                                <path d="M10 11v6M14 11v6" />
                                            </svg>
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>


                    <div v-else class="empty-state">
                        <div class="empty-icon">
                            👤
                        </div>

                        <div class="empty-title">
                            No employees found
                        </div>

                        <div class="empty-sub">
                            Try changing your search or filters.
                        </div>
                    </div>

                </div>

            </div>

        </div>


        <!-- ADD / EDIT MODAL -->
        <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">

            <div class="employee-modal">

                <div class="modal-header">

                    <div>

                        <div class="modal-title">
                            {{ editingEmployee ? 'Edit Employee' : 'Add Employee' }}
                        </div>

                        <div class="panel-sub">
                            {{
                                editingEmployee
                                    ? 'Update employee information'
                                    : 'Create a new employee record'
                            }}
                        </div>

                    </div>


                    <button class="modal-close" @click="closeModal">
                        ×
                    </button>

                </div>


                <form @submit.prevent="saveEmployee">

                    <div class="form-grid">

                        <div class="form-group full">
                            <div class="photo-upload">

                                <div class="photo-preview">
                                    <img v-if="employeeForm.image" :src="employeeForm.image" alt="Employee photo" />

                                    <span v-else>
                                        {{ employeeInitials }}
                                    </span>
                                </div>

                                <div class="photo-upload-content">

                                    <label class="upload-btn">
                                        Upload Photo

                                        <input type="file" accept="image/*" @change="handleImageUpload" hidden />
                                    </label>

                                    <button v-if="employeeForm.image" type="button" class="remove-photo-btn"
                                        @click="removeEmployeeImage">
                                        Remove
                                    </button>

                                    <div class="upload-help">
                                        JPG, PNG or WEBP · Max 2MB
                                    </div>

                                </div>

                            </div>
                        </div>

                        <div class="form-group">

                            <label>
                                First Name
                            </label>

                            <input v-model="form.firstName" type="text" required placeholder="e.g. Jonas" />

                        </div>

                        <div class="form-group">

                            <label>
                                Last Name
                            </label>

                            <input v-model="form.lastName" type="text" required placeholder="e.g. Diaz" />

                        </div>


                        <!-- EMAIL -->
                        <div class="form-group full">

                            <label>
                                Email
                            </label>

                            <input v-model="form.email" type="email" required placeholder="employee@example.com" />

                        </div>


                        <!-- DEPARTMENT -->
                        <div class="form-group">

                            <label>
                                Department
                            </label>

                            <select v-model="form.department" required>

                                <option value="" disabled>
                                    Select department
                                </option>

                                <option v-for="department in departments" :key="department" :value="department">
                                    {{ department }}
                                </option>

                            </select>

                        </div>


                        <!-- POSITION -->
                        <div class="form-group">

                            <label>
                                Position
                            </label>

                            <input v-model="form.position" type="text" required placeholder="e.g. Warehouse Staff" />

                        </div>


                        <!-- DATE HIRED -->
                        <div class="form-group">

                            <label>
                                Date Hired
                            </label>

                            <input v-model="form.dateHired" type="date" required />

                        </div>


                        <!-- STATUS -->
                        <div class="form-group">

                            <label>
                                Status
                            </label>

                            <select v-model="form.status" required>

                                <option value="active">
                                    Active
                                </option>

                                <option value="inactive">
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary-ledger" @click="closeModal">
                            Cancel
                        </button>


                        <button type="submit" class="btn btn-primary-ledger">
                            {{ editingEmployee ? 'Save Changes' : 'Create Employee' }}
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
</template>


<script setup>

import {
    computed,
    onBeforeUnmount,
    onMounted,
    reactive,
    ref
} from 'vue'


defineOptions({
    name: 'EmployeeManagementView'
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


// ─────────────────────────────────────────────
// Departments
// ─────────────────────────────────────────────

const departments = [
    'Warehouse',
    'Accounting',
    'Logistics',
    'Customer Care',
    'Marketing',
    'Human Resources',
    'IT'
]


// ─────────────────────────────────────────────
// Employees
// ─────────────────────────────────────────────

const employees = ref([
    {
        id: 1,
        firstName: 'Jonas',
        lastName: 'Diaz',
        name: 'Jonas Diaz',
        initials: 'JD',
        email: 'jonas@example.com',
        phone: '09171234567',
        role: 'Warehouse Staff',
        department: 'Warehouse',
        salary: 25000,
        image: null,
        status: 'active'
    },

    {
        id: 2,
        firstName: 'Carla',
        lastName: 'Santos',
        name: 'Carla Santos',
        initials: 'CS',
        email: 'carla@example.com',
        phone: '09181234567',
        role: 'Accountant',
        department: 'Accounting',
        salary: 28000,
        image: null,
        status: 'active'
    }
])


// ─────────────────────────────────────────────
// Search / Filters
// ─────────────────────────────────────────────

const searchQuery = ref('')

const departmentFilter = ref('all')

const statusFilter = ref('all')


const filteredEmployees = computed(() => {

    const search =
        searchQuery.value
            .trim()
            .toLowerCase()


    return employees.value.filter(employee => {

        const matchesSearch =
            !search ||
            employee.name
                .toLowerCase()
                .includes(search) ||
            employee.employeeId
                .toLowerCase()
                .includes(search) ||
            employee.email
                .toLowerCase()
                .includes(search) ||
            employee.department
                .toLowerCase()
                .includes(search)


        const matchesDepartment =
            departmentFilter.value === 'all' ||
            employee.department === departmentFilter.value


        const matchesStatus =
            statusFilter.value === 'all' ||
            employee.status === statusFilter.value


        return (
            matchesSearch &&
            matchesDepartment &&
            matchesStatus
        )

    })

})

// _____________________________________________
// Form
// _____________________________________________
const employeeForm = ref({
    id: null,
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    role: '',
    department: '',
    salary: '',
    image: null,
    status: 'active'
})


const employeeInitials = computed(() => {

    const first = employeeForm.value.firstName?.trim()
    const last = employeeForm.value.lastName?.trim()

    return (
        (first?.charAt(0) || '') +
        (last?.charAt(0) || '')
    ).toUpperCase() || 'NA'

})


function handleImageUpload(event) {

    const file = event.target.files?.[0]

    if (!file) {
        return
    }

    // 2MB limit
    if (file.size > 2 * 1024 * 1024) {

        alert('Image must be smaller than 2MB.')

        event.target.value = ''

        return
    }

    if (!file.type.startsWith('image/')) {

        alert('Please select a valid image.')

        event.target.value = ''

        return
    }

    employeeForm.value.image =
        URL.createObjectURL(file)

}


function removeEmployeeImage() {

    employeeForm.value.image = null

}



// ─────────────────────────────────────────────
// Statistics
// ─────────────────────────────────────────────

const activeCount = computed(() => {

    return employees.value.filter(
        employee =>
            employee.status === 'active'
    ).length

})


const inactiveCount = computed(() => {

    return employees.value.filter(
        employee =>
            employee.status === 'inactive'
    ).length

})


const departmentCount = computed(() => {

    return new Set(
        employees.value
            .filter(employee =>
                employee.status === 'active'
            )
            .map(employee =>
                employee.department
            )
    ).size

})


// ─────────────────────────────────────────────
// Modal
// ─────────────────────────────────────────────

const showModal = ref(false)

const editingEmployee = ref(null)


const form = reactive({
    firstName: '',
    lastName: '',
    email: '',
    department: '',
    position: '',
    dateHired: '',
    status: 'active'
})


// ─────────────────────────────────────────────
// Add
// ─────────────────────────────────────────────

function openAddModal() {

    editingEmployee.value = null

    Object.assign(form, {
        firstName: '',
        lastName: '',
        email: '',
        department: '',
        position: '',
        dateHired: '',
        status: 'active'
    })

    showModal.value = true

}


// ─────────────────────────────────────────────
// Edit
// ─────────────────────────────────────────────

function openEditModal(employee) {

    editingEmployee.value = employee

    Object.assign(form, {
        firstName: employee.firstName,
        lastName: employee.lastName,
        email: employee.email,
        department: employee.department,
        position: employee.position,
        dateHired: employee.dateHired,
        status: employee.status
    })

    showModal.value = true

}


// ─────────────────────────────────────────────
// Save
// ─────────────────────────────────────────────

function saveEmployee() {

    const firstName =
        form.firstName.trim()

    const lastName =
        form.lastName.trim()

    const name =
        `${firstName} ${lastName}`

    const initials =
        `${firstName.charAt(0)}${lastName.charAt(0)}`
            .toUpperCase()


    if (editingEmployee.value) {

        Object.assign(
            editingEmployee.value,
            {
                firstName,
                lastName,
                name,
                initials,
                email: form.email.trim(),
                department: form.department,
                position: form.position.trim(),
                dateHired: form.dateHired,
                status: form.status
            }
        )

    } else {

        const nextId =
            employees.value.length
                ? Math.max(
                    ...employees.value.map(
                        employee => employee.id
                    )
                ) + 1
                : 1


        employees.value.push({

            id: nextId,

            employeeId:
                `EMP-${String(nextId).padStart(4, '0')}`,

            firstName,
            lastName,
            name,
            initials,

            email:
                form.email.trim(),

            department:
                form.department,

            position:
                form.position.trim(),

            dateHired:
                form.dateHired,

            status:
                form.status

        })

    }


    closeModal()

}


// ─────────────────────────────────────────────
// Delete
// ─────────────────────────────────────────────

function deleteEmployee(employee) {

    const confirmed =
        window.confirm(
            `Delete ${employee.name} (${employee.employeeId})? This action cannot be undone.`
        )


    if (!confirmed) {
        return
    }


    employees.value =
        employees.value.filter(
            item =>
                item.id !== employee.id
        )

}


// ─────────────────────────────────────────────
// Close Modal
// ─────────────────────────────────────────────

function closeModal() {

    showModal.value = false

    editingEmployee.value = null

}


// ─────────────────────────────────────────────
// Formatting
// ─────────────────────────────────────────────

function formatDisplayDate(date) {

    if (!date) {
        return '—'
    }


    return new Date(
        `${date}T00:00:00`
    ).toLocaleDateString(
        'en-US',
        {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        }
    )

}


function statusClass(status) {

    return {
        active: 'badge-active',
        inactive: 'badge-inactive'
    }[status]

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

    clearInterval(clockTimer)

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

    border-bottom:
        1px solid var(--line, #DCD8CB);

    padding:
        1rem 1.75rem;

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

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper-2, #FBFAF6);

    color:
        var(--ink, #1C2B4A);

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

    font-family:
        'IBM Plex Mono',
        monospace;

    background:
        var(--ink, #1C2B4A);

    color:
        #F3DFA6;

    border-radius: 8px;

    padding:
        .5rem .9rem;

    font-size: .82rem;

    display: flex;

    align-items: center;

    gap: .5rem;
}


.clock-chip .dot {

    width: 6px;

    height: 6px;

    border-radius: 50%;

    background:
        var(--green, #2F8F5B);

    box-shadow:
        0 0 0 3px rgba(47, 143, 91, .25);
}


/* CONTENT */

.content {
    padding: 1.75rem;
}


/* BUTTONS */

.btn {

    border-radius: 6px;

    padding:
        .5rem .9rem;

    border:
        1px solid transparent;

    cursor: pointer;

    font-size: .8rem;

    font-weight: 600;
}


.btn-primary-ledger {

    background:
        var(--ink, #1C2B4A);

    color: #F3DFA6;

    border-color:
        var(--ink, #1C2B4A);
}


.btn-primary-ledger:hover {

    background:
        #28395E;

}


.btn-secondary-ledger {

    background:
        var(--paper-2, #FBFAF6);

    color:
        var(--ink-2, #28395E);

    border-color:
        var(--line, #DCD8CB);
}


.btn-secondary-ledger:hover {

    background:
        var(--paper, #F2F1EA);
}


/* STATS */

.punch-card {

    background:
        var(--paper-2, #FBFAF6);

    border:
        1px solid var(--line, #DCD8CB);

    border-radius: 10px;

    position: relative;

    padding:
        1.25rem 1.3rem 1.1rem;

    min-height: 145px;
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

    color:
        var(--slate, #6B7280);

    font-weight: 600;
}


.stat-value {

    font-family: 'Fraunces', serif;

    font-weight: 600;

    font-size: 2.1rem;

    line-height: 1.15;

    margin-top: .5rem;
}


.stat-delta {

    font-family:
        'IBM Plex Mono',
        monospace;

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

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size: .58rem;

    font-weight: 600;

    transform: rotate(-8deg);
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

    color: #426B8F;

    border-color: #6D94B6;
}


/* PANEL */

.panel {

    background:
        var(--paper-2, #FBFAF6);

    border:
        1px solid var(--line, #DCD8CB);

    border-radius: 10px;

    padding:
        1.3rem 1.4rem;
}


.section-title {

    font-family:
        'Fraunces',
        serif;

    font-weight: 600;

    font-size: 1.1rem;

    margin-bottom: .15rem;
}


.panel-sub {

    font-size: .78rem;

    color:
        var(--slate, #6B7280);
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
}


.search-box {

    flex: 1;

    min-width: 220px;

    display: flex;

    align-items: center;

    gap: .5rem;

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper-2, #FBFAF6);

    border-radius: 7px;

    padding:
        .55rem .7rem;

    color:
        var(--slate, #6B7280);
}


.search-box input {

    width: 100%;

    border: none;

    outline: none;

    background: transparent;

    color:
        var(--ink, #1C2B4A);

    font-size: .8rem;
}


.filter-select {

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper-2, #FBFAF6);

    color:
        var(--ink-2, #28395E);

    border-radius: 7px;

    padding:
        .55rem .7rem;

    font-size: .78rem;

    outline: none;

    cursor: pointer;
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

    color:
        var(--slate, #6B7280);

    border-bottom:
        1px solid var(--line, #DCD8CB);

    font-weight: 600;

    padding:
        .5rem .5rem .65rem;

    text-align: left;

    white-space: nowrap;
}


.table-ledger tbody td {

    padding: .8rem .5rem;

    border-bottom:
        1px dashed var(--line, #DCD8CB);

    vertical-align: middle;

    font-size: .82rem;
}


.table-ledger tbody tr:last-child td {
    border-bottom: none;
}


/* EMPLOYEE */

.employee-cell {

    display: flex;

    align-items: center;

    gap: .65rem;

    min-width: 210px;
}


.avatar-sm {

    width: 34px;

    height: 34px;

    border-radius: 50%;

    background:
        var(--amber-bg, #F6EEDB);

    color:
        var(--gold-dark, #9C7726);

    display: flex;

    align-items: center;

    justify-content: center;

    font-family:
        'Fraunces',
        serif;

    font-weight: 600;

    font-size: .78rem;

    flex-shrink: 0;
}


.emp-name {
    font-weight: 600;
}


.emp-email {

    font-size: .68rem;

    color:
        var(--slate, #6B7280);

    margin-top: .1rem;
}


.employee-id {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size: .72rem;

    color:
        var(--ink-2, #28395E);
}


.department {

    font-size: .78rem;

    color:
        var(--ink-2, #28395E);
}


.position {

    font-size: .78rem;

    color:
        var(--slate, #6B7280);
}


.date-text {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size: .7rem;

    color:
        var(--slate, #6B7280);

    white-space: nowrap;
}


/* STATUS */

.badge-status {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size: .62rem;

    font-weight: 600;

    padding:
        .3rem .55rem;

    border-radius: 5px;

    letter-spacing: .03em;

    display: inline-block;
}


.badge-active {

    background:
        var(--green-bg, #E5F2EA);

    color:
        var(--green, #2F8F5B);
}


.badge-inactive {

    background:
        var(--amber-bg, #F6EEDB);

    color:
        var(--gold-dark, #9C7726);
}


/* ACTIONS */

.action-group {

    display: flex;

    align-items: center;

    gap: .4rem;
}


.icon-btn {

    width: 32px;

    height: 32px;

    border-radius: 6px;

    display: flex;

    align-items: center;

    justify-content: center;

    cursor: pointer;

    background:
        var(--paper-2, #FBFAF6);

    transition: .15s ease;
}


.edit-btn {

    border:
        1px solid #B8C9D8;

    color:
        #426B8F;
}


.edit-btn:hover {

    background:
        #E8EEF3;
}


.delete-btn {

    border:
        1px solid #E5B7AE;

    color:
        var(--red, #C24D3B);
}


.delete-btn:hover {

    background:
        var(--red-bg, #F7E9E6);
}


/* EMPTY */

.empty-state {

    text-align: center;

    padding:
        3rem 1rem;

    color:
        var(--slate, #6B7280);
}


.empty-icon {
    font-size: 2rem;
}


.empty-title {

    font-family:
        'Fraunces',
        serif;

    color:
        var(--ink, #1C2B4A);

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

    background:
        rgba(28, 43, 74, .42);

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 1rem;

    z-index: 1000;
}


.employee-modal {

    width: min(680px, 100%);

    max-height: 90vh;

    overflow-y: auto;

    background:
        var(--paper-2, #FBFAF6);

    border:
        1px solid var(--line, #DCD8CB);

    border-radius: 12px;

    box-shadow:
        0 20px 60px rgba(28, 43, 74, .18);
}


.modal-header {

    padding:
        1.2rem 1.3rem;

    border-bottom:
        1px solid var(--line, #DCD8CB);

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 1rem;
}


.modal-title {

    font-family:
        'Fraunces',
        serif;

    font-weight: 600;

    font-size: 1.2rem;
}


.modal-close {

    border: none;

    background: transparent;

    color:
        var(--slate, #6B7280);

    font-size: 1.6rem;

    line-height: 1;

    cursor: pointer;
}


.modal-close:hover {
    color: var(--ink, #1C2B4A);
}


/* FORM */

form {
    padding: 1.3rem;
}


.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 1rem;
}


.form-group {

    display: flex;

    flex-direction: column;

    gap: .35rem;
}


.form-group.full {
    grid-column: 1 / -1;
}


.form-group label {

    font-family:
        'IBM Plex Mono',
        monospace;

    font-size: .63rem;

    text-transform: uppercase;

    letter-spacing: .08em;

    color:
        var(--slate, #6B7280);

    font-weight: 600;
}


.form-group input,
.form-group select {

    width: 100%;

    box-sizing: border-box;

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper-2, #FBFAF6);

    color:
        var(--ink, #1C2B4A);

    border-radius: 7px;

    padding:
        .6rem .7rem;

    font-size: .8rem;

    outline: none;
}


.form-group input:focus,
.form-group select:focus {

    border-color:
        var(--gold, #C79A3D);

    box-shadow:
        0 0 0 3px rgba(199, 154, 61, .12);
}


.modal-footer {

    display: flex;

    justify-content: flex-end;

    gap: .6rem;

    padding-top: 1.3rem;

    margin-top: 1.3rem;

    border-top:
        1px solid var(--line, #DCD8CB);
}


/* LAYOUT */

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


.row {

    display: flex;

    flex-wrap: wrap;

    margin:
        0 -.5rem;
}


.row>[class*="col-"] {
    padding:
        0 .5rem;
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

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-group.full {
        grid-column: auto;
    }

    .search-box {
        min-width: 100%;
    }

    .filter-select {
        width: 100%;
    }

}


.employee-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    overflow: hidden;

    background: var(--amber-bg, #F6EEDB);
    color: var(--gold-dark, #9C7726);

    display: flex;
    align-items: center;
    justify-content: center;

    font-family: 'Fraunces', serif;
    font-weight: 600;

    flex-shrink: 0;
}

.employee-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}


.photo-upload {
    display: flex;
    align-items: center;
    gap: 1rem;

    padding: 1rem;

    border: 1px dashed var(--line, #DCD8CB);
    border-radius: 10px;

    background: var(--paper, #F2F1EA);
}


.photo-preview {
    width: 76px;
    height: 76px;

    border-radius: 50%;

    overflow: hidden;

    background: var(--amber-bg, #F6EEDB);
    color: var(--gold-dark, #9C7726);

    display: flex;
    align-items: center;
    justify-content: center;

    font-family: 'Fraunces', serif;
    font-size: 1.4rem;
    font-weight: 600;

    flex-shrink: 0;
}

.photo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}


.photo-upload-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: .4rem;
}


.upload-btn {
    display: inline-flex;
    align-items: center;

    padding: .45rem .8rem;

    border-radius: 6px;

    background: var(--ink, #1C2B4A);
    color: #F3DFA6;

    font-family: 'IBM Plex Mono', monospace;
    font-size: .7rem;
    font-weight: 600;

    cursor: pointer;
}


.upload-btn:hover {
    opacity: .9;
}


.remove-photo-btn {
    border: none;
    background: transparent;

    color: var(--red, #C24D3B);

    font-size: .7rem;

    cursor: pointer;

    padding: 0;
}


.upload-help {
    color: var(--slate, #6B7280);
    font-size: .68rem;
}
</style>