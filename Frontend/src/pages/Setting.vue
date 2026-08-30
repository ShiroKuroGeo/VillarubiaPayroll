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
                        System Settings
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
            </div>
        </div>

        <div class="content">

            <div class="row g-3">

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp gold">
                            CA
                        </div>

                        <div class="stat-label">
                            Cash Advance Limit
                        </div>

                        <div class="stat-period">
                            Per employee
                        </div>

                        <div class="stat-value stat-value-money">
                            {{ formatCurrency(savedSettings.cashAdvanceLimit) }}
                        </div>

                        <div class="stat-delta stat-delta--gold">
                            Max outstanding balance
                        </div>

                    </div>

                </div>

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp blue">
                            OT
                        </div>

                        <div class="stat-label">
                            Overtime Rate
                        </div>

                        <div class="stat-period">
                            Per hour
                        </div>

                        <div class="stat-value stat-value-money">
                            {{ formatCurrency(savedSettings.overtimeRatePerHour) }}
                        </div>

                        <div class="stat-delta stat-delta--blue">
                            {{ savedSettings.overtimeMultiplier }}x base rate
                        </div>

                    </div>

                </div>

                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp green">
                            HRS
                        </div>

                        <div class="stat-label">
                            Standard Work Hours
                        </div>

                        <div class="stat-period">
                            Per day
                        </div>

                        <div class="stat-value">
                            {{ savedSettings.standardWorkHours }}
                        </div>

                        <div class="stat-delta stat-delta--slate">
                            {{ savedSettings.workDaysPerWeek }} days / week
                        </div>

                    </div>

                </div>


                <div class="col-6 col-lg-3">

                    <div class="punch-card">

                        <div class="stamp red">
                            LATE
                        </div>

                        <div class="stat-label">
                            Grace Period
                        </div>

                        <div class="stat-period">
                            Before marked late
                        </div>

                        <div class="stat-value">
                            {{ savedSettings.gracePeriodMinutes }}m
                        </div>

                        <div class="stat-delta" style="color: var(--red, #C24D3B);">
                            {{ formatCurrency(savedSettings.lateDeductionPerMinute) }} / min after
                        </div>

                    </div>

                </div>

            </div>

            <div class="panel my-3">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <div class="panel-title">
                            Payroll &amp; cash advance
                        </div>
                        <div class="panel-sub">
                            Controls used when computing net pay and employee cash advances
                        </div>
                    </div>
                    <span class="chip">
                        Payroll
                    </span>
                </div>


                <div class="settings-list">


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Cash Advance Limit
                            </div>

                            <div class="setting-desc">
                                Maximum outstanding cash advance balance allowed per employee at any time.
                            </div>

                        </div>

                        <div class="setting-control">

                            <div class="input-money has-prefix">
                                <span>₱</span>
                                <input v-model.number="form.cashAdvanceLimit" type="number" min="0" step="100"
                                    class="form-control" />
                            </div>

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Cash Advance Repayment (per cutoff)
                            </div>

                            <div class="setting-desc">
                                Percentage of net pay automatically deducted each payroll cutoff to repay an active
                                cash advance.
                            </div>

                        </div>

                        <div class="setting-control">

                            <div class="input-money has-suffix">
                                <input v-model.number="form.cashAdvanceRepaymentPercent" type="number" min="0" max="100"
                                    step="1" class="form-control" />
                                <span class="suffix">%</span>
                            </div>

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Allow Multiple Active Advances
                            </div>

                            <div class="setting-desc">
                                When off, an employee must fully settle an existing cash advance before requesting a
                                new one.
                            </div>

                        </div>

                        <div class="setting-control">

                            <button type="button" class="toggle-switch" :class="{ on: form.allowMultipleAdvances }"
                                @click="form.allowMultipleAdvances = !form.allowMultipleAdvances">
                                <span class="toggle-knob"></span>
                            </button>

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Pay Period
                            </div>

                            <div class="setting-desc">
                                How often employees are paid. Affects payroll run frequency.
                            </div>

                        </div>

                        <div class="setting-control">

                            <select v-model="form.payPeriod" class="form-control">
                                <option value="weekly">Weekly</option>
                                <option value="biweekly">Bi-weekly (every 15 days)</option>
                                <option value="monthly">Monthly</option>
                            </select>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 OVERTIME & ATTENDANCE RULES
            ====================================================== -->

            <div class="panel mb-3">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

                    <div>

                        <div class="panel-title">
                            Overtime &amp; attendance rules
                        </div>

                        <div class="panel-sub">
                            Work schedule, lateness, and overtime computation rules
                        </div>

                    </div>


                    <span class="chip">
                        Attendance
                    </span>

                </div>


                <div class="settings-list">


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Overtime Rate (per hour)
                            </div>

                            <div class="setting-desc">
                                Flat peso amount paid for every hour of approved overtime work.
                            </div>

                        </div>

                        <div class="setting-control">

                            <div class="input-money has-prefix">
                                <span>₱</span>
                                <input v-model.number="form.overtimeRatePerHour" type="number" min="0" step="0.25"
                                    class="form-control" />
                            </div>

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Overtime Multiplier
                            </div>

                            <div class="setting-desc">
                                Multiplier applied to the base overtime rate on rest days or holidays.
                            </div>

                        </div>

                        <div class="setting-control">

                            <div class="input-money has-suffix">
                                <input v-model.number="form.overtimeMultiplier" type="number" min="1" step="0.1"
                                    class="form-control" />
                                <span class="suffix">x</span>
                            </div>

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Standard Work Hours
                            </div>

                            <div class="setting-desc">
                                Expected number of work hours per day before overtime kicks in.
                            </div>

                        </div>

                        <div class="setting-control">

                            <input v-model.number="form.standardWorkHours" type="number" min="1" max="24" step="0.5"
                                class="form-control" />

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Work Days per Week
                            </div>

                            <div class="setting-desc">
                                Number of scheduled work days used for weekly and monthly attendance calculations.
                            </div>

                        </div>

                        <div class="setting-control">

                            <input v-model.number="form.workDaysPerWeek" type="number" min="1" max="7" step="1"
                                class="form-control" />

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Work Start Time
                            </div>

                            <div class="setting-desc">
                                Official shift start time used to determine lateness.
                            </div>

                        </div>

                        <div class="setting-control">

                            <input v-model="form.workStartTime" type="time" class="form-control" />

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Grace Period
                            </div>

                            <div class="setting-desc">
                                Minutes after the work start time an employee may still clock in without being marked
                                late.
                            </div>

                        </div>

                        <div class="setting-control">

                            <div class="input-money has-suffix">
                                <input v-model.number="form.gracePeriodMinutes" type="number" min="0" step="1"
                                    class="form-control" />
                                <span class="suffix">min</span>
                            </div>

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Late Deduction (per minute)
                            </div>

                            <div class="setting-desc">
                                Amount deducted from pay for every minute an employee clocks in beyond the grace
                                period.
                            </div>

                        </div>

                        <div class="setting-control">

                            <div class="input-money has-prefix">
                                <span>₱</span>
                                <input v-model.number="form.lateDeductionPerMinute" type="number" min="0" step="0.05"
                                    class="form-control" />
                            </div>

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Auto-approve Overtime
                            </div>

                            <div class="setting-desc">
                                When on, logged overtime hours are approved automatically instead of waiting on a
                                supervisor.
                            </div>

                        </div>

                        <div class="setting-control">

                            <button type="button" class="toggle-switch" :class="{ on: form.autoApproveOvertime }"
                                @click="form.autoApproveOvertime = !form.autoApproveOvertime">
                                <span class="toggle-knob"></span>
                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 GOVERNMENT CONTRIBUTIONS & TAX
            ====================================================== -->

            <div class="panel mb-3">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

                    <div>

                        <div class="panel-title">
                            Government contributions &amp; tax
                        </div>

                        <div class="panel-sub">
                            Statutory deduction rates applied to every payroll run
                        </div>

                    </div>


                    <span class="chip">
                        Deductions
                    </span>

                </div>


                <div class="settings-list">


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                SSS Contribution
                            </div>

                            <div class="setting-desc">
                                Employee share of the Social Security System contribution.
                            </div>

                        </div>

                        <div class="setting-control">

                            <div class="input-money has-suffix">
                                <input v-model.number="form.sssContributionPercent" type="number" min="0" max="100"
                                    step="0.1" class="form-control" />
                                <span class="suffix">%</span>
                            </div>

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                PhilHealth Contribution
                            </div>

                            <div class="setting-desc">
                                Employee share of the PhilHealth premium contribution.
                            </div>

                        </div>

                        <div class="setting-control">

                            <div class="input-money has-suffix">
                                <input v-model.number="form.philhealthContributionPercent" type="number" min="0"
                                    max="100" step="0.1" class="form-control" />
                                <span class="suffix">%</span>
                            </div>

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Pag-IBIG Contribution
                            </div>

                            <div class="setting-desc">
                                Fixed monthly employee contribution to Pag-IBIG Fund.
                            </div>

                        </div>

                        <div class="setting-control">

                            <div class="input-money has-prefix">
                                <span>₱</span>
                                <input v-model.number="form.pagibigContributionAmount" type="number" min="0" step="10"
                                    class="form-control" />
                            </div>

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Withholding Tax Rate
                            </div>

                            <div class="setting-desc">
                                Default withholding tax rate applied before other statutory deductions are
                                subtracted.
                            </div>

                        </div>

                        <div class="setting-control">

                            <div class="input-money has-suffix">
                                <input v-model.number="form.withholdingTaxPercent" type="number" min="0" max="100"
                                    step="0.1" class="form-control" />
                                <span class="suffix">%</span>
                            </div>

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                13th Month Pay
                            </div>

                            <div class="setting-desc">
                                Automatically accrue 1/12 of basic salary each month toward the employee's 13th month
                                pay.
                            </div>

                        </div>

                        <div class="setting-control">

                            <button type="button" class="toggle-switch" :class="{ on: form.thirteenthMonthEnabled }"
                                @click="form.thirteenthMonthEnabled = !form.thirteenthMonthEnabled">
                                <span class="toggle-knob"></span>
                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 COMPANY INFO
            ====================================================== -->

            <div class="panel mb-3">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

                    <div>

                        <div class="panel-title">
                            Company info
                        </div>

                        <div class="panel-sub">
                            General details used across payslips and reports
                        </div>

                    </div>


                    <span class="chip">
                        General
                    </span>

                </div>


                <div class="settings-list">


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Company Name
                            </div>

                            <div class="setting-desc">
                                Displayed on payslips, reports, and exported files.
                            </div>

                        </div>

                        <div class="setting-control">

                            <input v-model="form.companyName" type="text" class="form-control"
                                placeholder="Company name" />

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Currency
                            </div>

                            <div class="setting-desc">
                                Currency used for all monetary values across the system.
                            </div>

                        </div>

                        <div class="setting-control">

                            <select v-model="form.currency" class="form-control">
                                <option value="PHP">Philippine Peso (₱)</option>
                                <option value="USD">US Dollar ($)</option>
                            </select>

                        </div>

                    </div>


                    <div class="setting-row">

                        <div class="setting-copy">

                            <div class="setting-label">
                                Fiscal Year Start
                            </div>

                            <div class="setting-desc">
                                Month the company's fiscal year begins, used for annual reports.
                            </div>

                        </div>

                        <div class="setting-control">

                            <select v-model="form.fiscalYearStartMonth" class="form-control">
                                <option v-for="month in months" :key="month" :value="month">
                                    {{ month }}
                                </option>
                            </select>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
             STICKY UNSAVED CHANGES BAR
        ====================================================== -->

        <transition name="fade-slide">

            <div v-if="isDirty" class="unsaved-bar">

                <div class="unsaved-copy">

                    <span class="unsaved-dot"></span>

                    You have unsaved changes to system settings.

                </div>


                <div class="unsaved-actions">

                    <button class="cancel-btn" @click="discardChanges">
                        Discard
                    </button>


                    <button class="save-btn" @click="saveSettings">
                        Save Changes
                    </button>

                </div>

            </div>

        </transition>

    </div>
