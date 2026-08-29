<template>
    <div class="main">

        <!-- Topbar -->
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
                        Attendance
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

            <!-- Summary -->
            <div class="row g-3 mb-4">

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
                            August 29, 2026
                        </div>

                        <div class="stat-value">
                            {{ totalEmployees }}
                        </div>

                        <div class="stat-delta stat-delta--slate">
                            Active employees
                        </div>

                    </div>

                </div>


                <!-- Present -->
                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp green">
                            IN
                        </div>

                        <div class="stat-label">
                            Present Today
                        </div>

                        <div class="stat-period">
                            August 29, 2026
                        </div>

                        <div class="stat-value">
                            {{ presentCount }}
                        </div>

                        <div class="stat-delta text-success">
                            {{ attendanceRate }}% attendance rate
                        </div>

                    </div>

                </div>


                <!-- Late -->
                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp gold">
                            LATE
                        </div>

                        <div class="stat-label">
                            Late Today
                        </div>

                        <div class="stat-period">
                            August 29, 2026
                        </div>

                        <div class="stat-value">
                            {{ lateCount }}
                        </div>

                        <div class="stat-delta stat-delta--gold">
                            Requires attention
                        </div>

                    </div>

                </div>


                <!-- Absent -->
                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp red">
                            OUT
                        </div>

                        <div class="stat-label">
                            Absent / On Leave
                        </div>

                        <div class="stat-period">
                            August 29, 2026
                        </div>

                        <div class="stat-value">
                            {{ absentLeaveCount }}
                        </div>

                        <div class="stat-delta stat-delta--slate">
                            {{ absentCount }} absent · {{ leaveCount }} on leave
                        </div>

                    </div>

                </div>

            </div>


            <!-- Weekly Attendance -->
            <div class="row g-3 mb-4">

                <div class="col-lg-8">

                    <div class="panel attendance-chart-panel">

                        <div class="d-flex justify-content-between align-items-start mb-2">

                            <div>
                                <div class="panel-title">
                                    Weekly attendance
                                </div>

                                <div class="panel-sub">
                                    Attendance movement for August 24–29, 2026
                                </div>
                            </div>

                            <span class="chip">
                                This week
                            </span>

                        </div>

                        <div class="attendance-chart-wrapper">
                            <canvas ref="attendanceCanvas"></canvas>
                        </div>

                    </div>

                </div>


                <!-- Attendance Summary -->
                <div class="col-lg-4">

                    <div class="panel attendance-summary-panel">

                        <div class="panel-title">
                            Attendance summary
                        </div>

                        <div class="panel-sub mb-3">
                            August 29, 2026
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
                                    <span class="summary-dot red"></span>
                                    Absent
                                </div>

                                <div class="summary-value">
                                    {{ absentCount }}
                                </div>

                            </div>


                            <div class="summary-row">

                                <div class="summary-label">
                                    <span class="summary-dot slate"></span>
                                    On leave
                                </div>

                                <div class="summary-value">
                                    {{ leaveCount }}
                                </div>

                            </div>

                        </div>


                        <div class="attendance-rate">

                            <div>
                                <div class="rate-label">
                                    ATTENDANCE RATE
                                </div>

                                <div class="rate-value">
                                    {{ attendanceRate }}%
                                </div>
                            </div>

                            <div class="rate-ring">
                                {{ attendanceRate }}%
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="panel">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                    <div>

                        <div class="section-title mb-0">
                            Attendance for selected date
                        </div>

                        <div class="panel-sub">
                            View who was present, late, absent, or on leave
                        </div>

                    </div>


                    <div class="attendance-controls d-flex gap-2 flex-wrap">

                        <!-- Date Picker -->
                        <div class="date-picker">

                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>

                            <input type="date" v-model="selectedDate" aria-label="Select attendance date" />

                        </div>

                    </div>

                </div>

                <div class="selected-date-banner">

                    <div>
                        <div class="selected-date-label">
                            ATTENDANCE DATE
                        </div>

                        <div class="selected-date-value">
                            {{ formattedSelectedDate }}
                        </div>
                    </div>

                    <div class="selected-date-count">
                        {{ filteredLog.length }} records
                    </div>

                </div>

                <div class="d-flex gap-2 flex-wrap mb-3">

                    <button v-for="f in statusFilters" :key="f.key" class="filter-pill" :class="{
                        active: statusFilter === f.key
                    }" @click="statusFilter = f.key">
                        {{ f.label }}
                    </button>

                </div>

                <div class="table-responsive">

                    <table class="table-ledger attendance-table" v-if="filteredLog.length">

                        <thead>

                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Clock in</th>
                                <th>Clock out</th>
                                <th>Total hours</th>
                                <th>Status</th>
                            </tr>

                        </thead>


                        <tbody>

                            <tr v-for="p in filteredLog" :key="p.id">

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


                                <td class="mono-time">
                                    {{ p.clockIn || '—' }}
                                </td>


                                <td class="mono-time">
                                    {{ p.clockOut || '—' }}
                                </td>


                                <td class="mono-time">
                                    {{ totalHours(p) }}
                                </td>


                                <td>

                                    <span class="badge-status" :class="badgeClass(p.status)" @click="cycleStatus(p)">
                                        {{ p.status.toUpperCase() }}
                                    </span>

                                </td>

                            </tr>

                        </tbody>

                    </table>


                    <div class="empty-state" v-else>
                        No attendance records match this date and filter.
                    </div>

                </div>
            </div>


        </div>

    </div>
