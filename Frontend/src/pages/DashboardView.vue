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
                    <div class="eyebrow">{{ todayLabel }}</div>
                    <h1>{{ title }}</h1>
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

                <button class="btn btn-ink btn-sm" :disabled="payrollRunning" @click="runPayroll">
                    {{ payrollRunning ? 'Running…' : (payrollJustRan ? 'Payroll run ✓' : 'Run Payroll') }}
                </button>
            </div>
        </div>

        <div class="content">

            <!-- Stat cards -->
            <div class="row g-3 mb-4">

                <!-- Total Employees -->
                <div class="col-6 col-lg-3">
                    <div class="punch-card">
                        <div class="stamp green">STAFF</div>

                        <div class="stat-label">
                            Total Employees
                        </div>

                        <div class="stat-period">
                            August 17–22, 2026
                        </div>

                        <div class="stat-value">
                            {{ totalEmployees }}
                        </div>

                        <div class="stat-delta text-success">
                            ▲ 6 new this month
                        </div>
                    </div>
                </div>

                <!-- Total Salary Paid -->
                <div class="col-6 col-lg-3">
                    <div class="punch-card">
                        <div class="stamp green">PAID</div>

                        <div class="stat-label">
                            Total Salary Submitted / Paid
                        </div>

                        <div class="stat-period">
                            August 17–22, 2026
                        </div>

                        <div class="stat-value font-mono stat-value--sm">
                            {{ peso(totalSalaryPaidThisWeek) }}
                        </div>

                        <div class="stat-delta text-success">
                            Salary processed this week
                        </div>
                    </div>
                </div>

                <!-- Cash Advance -->
                <div class="col-6 col-lg-3">
                    <div class="punch-card">
                        <div class="stamp gold">C.A.</div>

                        <div class="stat-label">
                            Total C.A. This Week
                        </div>

                        <div class="stat-period">
                            August 17–22, 2026
                        </div>

                        <div class="stat-value font-mono stat-value--sm">
                            {{ peso(totalCashAdvanceThisWeek) }}
                        </div>

                        <div class="stat-delta stat-delta--gold">
                            Cash advances released
                        </div>
                    </div>
                </div>

                <!-- Attendance Today -->
                <div class="col-6 col-lg-3">
                    <div class="punch-card">
                        <div class="stamp green">TODAY</div>

                        <div class="stat-label">
                            Total Attendance Today
                        </div>

                        <div class="stat-period">
                            August 29, 2026
                        </div>

                        <div class="stat-value">
                            {{ presentCount }}
                        </div>

                        <div class="stat-delta stat-delta--slate">
                            {{ attendanceRate }}% attendance rate
                        </div>
                    </div>
                </div>

            </div>

            <!-- Charts -->
            <div class="row g-3 mb-4">

                <!-- Weekly Attendance -->
                <div class="col-lg-7">
                    <div class="panel h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="panel-title">
                                    Weekly attendance
                                </div>

                                <div class="panel-sub">
                                    Total attendance, August 17–22, 2026
                                </div>
                            </div>

                            <span class="chip">
                                This week
                            </span>
                        </div>

                        <canvas ref="attendanceCanvas" height="130"></canvas>
                    </div>
                </div>

                <!-- 5 Week Salary -->
                <div class="col-lg-5">
                    <div class="panel h-100">

                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="panel-title">
                                    5 Week Total Salary Paid
                                </div>

                                <div class="panel-sub">
                                    Salary paid across the last 5 weeks
                                </div>
                            </div>

                            <span class="chip">
                                Salary
                            </span>
                        </div>

                        <div class="salary-chart-wrapper">
                            <canvas ref="salaryCanvas"></canvas>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Attendance + Recent Cash Advance -->
            <div class="row g-3">

                <!-- Today's Punch Log -->
                <div class="col-lg-7">
                    <div class="panel h-100">

                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                            <div class="section-title mb-0">
                                Today's punch log
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <button v-for="f in statusFilters" :key="f.key" class="filter-pill"
                                    :class="{ active: statusFilter === f.key }" @click="statusFilter = f.key">
                                    {{ f.label }}
                                </button>
                            </div>

                        </div>

                        <div class="table-responsive">

                            <table class="table table-ledger" v-if="filteredLog.length">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Clock in</th>
                                        <th>Clock out</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="p in filteredLog" :key="p.id">
                                        <td class="d-flex align-items-center gap-2">

                                            <div class="avatar-sm">
                                                {{ p.initials }}
                                            </div>

                                            <div>
                                                <div class="emp-name">
                                                    {{ p.name }}
                                                </div>

                                                <div class="emp-role">
                                                    {{ p.role }}
                                                </div>
                                            </div>

                                        </td>

                                        <td class="mono-time">
                                            {{ p.clockIn || '—' }}
                                        </td>

                                        <td class="mono-time">
                                            {{ p.clockOut || '—' }}
                                        </td>

                                        <td>
                                            <span class="badge-status" :class="badgeClass(p.status)"
                                                @click="cycleStatus(p)">
                                                {{ p.status.toUpperCase() }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="empty-state" v-else>
                                No punches match this filter.
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Recent Cash Advance -->
                <!-- Recent Cash Advance -->
<div class="col-lg-5">

    <div class="section-title">
        Recent Cash Advance
    </div>

    <div class="cash-advance-list">

        <div
            class="cash-advance-item"
            v-for="ca in recentCashAdvances"
            :key="ca.id"
        >

            <div class="d-flex align-items-center gap-2">

                <div class="avatar-sm">
                    {{ ca.initials }}
                </div>

                <div class="cash-advance-person">
                    <div class="emp-name">
                        {{ ca.name }}
                    </div>

                    <div class="emp-role">
                        {{ ca.role }} · {{ ca.date }}
                    </div>
                </div>

            </div>

            <div class="cash-advance-info">

                <div class="cash-advance-purpose">
                    {{ ca.purpose }}
                </div>

                <div class="cash-advance-amount">
                    {{ peso(ca.amount) }}
                </div>

                <span
                    class="badge-status"
                    :class="caStatusClass(ca.status)"
                >
                    {{ ca.status.toUpperCase() }}
                </span>

            </div>

        </div>

    </div>

</div>


            </div>

        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onBeforeUnmount, ref } from 'vue'
