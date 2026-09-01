<template>
    <aside class="sidebar" :class="{ open: modelValue }">
        <div class="brand d-flex align-items-center gap-2">
            <div class="brand-mark" style="font-size: 11px;">A&amp;P</div>
            <div>
                <div class="brand-name">Villarubia A&amp;P</div>
                <div class="brand-sub">Attendance &amp; Payroll</div>
            </div>
        </div>

        <nav class="nav-ledger">
            <div class="nav-label">Overview</div>
            <a v-for="item in navOverview" :key="item.key" :class="{ active: isActive(item.key) }"
                @click="select(item.key)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    v-html="item.icon"></svg>
                {{ item.label }}
            </a>

            <div class="nav-label">Manage</div>
            <a v-for="item in navManage" :key="item.key" :class="{ active: isActive(item.key) }"
                @click="select(item.key)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    v-html="item.icon"></svg>
                {{ item.label }}
            </a>
        </nav>

        <div class="sidebar-foot">

            <div class="foot-user">

                <div class="avatar-ring">
                    {{ userInitials }}
                </div>

                <div class="foot-copy">

                    <div class="foot-name">
                        {{ userName }}
                    </div>

                    <div class="foot-role">
                        {{ userRole }}
                    </div>

                </div>

            </div>


            <button class="logout-btn" @click="logout" aria-label="Log out" title="Log out">

                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                </svg>

            </button>

        </div>
    </aside>
</template>

<script setup>
import { computed } from 'vue'

import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    activeNav: {
        type: String,
        default: 'dashboard',
    },
    userName: {
        type: String,
        default: 'Mara Reyes',
    },
    userRole: {
        type: String,
        default: 'HR Administrator',
    },
})

const emit = defineEmits([
    'update:modelValue',
    'update:activeNav',
    'navigate',
])

const navOverview = [
    {
        key: 'dashboard',
        label: 'Dashboard',
        icon: '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
    },
    {
        key: 'attendance',
        label: 'Attendance',
        icon: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
    },
    {
        key: 'payroll',
        label: 'Payroll',
        icon: '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
    },
]