</template>


<script setup>

import {
    computed,
    nextTick,
    onMounted,
    onBeforeUnmount,
    ref
} from 'vue'

import Chart from 'chart.js/auto'


defineOptions({
    name: 'AttendancePage',
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

const selectedDate = ref('2026-08-29')

const formattedSelectedDate = computed(() => {

    if (!selectedDate.value) {
        return 'Select a date'
    }

    const date = new Date(
        `${selectedDate.value}T00:00:00`
    )

    return date.toLocaleDateString(
        'en-US',
        {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        }
    )


})


// ─────────────────────────────────────────────
// Dashboard Data
// ─────────────────────────────────────────────

const totalEmployees = ref(248)

const punchLog = ref([
    {
        id: 1,
        date: '2026-08-29',
        initials: 'JD',
        name: 'Jonas Diaz',
        role: 'Warehouse',
        clockIn: '08:01 AM',
        clockOut: null,
        status: 'present'
    },

    {
        id: 2,
        date: '2026-08-29',
        initials: 'CS',
        name: 'Carla Santos',
        role: 'Accounting',
        clockIn: '09:14 AM',
        clockOut: null,
        status: 'late'
    },

    {
        id: 3,
        date: '2026-08-29',
        initials: 'RT',
        name: 'Ramon Tan',
        role: 'Logistics',
        clockIn: null,
        clockOut: null,
        status: 'absent'
    },

    {
        id: 4,
        date: '2026-08-29',
        initials: 'PL',
        name: 'Paulo Lim',
        role: 'Customer Care',
        clockIn: '07:52 AM',
        clockOut: '05:03 PM',
        status: 'present'
    },

    {
        id: 5,
        date: '2026-08-29',
        initials: 'NA',
        name: 'Nadia Ang',
        role: 'Marketing',
        clockIn: null,
        clockOut: null,
        status: 'leave'
    },

    {
        id: 6,
        date: '2026-08-29',
        initials: 'EV',
        name: 'Erik Villar',
        role: 'Warehouse',
        clockIn: '08:08 AM',
        clockOut: null,
        status: 'present'
    },

    // Example records for another date
    {
        id: 7,
        date: '2026-08-28',
        initials: 'JD',
        name: 'Jonas Diaz',
        role: 'Warehouse',
        clockIn: '08:12 AM',
        clockOut: '05:01 PM',
        status: 'late'
    },

    {
        id: 8,
        date: '2026-08-28',
        initials: 'CS',
        name: 'Carla Santos',
        role: 'Accounting',
        clockIn: '08:03 AM',
        clockOut: '05:10 PM',
        status: 'present'
    },

    {
        id: 9,
        date: '2026-08-28',
        initials: 'RT',
        name: 'Ramon Tan',
        role: 'Logistics',
        clockIn: null,
        clockOut: null,
        status: 'absent'
    }
])



// ─────────────────────────────────────────────
// Filters
// ─────────────────────────────────────────────

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
        key: 'absent',
        label: 'Absent'
    },

    {
        key: 'leave',
        label: 'On leave'
    }

]


const statusFilter = ref('all')


const filteredLog = computed(() => {

    let records = punchLog.value.filter(
        p => p.date === selectedDate.value
    )

    if (statusFilter.value !== 'all') {
        records = records.filter(
            p => p.status === statusFilter.value
        )
    }

    return records

})


// ─────────────────────────────────────────────
// Statistics
// ─────────────────────────────────────────────

const presentCount = computed(() => {

    return punchLog.value.filter(
        p =>
            p.status === 'present' ||
            p.status === 'late'
    ).length

})


const lateCount = computed(() => {

    return punchLog.value.filter(
        p =>
            p.status === 'late'
    ).length

})