import Chart from 'chart.js/auto'

defineOptions({
    name: 'AdminMain',
})

const props = defineProps({
    title: {
        type: String,
        default: 'Attendance & Payroll',
    },

    todayLabel: {
        type: String,
        default: 'Saturday, August 29',
    },
})

const emit = defineEmits(['toggle-sidebar'])

// ─── State ──────────────────────────────────────────────

const liveClock = ref('--:--:--')

let clockTimer = null

// Dashboard values
const totalEmployees = ref(248)

const totalSalaryPaidThisWeek = ref(1860000)

const totalCashAdvanceThisWeek = ref(86450)

// ─── Punch Log ─────────────────────────────────────────

const punchLog = ref([
    {
        id: 1,
        initials: 'JD',
        name: 'Jonas Diaz',
        role: 'Warehouse',
        clockIn: '08:01 AM',
        clockOut: null,
        status: 'present',
    },

    {
        id: 2,
        initials: 'CS',
        name: 'Carla Santos',
        role: 'Accounting',
        clockIn: '09:14 AM',
        clockOut: null,
        status: 'late',
    },

    {
        id: 3,
        initials: 'RT',
        name: 'Ramon Tan',
        role: 'Logistics',
        clockIn: null,
        clockOut: null,
        status: 'absent',
    },

    {
        id: 4,
        initials: 'PL',
        name: 'Paulo Lim',
        role: 'Customer Care',
        clockIn: '07:52 AM',
        clockOut: '05:03 PM',
        status: 'present',
    },

    {
        id: 5,
        initials: 'NA',
        name: 'Nadia Ang',
        role: 'Marketing',
        clockIn: null,
        clockOut: null,
        status: 'leave',
    },

    {
        id: 6,
        initials: 'EV',
        name: 'Erik Villar',
        role: 'Warehouse',
        clockIn: '08:08 AM',
        clockOut: null,
        status: 'present',
    },
])

// ─── Recent Cash Advances ──────────────────────────────

const recentCashAdvances = ref([
    {
        id: 1,
        initials: 'JD',
        name: 'Jonas Diaz',
        role: 'Warehouse',
        amount: 5000,
        purpose: 'Emergency expense',
        date: 'Aug 29, 2026',
        status: 'approved',
    },

    {
        id: 2,
        initials: 'CS',
        name: 'Carla Santos',
        role: 'Accounting',
        amount: 3500,
        purpose: 'Medical expense',
        date: 'Aug 28, 2026',
        status: 'approved',
    },

    {
        id: 3,
        initials: 'RT',
        name: 'Ramon Tan',
        role: 'Logistics',
        amount: 8000,
        purpose: 'Family expense',
        date: 'Aug 27, 2026',
        status: 'pending',
    },

    {
        id: 4,
        initials: 'PL',
        name: 'Paulo Lim',
        role: 'Customer Care',
        amount: 2500,
        purpose: 'Transportation',
        date: 'Aug 26, 2026',
        status: 'paid',
    },
])