</template>


<script setup>

import {
    computed,
    onMounted,
    onBeforeUnmount,
    reactive,
    ref
} from 'vue'


defineOptions({
    name: 'SystemSettingsPage'
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
// DEFAULTS
// =====================================================

function createDefaultSettings() {

    return {

        cashAdvanceLimit: 5000,
        cashAdvanceRepaymentPercent: 10,
        allowMultipleAdvances: false,
        payPeriod: 'monthly',

        overtimeRatePerHour: 150,
        overtimeMultiplier: 1.25,
        standardWorkHours: 8,
        workDaysPerWeek: 5,
        workStartTime: '08:00',
        gracePeriodMinutes: 10,
        lateDeductionPerMinute: 2,
        autoApproveOvertime: false,

        // Government contributions & tax
        sssContributionPercent: 4.5,
        philhealthContributionPercent: 2.5,
        pagibigContributionAmount: 200,
        withholdingTaxPercent: 15,
        thirteenthMonthEnabled: true,

        // Company info
        companyName: 'Acme Trading Corp.',
        currency: 'PHP',
        fiscalYearStartMonth: 'January'

    }

}


const months = [
    'January', 'February', 'March', 'April',
    'May', 'June', 'July', 'August',
    'September', 'October', 'November', 'December'
]


// =====================================================
// STATE
// =====================================================

// savedSettings mirrors what's persisted / used by the rest of the app.
// form is the editable draft the admin is working on.

const savedSettings = reactive(
    createDefaultSettings()
)


const form = reactive({
    ...savedSettings
})


const isDirty = computed(() => {

    return Object.keys(savedSettings).some(
        key => form[key] !== savedSettings[key]
    )

})


// =====================================================
// SAVE / DISCARD / RESET
// =====================================================

function saveSettings() {

    Object.keys(savedSettings).forEach(key => {
        savedSettings[key] = form[key]
    })

    alert('System settings saved.')

}


function discardChanges() {

    Object.keys(form).forEach(key => {
        form[key] = savedSettings[key]
    })

}


function resetAllToDefaults() {

    const confirmed =
        window.confirm(
            'Reset all system settings to their default values? This cannot be undone.'
        )


    if (!confirmed) {
        return
    }


    const defaults =
        createDefaultSettings()


    Object.keys(defaults).forEach(key => {
        form[key] = defaults[key]
        savedSettings[key] = defaults[key]
    })

}


// =====================================================
// FORMATTING
// =====================================================

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
   CONTENT
===================================================== */

.content {
    padding: 1.75rem;
    padding-bottom: 2.5rem;
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
   SETTINGS LIST
===================================================== */

.settings-list {

    margin-top:
        1.1rem;

    border-top:
        1px solid var(--line, #DCD8CB);
}


.setting-row {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        1.5rem;

    padding:
        1rem .1rem;

    border-bottom:
        1px dashed var(--line, #DCD8CB);
}


.setting-row:last-child {
    border-bottom: none;
}


.setting-copy {

    flex:
        1;

    min-width:
        0;
}


.setting-label {

    font-weight:
        600;

    font-size:
        .86rem;

    color:
        var(--ink, #1C2B4A);
}


.setting-desc {

    font-size:
        .75rem;

    color:
        var(--slate, #6B7280);

    margin-top:
        .2rem;

    max-width:
        480px;
}


.setting-control {

    flex-shrink:
        0;

    width:
        200px;
}


/* =====================================================
   FORM CONTROLS
===================================================== */

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
        .55rem .7rem;

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


/* =====================================================
   MONEY / SUFFIX INPUT
===================================================== */

.input-money {

    position:
        relative;
}


.input-money>span:first-child {

    position:
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


.input-money.has-prefix .form-control {

    padding-left:
        1.55rem;
}


.input-money .suffix {

    position:
        absolute;

    right:
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
        .75rem;

    pointer-events:
        none;
}


.input-money.has-suffix .form-control {

    padding-right:
        2.4rem;
}


/* =====================================================
   TOGGLE SWITCH
===================================================== */

.toggle-switch {

    width:
        44px;

    height:
        24px;

    border-radius:
        20px;

    border:
        1px solid var(--line, #DCD8CB);

    background:
        var(--paper, #F2F1EA);

    position:
        relative;

    cursor:
        pointer;

    padding:
        0;

    margin-left:
        auto;

    display:
        block;

    transition:
        background .15s ease,
        border-color .15s ease;
}


.toggle-switch .toggle-knob {

    position:
        absolute;

    top:
        2px;

    left:
        2px;

    width:
        18px;

    height:
        18px;

    border-radius:
        50%;

    background:
        white;

    box-shadow:
        0 1px 2px rgba(28, 43, 74, .25);

    transition:
        transform .15s ease;
}


.toggle-switch.on {

    background:
        var(--green, #2F8F5B);

    border-color:
        var(--green, #2F8F5B);
}


.toggle-switch.on .toggle-knob {

    transform:
        translateX(20px);
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
        .48rem .9rem;

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
        .48rem .95rem;

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
   UNSAVED CHANGES BAR
===================================================== */

.unsaved-bar {

    position:
        sticky;

    bottom:
        0;

    left:
        0;

    right:
        0;

    background:
        var(--ink, #1C2B4A);

    color:
        #F3DFA6;

    padding:
        .9rem 1.75rem;

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

    box-shadow:
        0 -8px 24px rgba(28, 43, 74, .25);

    z-index:
        50;
}


.unsaved-copy {

    display:
        flex;

    align-items:
        center;

    gap:
        .55rem;

    font-size:
        .82rem;
}


.unsaved-dot {

    width:
        7px;

    height:
        7px;

    border-radius:
        50%;

    background:
        var(--gold, #C79A3D);

    box-shadow:
        0 0 0 3px rgba(199, 154, 61, .25);

    flex-shrink:
        0;
}


.unsaved-bar .cancel-btn {

    background:
        transparent;

    border-color:
        rgba(243, 223, 166, .35);

    color:
        #F3DFA6;
}


.unsaved-bar .cancel-btn:hover {

    background:
        rgba(243, 223, 166, .1);
}


.unsaved-bar .save-btn {

    background:
        var(--gold, #C79A3D);

    border-color:
        var(--gold, #C79A3D);

    color:
        var(--ink, #1C2B4A);
}


.unsaved-bar .save-btn:hover {

    background:
        #B98A2C;
}


.fade-slide-enter-active,
.fade-slide-leave-active {

    transition:
        opacity .18s ease,
        transform .18s ease;
}


.fade-slide-enter-from,
.fade-slide-leave-to {

    opacity:
        0;

    transform:
        translateY(12px);
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


.mb-3 {
    margin-bottom:
        1rem;
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

}


@media (max-width: 767px) {

    .setting-row {

        flex-direction:
            column;

        align-items:
            flex-start;
    }


    .setting-control {

        width:
            100%;
    }

}


@media (max-width: 576px) {

    .content {
        padding:
            1rem;

        padding-bottom:
            2rem;
    }


    .topbar {
        padding:
            1rem;
    }


    .col-6 {
        width:
            100%;
    }


    .unsaved-bar {

        padding:
            .9rem 1rem;

        justify-content:
            stretch;
    }


    .unsaved-actions {

        display:
            flex;

        gap:
            .5rem;

        width:
            100%;
    }


    .unsaved-actions .cancel-btn,
    .unsaved-actions .save-btn {

        flex:
            1;
    }

}
</style>