const absentCount = computed(() => {

    return punchLog.value.filter(
        p =>
            p.status === 'absent'
    ).length

})


const leaveCount = computed(() => {

    return punchLog.value.filter(
        p =>
            p.status === 'leave'
    ).length

})


const absentLeaveCount = computed(() => {

    return (
        absentCount.value +
        leaveCount.value
    )

})


const attendanceRate = computed(() => {

    const present =
        punchLog.value.filter(
            p =>
                p.status === 'present' ||
                p.status === 'late'
        ).length

    if (!punchLog.value.length) {
        return '0.0'
    }

    return (
        present /
        punchLog.value.length *
        100
    ).toFixed(1)

})


// ─────────────────────────────────────────────
// Status
// ─────────────────────────────────────────────

function cycleStatus(person) {

    const cycle = [
        'present',
        'late',
        'absent',
        'leave'
    ]

    const index =
        cycle.indexOf(person.status)

    person.status =
        cycle[
        (index + 1) %
        cycle.length
        ]

}


function badgeClass(status) {

    return {

        present: 'badge-present',

        late: 'badge-late',

        absent: 'badge-absent',

        leave: 'badge-leave'

    }[status]

}


// ─────────────────────────────────────────────
// Hours
// ─────────────────────────────────────────────

function totalHours(person) {

    if (
        !person.clockIn ||
        !person.clockOut
    ) {
        return '—'
    }

    const parseTime = time => {

        const [
            value,
            modifier
        ] = time.split(' ')

        let [
            hours,
            minutes
        ] = value.split(':')
            .map(Number)

        if (
            modifier === 'PM' &&
            hours !== 12
        ) {
            hours += 12
        }

        if (
            modifier === 'AM' &&
            hours === 12
        ) {
            hours = 0
        }

        return (
            hours * 60 +
            minutes
        )

    }

    const start =
        parseTime(person.clockIn)

    const end =
        parseTime(person.clockOut)

    const minutes =
        end - start

    if (minutes <= 0) {
        return '—'
    }

    const hours =
        Math.floor(minutes / 60)

    const mins =
        minutes % 60

    return `${hours}h ${mins
        .toString()
        .padStart(2, '0')}m`

}


// ─────────────────────────────────────────────
// Chart
// ─────────────────────────────────────────────

const attendanceCanvas = ref(null)

const attendanceChart = ref(null)


function renderChart() {

    if (!attendanceCanvas.value) {
        return
    }


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


    const line =
        style
            .getPropertyValue('--line')
            .trim() ||
        '#DCD8CB'


    const slate =
        style
            .getPropertyValue('--slate')
            .trim() ||
        '#6B7280'


    Chart.defaults.font.family =
        "'Inter', sans-serif"

    Chart.defaults.color =
        slate


    attendanceChart.value =
        new Chart(
            attendanceCanvas.value,
            {

                type: 'line',

                data: {

                    labels: [
                        'Mon 24',
                        'Tue 25',
                        'Wed 26',
                        'Thu 27',
                        'Fri 28',
                        'Sat 29'
                    ],

                    datasets: [

                        {
                            label: 'Present',

                            data: [
                                232,
                                235,
                                229,
                                238,
                                234,
                                236
                            ],

                            borderColor: ink,

                            backgroundColor:
                                'rgba(40,57,94,0.08)',

                            fill: true,

                            tension: .35,

                            borderWidth: 2.5,

                            pointRadius: 4,

                            pointBackgroundColor:
                                ink
                        },

                        {
                            label: 'Late',

                            data: [
                                7,
                                9,
                                12,
                                6,
                                10,
                                4
                            ],

                            borderColor: gold,

                            backgroundColor:
                                'rgba(199,154,61,0.06)',

                            fill: true,

                            tension: .35,

                            borderWidth: 2,

                            pointRadius: 3,

                            pointBackgroundColor:
                                gold
                        }

                    ]

                },


                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    interaction: {
                        mode: 'index',
                        intersect: false
                    },

                    plugins: {

                        legend: {

                            position: 'bottom',

                            labels: {

                                usePointStyle: true,

                                boxWidth: 8,

                                padding: 16

                            }

                        }

                    },


                    scales: {

                        y: {

                            beginAtZero: true,

                            grid: {
                                color: line
                            },

                            border: {
                                display: false
                            }

                        },

                        x: {

                            grid: {
                                display: false
                            },

                            border: {
                                display: false
                            }

                        }

                    }

                }

            }
        )

}


// ─────────────────────────────────────────────
// Export
// ─────────────────────────────────────────────