// ─── Five Week Salary Data ─────────────────────────────

const weeklySalary = ref([
    {
        label: 'JULY 27–AUGUST 1',
        totalSalary: 31585,
    },

    {
        label: 'AUGUST 3–8',
        totalSalary: 33085,
    },

    {
        label: 'AUGUST 10–15',
        totalSalary: 36085,
    },

    {
        label: 'AUGUST 17–22',
        totalSalary: 36585,
    },

    {
        label: 'AUGUST 24–29',
        totalSalary: 33585,
    },
])

// ─── Filters ───────────────────────────────────────────

const statusFilters = [
    { key: 'all', label: 'All' },
    { key: 'present', label: 'Present' },
    { key: 'late', label: 'Late' },
    { key: 'absent', label: 'Absent' },
    { key: 'leave', label: 'On leave' },
]

const statusFilter = ref('all')

const payrollRunning = ref(false)
const payrollJustRan = ref(false)

let payrollJustRanTimer = null

// ─── Charts ─────────────────────────────────────────────

const attendanceChart = ref(null)
const salaryChart = ref(null)

const attendanceCanvas = ref(null)
const salaryCanvas = ref(null)

// ─── Computed ───────────────────────────────────────────

const filteredLog = computed(() => {
    return statusFilter.value === 'all'
        ? punchLog.value
        : punchLog.value.filter(
            p => p.status === statusFilter.value
        )
})

const presentCount = computed(() => {
    return punchLog.value.filter(
        p =>
            p.status === 'present' ||
            p.status === 'late'
    ).length
})

const lateCount = computed(() => {
    return punchLog.value.filter(
        p => p.status === 'late'
    ).length
})

const attendanceRate = computed(() => {
    const present = punchLog.value.filter(
        p =>
            p.status === 'present' ||
            p.status === 'late'
    ).length

    return (
        (present / punchLog.value.length) *
        100
    ).toFixed(1)
})

// ─── Methods ────────────────────────────────────────────

function tickClock() {
    liveClock.value = new Date().toLocaleTimeString(
        'en-US',
        {
            hour12: true,
        }
    )
}

function cycleStatus(person) {
    const cycle = [
        'present',
        'late',
        'absent',
        'leave',
    ]

    const currentIndex = cycle.indexOf(
        person.status
    )

    person.status =
        cycle[
        (currentIndex + 1) % cycle.length
        ]
}

function badgeClass(status) {
    return {
        present: 'badge-present',
        late: 'badge-late',
        absent: 'badge-absent',
        leave: 'badge-leave',
    }[status]
}

function caStatusClass(status) {
    return {
        approved: 'badge-present',
        paid: 'badge-present',
        pending: 'badge-late',
        rejected: 'badge-absent',
    }[status]
}

function peso(n) {
    return (
        '₱' +
        n.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })
    )
}

function runPayroll() {
    if (payrollRunning.value) return

    payrollRunning.value = true
    payrollJustRan.value = false

    setTimeout(() => {
        payrollRunning.value = false
        payrollJustRan.value = true

        payrollJustRanTimer = setTimeout(() => {
            payrollJustRan.value = false
        }, 2500)
    }, 1200)
}

function exportCsv() {
    const rows = [
        [
            'Employee',
            'Role',
            'Clock In',
            'Clock Out',
            'Status',
        ],
    ]

    punchLog.value.forEach(p => {
        rows.push([
            p.name,
            p.role,
            p.clockIn || '',
            p.clockOut || '',
            p.status,
        ])
    })

    const csv = rows
        .map(row =>
            row
                .map(cell => `"${cell}"`)
                .join(',')
        )
        .join('\n')

    const blob = new Blob(
        [csv],
        {
            type: 'text/csv',
        }
    )

    const url =
        URL.createObjectURL(blob)

    const a =
        document.createElement('a')

    a.href = url
    a.download = 'punch-log.csv'
    a.click()

    URL.revokeObjectURL(url)
}

// ─── Charts ─────────────────────────────────────────────

