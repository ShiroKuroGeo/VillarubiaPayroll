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
                            {{
                                formatCurrency(
                                    getSettingValue('Cash Advance Limit')
                                )
                            }}
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
                            {{
                                formatCurrency(
                                    getSettingValue(
                                        'Overtime Rate (Per Hour)'
                                    )
                                )
                            }}
                        </div>

                        <div class="stat-delta stat-delta--blue">
                            {{
                                getSettingValue(
                                    'Overtime Multiplier'
                                )
                            }}x base rate
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
                            {{
                                getSettingValue(
                                    'Standard Working Hours'
                                )
                            }}
                        </div>

                        <div class="stat-delta stat-delta--slate">
                            {{
                                getSettingValue(
                                    'Work Days per Week'
                                )
                            }} days / week
                        </div>

                    </div>
                </div>


                <div class="col-6 col-lg-3">
                    <div class="punch-card">

                        <div class="stamp red">
                            LATE
                        </div>

                        <div class="stat-label">
                            Late Deduction
                        </div>

                        <div class="stat-period">
                            Per minute
                        </div>

                        <div class="stat-value">
                            ₱{{
                                getSettingValue(
                                    'Late Deduction (Per Minute)'
                                )
                            }}
                        </div>

                        <div class="stat-delta" style="color: var(--red, #C24D3B);">
                            Per minute late
                        </div>

                    </div>
                </div>

            </div>

            <div v-for="section in sections" :key="section.id" class="panel my-3">
                <div class=" d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <div class="panel-title">
                            {{ section.name }}
                        </div>
                        <div class="panel-sub">
                            {{ section.description }}
                        </div>
                    </div>
                    <span v-if="section.tags" class="chip" style="text-transform: capitalize;">
                        {{ formatTags(section.tags) }}
                    </span>
                </div>
                <div class="settings-list">
                    <div v-for="setting in getSettingsBySection(section.name)" :key="setting.id" class="setting-row">
                        <div class="setting-copy">
                            <div class="setting-label">
                                {{ setting.name }}
                            </div>
                            <div class="setting-desc">
                                {{ setting.description }}
                            </div>
                        </div>
                        <div class="setting-control">
                            <input v-if="getInputType(setting) === 'number'" v-model="form[setting.id]" type="number" class="form-control" :min="getMin(setting)" :max="getMax(setting)" :step="getStep(setting)" />
                            <input v-else-if="getInputType(setting) === 'time'" v-model="form[setting.id]" type="time" class="form-control" />
                            <select v-else-if="getInputType(setting) === 'pay-period'" v-model="form[setting.id]" class="form-control">
                                <option value="Weekly">
                                    Weekly
                                </option>
                                <option value="Bi-weekly">
                                    Bi-weekly
                                </option>
                                <option value="Monthly">
                                    Monthly
                                </option>
                            </select>

                            <input v-else v-model="form[setting.id]" type="text" class="form-control" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

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


                    <button class="save-btn" :disabled="saving" @click="saveSettings">
                        {{
                            saving
                                ? 'Saving...'
                                : 'Save Changes'
                        }}
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

import { useMaintenanceStore } from '@/stores/useMaintenance'


defineOptions({
    name: 'SystemSettingsPage'
})


defineEmits([
    'toggle-sidebar'
])

const liveClock = ref('--:--:--')

let clockTimer = null


const tickClock = () => {
    liveClock.value =
        new Date().toLocaleTimeString('en-US', { hour12: true })
}


const currentDate = computed(() => {
    return new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    })
})

const maintenanceStore = useMaintenanceStore()

const maintenance = ref([])
const form = reactive({})
const savedSettings = reactive({})
const saving = ref(false)
const sections = ref([])

const normalizeItem = (item) => ({
    id: item.main_id ?? item.id,
    name: item.main_name ?? item.name,
    description: item.main_desc ?? item.description,
    value: item.main_value ?? item.value,
    tags: item.main_tags ?? item.tags,
    isSection: Boolean(item.main_is_section ?? item.is_section),
    sectionName: item.main_section_name ?? item.section_name ?? null,
    inputType: item.main_input_type ?? item.input_type ?? null
})