const navManage = [
    {
        key: 'employeeManagement',
        label: 'Employees',
        icon: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    },
    {
        key: 'attendanceManagement',
        label: 'Attendance',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="8.5" cy="7" r="4"/>
                <polyline points="17 11 19 13 23 9"/>
                </svg>`,
    },
    {
        key: 'salaryManagement',
        label: 'Salary',
        icon: '<path d="M6 2h9l4 4v16H6z"/><path d="M15 2v5h5"/><path d="M9 11h6"/><path d="M9 15h6"/><path d="M9 19h4"/><circle cx="17" cy="15" r="2"/><path d="M17 13v4"/>',
    },
    {
        key: 'payrollManagement',
        label: 'Payroll',
        icon: '<path d="M6 2h9l4 4v16H6z"/><path d="M15 2v5h5"/><path d="M9 11h6"/><path d="M9 15h6"/><path d="M9 19h4"/><circle cx="17" cy="15" r="2"/><path d="M17 13v4"/>',
    },
    {
        key: 'cashAdvanceManagement',
        label: 'Cash Advance',
        icon: '<path d="M3 7h18v14H3z"/><path d="M3 7l2-4h14l2 4"/><circle cx="12" cy="14" r="3"/><path d="M6 11h.01M18 17h.01"/>',
    },
    {
        key: 'deductionManagement',
        label: 'Deductions',
        icon: '<path d="M3 7h18v14H3z"/><path d="M3 7l2-4h14l2 4"/><circle cx="12" cy="14" r="3"/><path d="M6 11h.01M18 17h.01"/>',
    },
    // {
    //     key: 'reportsManagement',
    //     label: 'Reports',
    //     icon: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/>',
    // },
    {
        key: 'setting',
        label: 'Settings',
        icon: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
    },
]

const userInitials = computed(() => {
    return props.userName
        .split(' ')
        .map(w => w[0])
        .join('')
        .slice(0, 2)
        .toUpperCase()
})

function select(key) {
    emit('update:activeNav', key)

    const routes = {
        dashboard: 'admin.dashboard',
        attendance: 'admin.attendance',
        payroll: 'admin.payroll',
        employeeManagement: 'admin.employeeManagement',
        salaryManagement: 'admin.salaryManagement',
        cashAdvanceManagement: 'admin.cashAdvanceManagement',
        payrollManagement: 'admin.payrollManagement',
        attendanceManagement: 'admin.attendanceManagement',
        deductionManagement: 'admin.deductionManagement',
        setting: 'admin.setting',
    }

    if (routes[key]) {
        router.push({
            name: routes[key],
        })
    }

    emit('navigate', key)
    emit('update:modelValue', false)
}

const logout = () => {
    alert();
}

function isActive(key) {
    const routes = {
        dashboard: 'admin.dashboard',
        attendance: 'admin.attendance',
        payroll: 'admin.payroll',
        employeeManagement: 'admin.employeeManagement',
        salaryManagement: 'admin.salaryManagement',
        payrollManagement: 'admin.payrollManagement',
        cashAdvanceManagement: 'admin.cashAdvanceManagement',
        attendanceManagement: 'admin.attendanceManagement',
        deductionManagement: 'admin.deductionManagement',
        setting: 'admin.setting',
    }

    return route.name === routes[key]
}
</script>


<style scoped>
.sidebar {
    width: 248px;
    background: var(--ink, #1C2B4A);
    color: #EDEFF5;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: 100vh;
}

.brand {
    padding: 1.6rem 1.4rem 1.2rem;
    border-bottom: 1px solid rgba(255, 255, 255, .08);
}

.brand-mark {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: linear-gradient(155deg, var(--gold, #C79A3D), var(--gold-dark, #9C7726));
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Fraunces', serif;
    font-weight: 700;
    color: var(--ink, #1C2B4A);
    font-size: 1.05rem;
}

.brand-name {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.15rem;
    letter-spacing: .2px;
}

.brand-sub {
    font-size: .7rem;
    color: #9AA6C4;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.nav-ledger {
    padding: 1rem .8rem;
    flex: 1;
    overflow-y: auto;
}

.nav-label {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: #7C88AA;
    padding: .6rem .7rem .3rem;
}

.nav-ledger a {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .55rem .7rem;
    border-radius: 8px;
    color: #C6CCE0;
    text-decoration: none;
    font-size: .89rem;
    font-weight: 500;
    margin-bottom: .15rem;
    cursor: pointer;
    user-select: none;
    transition: background .15s ease, color .15s ease;
}

.nav-ledger a svg {
    opacity: .8;
    flex-shrink: 0;
}

.nav-ledger a:hover {
    background: rgba(255, 255, 255, .06);
    color: #fff;
}

.nav-ledger a.active {
    background: rgba(199, 154, 61, .16);
    color: #F3DFA6;
    box-shadow: inset 3px 0 0 var(--gold, #C79A3D);
}

.sidebar-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .85rem .9rem;
}

.foot-user {
    display: flex;
    align-items: center;
    gap: .65rem;
    min-width: 0;
}


.avatar-ring {

    width: 38px;
    height: 38px;
    border-radius: 50%;
    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    background: var(--amber-bg, #F6EEDB);
    color: var(--gold-dark, #9C7726);
    border: 2px dashed var(--gold, #C79A3D);

    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: .78rem;
}


.foot-copy {
    min-width: 0;
}


.foot-name {

    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: .84rem;
    color: var(--ink, #1C2B4A);

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}


.foot-role {

    font-family: 'IBM Plex Mono', monospace;
    font-size: .64rem;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: var(--slate, #6B7280);

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}


.logout-btn {

    width: 32px;
    height: 32px;
    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border: 1px solid var(--line, #DCD8CB);
    background: var(--paper-2, #FBFAF6);
    color: var(--slate, #6B7280);
    border-radius: 7px;

    cursor: pointer;
    transition: all .15s ease;
}


.logout-btn:hover {

    background: var(--red, #C24D3B);
    border-color: var(--red, #C24D3B);
    color: white;
}

.avatar-ring {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(155deg, #3C4E75, #28395E);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: .85rem;
    color: #EDEFF5;
    border: 1px solid rgba(255, 255, 255, .15);
}

.foot-name {
    font-size: .85rem;
    font-weight: 600;
    color: #EDEFF5;
}

.foot-role {
    font-size: .72rem;
    color: #9AA6C4;
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

@media (max-width: 991px) {
    .sidebar {
        position: fixed;
        left: -260px;
        z-index: 1050;
        transition: left .2s ease;
        box-shadow: 4px 0 24px rgba(0, 0, 0, .25);
    }

    .sidebar.open {
        left: 0;
    }
}
</style>