function exportCsv() {

    const rows = [

        [
            'Employee',
            'Department',
            'Clock In',
            'Clock Out',
            'Status'
        ]

    ]


    punchLog.value.forEach(p => {

        rows.push([

            p.name,

            p.role,

            p.clockIn || '',

            p.clockOut || '',

            p.status

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
        'attendance-august-29-2026.csv'

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


    nextTick(() => {

        renderChart()

    })

})


onBeforeUnmount(() => {

    clearInterval(
        clockTimer
    )

    attendanceChart.value?.destroy()

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

    font-size: .6rem;

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


.stamp.red {
    color: var(--red, #C24D3B);
    border-color: var(--red, #C24D3B);
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
   Attendance Chart
───────────────────────────────────────────── */

.attendance-chart-panel {
    height: 100%;
}


.attendance-chart-wrapper {
    position: relative;

    width: 100%;

    height: 280px;

    max-height: 280px;
}


/* ─────────────────────────────────────────────
   Summary
───────────────────────────────────────────── */

.attendance-summary-panel {
    height: 100%;
}


.summary-list {
    border-top: 1px solid var(--line, #DCD8CB);
}


.summary-row {
    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: .85rem .1rem;

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


.summary-dot.green {
    background: var(--green, #2F8F5B);
}


.summary-dot.gold {
    background: var(--gold, #C79A3D);
}


.summary-dot.red {
    background: var(--red, #C24D3B);
}


.summary-dot.slate {
    background: var(--slate, #6B7280);
}


.attendance-rate {
    display: flex;

    align-items: center;

    justify-content: space-between;

    padding-top: 1.1rem;

    margin-top: .4rem;
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

    border: 5px solid var(--green, #2F8F5B);

    display: flex;

    align-items: center;

    justify-content: center;

    font-family: 'IBM Plex Mono', monospace;

    font-size: .7rem;

    font-weight: 600;

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


.mono-time {
    font-family: 'IBM Plex Mono', monospace;

    font-size: .78rem;

    color: var(--ink-2, #28395E);

    white-space: nowrap;
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

    background:
        var(--amber-bg, #F6EEDB);

    color:
        var(--gold-dark, #9C7726);

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

    font-size: .68rem;

    font-weight: 600;

    padding: .3rem .55rem;

    border-radius: 5px;

    letter-spacing: .03em;

    cursor: pointer;

    display: inline-block;

    border: none;
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

    background:
        var(--amber-bg, #F6EEDB);

    color:
        var(--gold-dark, #9C7726);
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
   Layout
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


.mb-2 {
    margin-bottom: .5rem;
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

}


@media (max-width: 576px) {

    .content {
        padding: 1rem;
    }

    .topbar {
        padding: 1rem;
    }

    .attendance-chart-wrapper {
        height: 230px;
        max-height: 230px;
    }

    .rate-ring {
        width: 54px;
        height: 54px;
    }

}

/* ─────────────────────────────────────────────
Attendance Date Picker
───────────────────────────────────────────── */

.attendance-controls {
    align-items: center;
}

.date-picker {
    display: flex;
    align-items: center;
    gap: .5rem;

    background: var(--paper-2, #FBFAF6);

    border: 1px solid var(--line, #DCD8CB);

    border-radius: 7px;

    padding: .35rem .65rem;

    color: var(--ink-2, #28395E);


}

.date-picker input {
    border: none;

    outline: none;

    background: transparent;

    color: var(--ink-2, #28395E);

    font-family: 'IBM Plex Mono', monospace;

    font-size: .75rem;

    font-weight: 600;

    cursor: pointer;


}

.date-picker input::-webkit-calendar-picker-indicator {
    cursor: pointer;

    opacity: .7;


}

.selected-date-banner {
    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 1rem;

    background: var(--paper, #F2F1EA);

    border: 1px dashed var(--line, #DCD8CB);

    border-radius: 8px;

    padding: .75rem .9rem;

    margin-bottom: 1rem;
}

.selected-date-label {
    font-family: 'IBM Plex Mono', monospace;

    font-size: .6rem;

    letter-spacing: .1em;

    color: var(--slate, #6B7280);

    font-weight: 600;


}

.selected-date-value {
    font-family: 'Fraunces', serif;

    font-size: .95rem;

    font-weight: 600;

    color: var(--ink, #1C2B4A);

    margin-top: .1rem;


}

.selected-date-count {
    font-family: 'IBM Plex Mono', monospace;

    font-size: .7rem;

    color: var(--slate, #6B7280);

    white-space: nowrap;


}

@media (max-width: 576px) {

    .selected-date-banner {
        align-items: flex-start;

        flex-direction: column;
    }

    .date-picker {
        width: 100%;
    }

    .date-picker input {
        width: 100%;
    }


}
</style>