const getSettingValue = (name) => {

    const setting =
        maintenance.value.find(
            item =>
                item.name === name &&
                !item.isSection
        )

    if (!setting) {
        return 0
    }

    return (
        form[setting.id] ??
        setting.value ??
        0
    )
}

const getSettingsBySection = (sectionName) => {

    return maintenance.value.filter(
        item =>
            !item.isSection &&
            item.sectionName === sectionName
    )

}

const getInputType = (setting) => {

    if (setting.inputType) {
        return setting.inputType
    }

    const name = setting.name.toLowerCase()

    if (name.includes('time')) {
        return 'time'
    }

    if (name === 'pay period') {
        return 'pay-period'
    }

    const numberKeywords = [
        'limit',
        'rate',
        'multiplier',
        'hours',
        'days',
        'deduction',
        'amount',
        'percent',
        'percentage',
        'minute',
        'contribution'
    ]

    if (numberKeywords.some(keyword => name.includes(keyword))) {
        return 'number'
    }

    return 'text'

}

const getMin = (setting) => {

    const name = setting.name.toLowerCase()

    if (name.includes('multiplier')) return 1
    if (name.includes('hours')) return 1
    if (name.includes('days')) return 1

    return 0

}

const getMax = (setting) => {

    const name = setting.name.toLowerCase()

    if (name.includes('days per week')) return 7
    if (name.includes('hours')) return 24
    if (name.includes('percent')) return 100

    return null

}

const getStep = (setting) => {

    const name = setting.name.toLowerCase()

    if (name.includes('multiplier')) return 0.1
    if (name.includes('rate')) return 0.25
    if (name.includes('deduction')) return 0.05
    if (name.includes('hours')) return 0.5

    return 1

}

const formatTags = (tags) => {

    if (!tags) {
        return ''
    }

    return tags
        .split(',')
        .map(tag => tag.trim())
        .join(' • ')

}

const formatCurrency = (amount) => {

    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2
    }).format(Number(amount || 0))

}


const isDirty = computed(() => {

    return Object.keys(savedSettings).some(
        key =>
            String(form[key]) !==
            String(savedSettings[key])
    )

})


const getMaintenance = async () => {

    try {

        const result = await maintenanceStore.maintenanceList()

        // Normalize every record to a consistent shape before use
        const normalized = result.map(normalizeItem)

        maintenance.value = normalized

        sections.value = normalized.filter(
            item => item.isSection
        )

        normalized
            .filter(item => !item.isSection)
            .forEach(item => {
                form[item.id] = item.value ?? ''
                savedSettings[item.id] = item.value ?? ''
            })

    } catch (error) {

        console.error(
            'Failed to load system settings:',
            error
        )

    }

}

const saveSettings = async () => {
    try {
        saving.value = true
        const settings =
            maintenance.value
                .filter(item => !item.isSection)
                .map(item => {
                    const raw = form[item.id]
                    return {
                        id: item.id,
                        value:
                            raw === null || raw === undefined
                                ? null
                                : String(raw)
                    }
                })

        await maintenanceStore.updateSettings({ settings })

        settings.forEach(setting => {
            savedSettings[setting.id] = setting.value
        })

        maintenance.value.forEach(item => {
            const updated = settings.find(
                setting => setting.id === item.id
            )
            if (updated) {
                item.value = updated.value
            }
        })
    } catch (error) {
        console.error(
            'Failed to save settings:',
            error
        )
        alert('Failed to save system settings.')
    } finally {
        saving.value = false
    }

}


const discardChanges = () => {

    Object.keys(savedSettings).forEach(key => {
        form[key] = savedSettings[key]
    })

}


onMounted(() => {

    tickClock()

    clockTimer = setInterval(tickClock, 1000)

    getMaintenance()

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

.content {
    padding: 1.75rem;
    padding-bottom: 2.5rem;
}

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