function renderCharts() {
    const style =
        getComputedStyle(
            document.documentElement
        )

    const ink =
        style
            .getPropertyValue('--ink-2')
            .trim() ||
        '#28395E'

    const gold =
        style
            .getPropertyValue('--gold')
            .trim() ||
        '#C79A3D'

    const green =
        style
            .getPropertyValue('--green')
            .trim() ||
        '#2F8F5B'

    const slate =
        style
            .getPropertyValue('--slate')
            .trim() ||
        '#6B7280'

    const line =
        style
            .getPropertyValue('--line')
            .trim() ||
        '#DCD8CB'

    Chart.defaults.font.family =
        "'Inter', sans-serif"

    Chart.defaults.color = slate

    // ─────────────────────────────────────────────
    // Weekly Attendance Chart
    // ─────────────────────────────────────────────

    if (attendanceCanvas.value) {
        attendanceChart.value =
            new Chart(
                attendanceCanvas.value,
                {
                    type: 'line',

                    data: {
                        labels: [
                            'Mon',
                            'Tue',
                            'Wed',
                            'Thu',
                            'Fri',
                            'Sat',
                        ],

                        datasets: [
                            {
                                label: 'Present',

                                data: [
                                    230,
                                    227,
                                    233,
                                    221,
                                    218,
                                    190,
                                ],

                                borderColor: ink,

                                backgroundColor:
                                    'rgba(40,57,94,0.08)',

                                fill: true,

                                tension: 0.35,

                                borderWidth: 2.5,

                                pointRadius: 3,
                            },

                            {
                                label: 'On leave',

                                data: [
                                    10,
                                    12,
                                    8,
                                    15,
                                    18,
                                    9,
                                ],

                                borderColor: gold,

                                backgroundColor:
                                    'rgba(199,154,61,0.08)',

                                fill: true,

                                tension: 0.35,

                                borderWidth: 2.5,

                                pointRadius: 3,
                            },
                        ],
                    },

                    options: {
                        responsive: true,

                        plugins: {
                            legend: {
                                position: 'bottom',

                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    padding: 16,
                                },
                            },
                        },

                        scales: {
                            y: {
                                beginAtZero: true,

                                grid: {
                                    color: line,
                                },

                                border: {
                                    display: false,
                                },
                            },

                            x: {
                                grid: {
                                    display: false,
                                },

                                border: {
                                    display: false,
                                },
                            },
                        },
                    },
                }
            )
    }
    // ─────────────────────────────────────────────
    // Five Week Total Salary Paid - Doughnut
    // ─────────────────────────────────────────────

    if (salaryCanvas.value) {
        salaryChart.value = new Chart(
            salaryCanvas.value,
            {
                type: 'doughnut',

                data: {
                    labels: weeklySalary.value.map(
                        week => week.label
                    ),

                    datasets: [
                        {
                            data: weeklySalary.value.map(
                                week => week.totalSalary
                            ),

                            backgroundColor: [
                                ink,
                                '#52678F',
                                '#7C8BAA',
                                gold,
                                green,
                            ],

                            borderColor: '#FBFAF6',
                            borderWidth: 3,
                        },
                    ],
                },

                options: {
                    responsive: true,

                    maintainAspectRatio: false,

                    cutout: '12%',

                    plugins: {
                        legend: {
                            position: 'bottom',

                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 12,

                                font: {
                                    size: 10,
                                },
                            },
                        },

                        tooltip: {
                            callbacks: {
                                label: context => {
                                    const value = context.raw

                                    return (
                                        ' ' +
                                        peso(value)
                                    )
                                },
                            },
                        },
                    },
                },
            }
        )
    }

}

// ─── Lifecycle ──────────────────────────────────────────

onMounted(() => {
    tickClock()

    clockTimer =
        setInterval(
            tickClock,
            1000
        )

    nextTick(() => {
        renderCharts()
    })
})

onBeforeUnmount(() => {
    clearInterval(clockTimer)

    clearTimeout(
        payrollJustRanTimer
    )

    attendanceChart.value?.destroy()

    salaryChart.value?.destroy()
})
</script>

<style scoped>
.main {
    flex: 1;
    min-width: 0;
}

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
    letter-spacing: .03em;
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

.content {
    padding: 1.75rem;
}

.font-mono {
    font-family: 'IBM Plex Mono', monospace;
}

/* Stat Cards */

.punch-card {
    background: var(--paper-2, #FBFAF6);
    border: 1px solid var(--line, #DCD8CB);
    border-radius: 10px;
    position: relative;
    padding: 1.25rem 1.3rem 1.1rem;
    overflow: visible;
    min-height: 160px;
}

.punch-card::before {
    content: "";
    position: absolute;
    top: -1px;
    left: 14px;
    right: 14px;
    height: 1px;
    background-image: radial-gradient(circle,
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

.stat-value--sm {
    font-size: 1.55rem;
    letter-spacing: -.02em;
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

/* Panels */

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

/* Table */

.table-ledger {
    margin-bottom: 0;
    width: 100%;
    border-collapse: collapse;
}

.table-ledger thead th {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .09em;
    color: var(--slate, #6B7280);
    border-bottom: 1px solid var(--line, #DCD8CB);
    font-weight: 600;
    padding-bottom: .6rem;
    background: transparent;
    text-align: left;
}

.table-ledger tbody td {
    padding: .7rem .5rem;
    border-bottom: 1px dashed var(--line, #DCD8CB);
    vertical-align: middle;
    font-size: .87rem;
}

.table-ledger tbody tr:last-child td {
    border-bottom: none;
}

.mono-time {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .83rem;
    color: var(--ink-2, #28395E);
}

.emp-name {
    font-weight: 600;
}

.emp-role {
    font-size: .74rem;
    color: var(--slate, #6B7280);
}

.badge-status {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .68rem;
    font-weight: 600;
    padding: .3rem .55rem;
    border-radius: 5px;
    letter-spacing: .03em;
    cursor: pointer;
    border: none;
    display: inline-block;
}

.badge-present {
    background: var(--green-bg, #E5F2EA);
    color: var(--green, #2F8F5B);
}

.badge-late {
    background: var(--amber-bg, #F6EEDB);
    color: var(--gold-dark, #9C7726);
}

.badge-absent {
    background: var(--red-bg, #F7E9E6);
    color: var(--red, #C24D3B);
}

.badge-leave {
    background: #EAEBF0;
    color: var(--slate, #6B7280);
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

.cash-advance-list {
    border-top: 1px solid var(--line, #DCD8CB);
}

.cash-advance-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: .9rem .2rem;
    border-bottom: 1px dashed var(--line, #DCD8CB);
}

.cash-advance-item:last-child {
    border-bottom: none;
}

.cash-advance-person {
    min-width: 0;
}

.cash-advance-info {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: .75rem;
    text-align: right;
}

.cash-advance-purpose {
    font-size: .74rem;
    color: var(--slate, #6B7280);
}

.cash-advance-amount {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .82rem;
    font-weight: 600;
    color: var(--ink, #1C2B4A);
    white-space: nowrap;
}

@media (max-width: 576px) {
    .cash-advance-item {
        align-items: flex-start;
    }

    .cash-advance-info {
        flex-direction: column;
        align-items: flex-end;
        gap: .3rem;
    }

    .cash-advance-purpose {
        display: none;
    }
}

.ca-date {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .68rem;
    color: var(--slate, #6B7280);
    white-space: nowrap;
}

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
}

/* Buttons */

.btn {
    border-radius: 6px;
    padding: .45rem .9rem;
    border: 1px solid transparent;
    cursor: pointer;
}

.btn-ink {
    background: var(--ink, #1C2B4A);
    color: #F3DFA6;
    border-color: var(--ink, #1C2B4A);
    font-size: .85rem;
    font-weight: 600;
}

.btn-ink:hover {
    background: var(--ink-2, #28395E);
    color: #fff;
}

.btn-ink:disabled {
    opacity: .6;
    cursor: default;
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

.empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: var(--slate, #6B7280);
    font-size: .85rem;
}

/* Layout utilities */

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

.flex-column {
    flex-direction: column;
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

.mb-2 {
    margin-bottom: .5rem;
}

.mb-3 {
    margin-bottom: 1rem;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.h-100 {
    height: 100%;
}

.text-success {
    color: var(--green, #2F8F5B);
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

.table-responsive {
    overflow-x: auto;
}

/* Salary chart needs enough height */
.salary-chart-container {
    position: relative;
    height: 240px;
}

@media (min-width: 992px) {
    .col-lg-3 {
        width: 25%;
    }

    .col-lg-5 {
        width: 41.6667%;
    }

    .col-lg-7 {
        width: 58.3333%;
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

    .cash-advance-head {
        align-items: flex-start;
    }

    .stat-value--sm {
        font-size: 1.35rem;
    }
}

.salary-chart-wrapper {
    position: relative;
    width: 100%;
    height: 260px;
    max-height: 260px;
}
</style>