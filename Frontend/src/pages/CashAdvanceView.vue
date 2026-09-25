<template>
    <div class="main d-flex justify-content-center align-items-center vh-100">
        <div class="content vh-100">
            <div class="form-wrap">
                <div v-if="!submitted" class="panel">
                    <div class="section-header">
                        <div>
                            <div class="section-title">
                                New cash advance request
                            </div>
                            <div class="panel-sub">
                                Fill out the form below to request a cash advance
                            </div>
                        </div>
                    </div>
                    <form @submit.prevent="submitRequest">
                        <div class="form-grid">
                            <div class="form-group full">
                                <label>Select your name</label>
                                <select name="employeeid" id="employeeid" class="form-select" v-model="form.employee_id">
                                    <option value="0">Select</option>
                                    <option v-for="value in employees" :key="value.id" :value="value.id">{{ value.name }}</option>
                                </select>
                            </div>

                            <div class="form-group full">
                                <label>Amount</label>
                                <input v-model="form.amount" type="number" min="1" :max="cashAdvanceLimit" step="1" required placeholder="e.g. 3000" />
                                <div v-if="form.amount > cashAdvanceLimit" class="field-hint field-hint--error">
                                    Amount exceeds the limit of cash advance {{ formatCurrency(cashAdvanceLimit) }}.
                                </div>
                            </div>

                            <!-- Payment type selector -->
                            <div class="form-group full">
                                <label>How should this be deducted?</label>
                                <div class="type-toggle" role="radiogroup">
                                    <button type="button" class="type-option" :class="{ active: form.payment_type === 'Installment' }" role="radio" :aria-checked="form.payment_type === 'Installment'" @click="setPaymentType('Installment')">
                                        <span class="type-option-title">Installment</span>
                                        <span class="type-option-desc">Split evenly, deducted every payroll</span>
                                    </button>
                                    <button type="button" class="type-option" :class="{ active: form.payment_type === 'Custom' }" role="radio" :aria-checked="form.payment_type === 'Custom'" @click="setPaymentType('Custom')">
                                        <span class="type-option-title">Custom</span>
                                        <span class="type-option-desc">You choose the amount and the payroll date</span>
                                    </button>
                                </div>
                                <p class="type-explainer">
                                    <template v-if="form.payment_type === 'Installment'">
                                        The total is divided across the number of payrolls you set below, and deducted automatically on every cutoff until it's fully paid.
                                    </template>
                                    <template v-else>
                                        Nothing is deducted automatically. Pick the exact amount and the payroll cutoff you want it taken from — useful if you only want part of it deducted on a specific date.
                                    </template>
                                </p>
                            </div>

                            <!-- Installment fields -->
                            <template v-if="form.payment_type === 'Installment'">
                                <div class="form-group full">
                                    <label>
                                        <span>Deduct over</span>
                                    </label>
                                    <div class="installment-row">
                                        <input type="range" min="1" max="30" step="1" v-model.number="form.installment_count" />
                                        <span class="installment-count-out">
                                            {{ form.installment_count }} payroll{{ form.installment_count > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group full">
                                    <div class="preview-box">
                                        <span>Per payroll deduction</span>
                                        <strong>{{ formatCurrency(installmentAmount) }}</strong>
                                    </div>
                                </div>
                            </template>

                            <!-- Custom fields -->
                            <template v-else>
                                <div class="form-group full">
                                    <label>Amount to deduct this time</label>
                                    <input v-model="form.custom_amount" type="number" min="1" :max="form.amount || cashAdvanceLimit" step="1" required placeholder="e.g. 1000" />
                                    <div v-if="form.custom_amount > form.amount" class="field-hint field-hint--error">
                                        Can't deduct more than the requested amount.
                                    </div>
                                </div>

                                <div class="form-group full">
                                    <label>On which payroll cutoff</label>
                                    <input v-model="form.target_cutoff_start" type="date" required />
                                </div>

                                <div class="form-group full">
                                    <div class="preview-box">
                                        <span>Remaining balance after</span>
                                        <strong>{{ formatCurrency(remainingAfterCustom) }}</strong>
                                    </div>
                                </div>
                            </template>

                            <div class="form-group full">
                                <label>Reason</label>
                                <textarea v-model="form.reason" rows="4" required placeholder="Briefly describe what this advance is for..."></textarea>
                            </div>
                        </div>
                        <div class="form-footer">
                            <button type="button" class="btn btn-secondary-ledger" @click="resetForm">
                                Clear
                            </button>
                            <button type="submit" class="btn btn-primary-ledger" :disabled="!canSubmit">
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
                <div v-else class="panel confirm-panel">
                    <div class="confirm-stamp">
                        SUBMITTED
                    </div>
                    <div class="confirm-icon">
                        ✓
                    </div>
                    <div class="confirm-title">
                        Request submitted
                    </div>
                    <div class="confirm-sub">
                        Your cash advance request has been sent for review.
                    </div>

                    <div class="details-list">
                        <div class="detail-row">
                            <span>Amount</span>
                            <strong>{{ formatCurrency(form.amount || 0) }}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Type</span>
                            <span class="badge-type" :class="lastSubmitted?.payment_type === 'Custom' ? 'badge-type--custom' : 'badge-type--installment'">
                                {{ lastSubmitted?.payment_type }}
                            </span>
                        </div>
                        <div class="detail-row" v-if="lastSubmitted?.payment_type === 'Installment'">
                            <span>Per payroll</span>
                            <strong>{{ formatCurrency(lastSubmitted?.installment_amount || 0) }} &times; {{ lastSubmitted?.installment_count || 0 }}</strong>
                        </div>
                        <div class="detail-row" v-else>
                            <span>Deducting</span>
                            <strong>{{ formatCurrency(lastSubmitted?.custom_amount || 0) }} on {{ formatDate(lastSubmitted?.target_cutoff_start) }}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Submitted</span>
                            <strong>
                                {{ viewEmployee(form) }} · {{ lastSubmitted?.requestTime }}
                            </strong>
                        </div>
                        <div class="detail-row">
                            <span>Status</span>
                            <span class="badge-status badge-pending">
                                PENDING
                            </span>
                        </div>
                    </div>
                    <div class="reason-box mt-3">
                        <div class="info-label">Reason</div>
                        <div class="reason-content">{{ form.reason }}</div>
                    </div>
                    <div class="confirm-actions">
                        <button type="button" class="btn btn-primary-ledger" @click="startNewRequest">
                            Submit Another Request
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>

import { useCashAdvanceStore } from '@/stores/useCashAdvance';
import { useEmployeeStore } from '@/stores/useEmployee';
import {
    onMounted,
    ref,
    computed
} from 'vue'

const employees = ref([]);
const employeeStore = useEmployeeStore();
const cashAdvanceStore = useCashAdvanceStore();
const today = new Date();

function defaultForm() {
    return {
        employee_id: 0,
        amount: null,
        payment_type: 'Installment',

        // Installment fields
        installment_amount: 0,
        installment_count: 1,

        // Custom fields
        custom_amount: null,
        target_cutoff_start: '',
        balance: 0,

        requested_date: today.toISOString(),
        reason: '',
    }
}

const form = ref(defaultForm());

const cashAdvanceLimit = ref(10000);

const installmentAmount = computed(() => {
    const amt = Number(form.value.amount) || 0
    const count = Number(form.value.installment_count) || 1

    if (amt <= 0 || count <= 0) {
        return 0
    }

    return Math.ceil((amt / count) * 100) / 100
})

const remainingAfterCustom = computed(() => {
    const amt = Number(form.value.amount) || 0
    const custom = Number(form.value.custom_amount) || 0

    return Math.max(amt - custom, 0)
})

const canSubmit = computed(() => {
    if (!form.value.employee_id || form.value.employee_id === 0) return false
    if (!form.value.amount || form.value.amount <= 0) return false
    if (form.value.amount > cashAdvanceLimit.value) return false

    if (form.value.payment_type === 'Custom') {
        if (!form.value.custom_amount || form.value.custom_amount <= 0) return false
        if (form.value.custom_amount > form.value.amount) return false
        if (!form.value.target_cutoff_start) return false
    }

    return true
})

function setPaymentType(type) {
    form.value.payment_type = type
}

function resetForm() {
    form.value = defaultForm()
}

const submitted = ref(false)
const lastSubmitted = ref(null)

const submitRequest = async () => {

    if (!canSubmit.value) {
        return
    }

    const payload = { ...form.value }

    if (payload.payment_type === 'Installment') {
        payload.installment_amount = installmentAmount.value
        payload.custom_amount = null
        payload.target_cutoff_start = null
        payload.balance = null
    } else {
        payload.installment_amount = null
        payload.installment_count = null
        payload.balance = Number(payload.amount)
    }

    const createCashAdvance = await cashAdvanceStore.createCashAdvance(payload)

    if (createCashAdvance === 409) {
        submitted.value = false
    } else {
        lastSubmitted.value = {
            ...payload,
            requestTime: new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }),
        }
        submitted.value = true
    }
}

const viewEmployee = (emp) => {
    const result = employees?.value.find(ar => ar.id === emp.employee_id);

    return result ? result.name : '—'
}

function startNewRequest() {
    resetForm()
    submitted.value = false
    lastSubmitted.value = null
}

function formatCurrency(value) {

    return new Intl.NumberFormat(
        'en-PH',
        {
            style: 'currency',
            currency: 'PHP',
            minimumFractionDigits: 2
        }
    ).format(Number(value) || 0)

}

const listEmployee = async () => {
    const listEmployees = await employeeStore.allEmployees();
    employees.value = listEmployees.data.data;
}

function formatDate(date) {

    if (!date) return '—'

    return new Date(date + 'T00:00:00').toLocaleDateString(
        'en-US',
        {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        }
    )

}

onMounted(() => {
    listEmployee();
});
</script>

<style scoped>
.main {
    display: flex;
    flex: 1;
    min-width: 0;
}

.content {
    padding: 1.75rem;
    width: 740px;
}

.form-wrap {
    width: 100%;
    max-width: 840px;
}

.panel {
    background: var(--paper-2, #FBFAF6);
    border: 1px solid var(--line, #DCD8CB);
    border-radius: 10px;
    padding: 1.4rem 1.5rem;
}

.section-title {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: .15rem;
}

.panel-sub {
    font-size: .78rem;
    color: var(--slate, #6B7280);
}

.section-header {
    margin-bottom: 1.1rem;
}


/* LIMIT NOTE */

.limit-note {
    font-size: .8rem;
    color: var(--slate, #6B7280);
    background: var(--paper, #F2F1EA);
    border-radius: 7px;
    padding: .75rem .9rem;
    margin-bottom: 1.3rem;
}

.limit-note strong {
    color: var(--ink, #1C2B4A);
}


/* FORM */

.form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: .4rem;
}

.form-group label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .65rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--slate, #6B7280);
    font-weight: 600;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid var(--line, #DCD8CB);
    background: var(--paper-2, #FBFAF6);
    color: var(--ink, #1C2B4A);
    border-radius: 7px;
    padding: .65rem .75rem;
    font-size: .85rem;
    outline: none;
    font-family: inherit;
    resize: vertical;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--gold, #C79A3D);
    box-shadow: 0 0 0 3px rgba(199, 154, 61, .12);
}

.field-hint {
    font-size: .72rem;
}

.field-hint--error {
    color: var(--red, #C24D3B);
}


/* PAYMENT TYPE TOGGLE */

.type-toggle {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .6rem;
}

.type-option {
    text-align: left;
    display: flex;
    flex-direction: column;
    gap: .25rem;
    border: 1px solid var(--line, #DCD8CB);
    background: var(--paper, #F2F1EA);
    border-radius: 8px;
    padding: .7rem .8rem;
    cursor: pointer;
    font-family: inherit;
    transition: border-color .15s ease, background .15s ease;
}

.type-option:hover {
    border-color: var(--gold, #C79A3D);
}

.type-option.active {
    background: var(--paper-2, #FBFAF6);
    border-color: var(--gold, #C79A3D);
    box-shadow: 0 0 0 3px rgba(199, 154, 61, .12);
}

.type-option-title {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: .92rem;
    color: var(--ink, #1C2B4A);
}

.type-option-desc {
    font-size: .72rem;
    line-height: 1.4;
    color: var(--slate, #6B7280);
}

.type-explainer {
    font-size: .76rem;
    line-height: 1.5;
    color: var(--slate, #6B7280);
    margin: .5rem 0 0;
}

.installment-row {
    display: flex;
    align-items: center;
    gap: .75rem;
}

.installment-row input[type="range"] {
    flex: 1;
}

.installment-count-out {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .78rem;
    font-weight: 600;
    color: var(--ink, #1C2B4A);
    min-width: 82px;
    text-align: right;
}

.preview-box {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    background: var(--paper, #F2F1EA);
    border-radius: 7px;
    padding: .75rem .9rem;
}

.preview-box span {
    font-size: .78rem;
    color: var(--slate, #6B7280);
}

.preview-box strong {
    font-size: 1.05rem;
    color: var(--ink, #1C2B4A);
}

.form-footer {
    display: flex;
    justify-content: flex-end;
    gap: .6rem;
    padding-top: 1.4rem;
    margin-top: 1.4rem;
    border-top: 1px solid var(--line, #DCD8CB);
}


/* BUTTONS */

.btn {
    border-radius: 6px;
    padding: .55rem .95rem;
    border: 1px solid transparent;
    cursor: pointer;
    font-size: .8rem;
    font-weight: 600;
    font-family: inherit;
}

.btn:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.btn-primary-ledger {
    background: var(--ink, #1C2B4A);
    color: #F3DFA6;
    border-color: var(--ink, #1C2B4A);
}

.btn-primary-ledger:hover:not(:disabled) {
    background: #28395E;
}

.btn-secondary-ledger {
    background: var(--paper-2, #FBFAF6);
    color: var(--ink-2, #28395E);
    border-color: var(--line, #DCD8CB);
}

.btn-secondary-ledger:hover {
    background: var(--paper, #F2F1EA);
}


/* CONFIRMATION */

.confirm-panel {
    text-align: center;
    position: relative;
    padding: 2.2rem 1.6rem 1.8rem;
}

.confirm-stamp {
    position: absolute;
    top: 1.2rem;
    right: 1.3rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: .6rem;
    font-weight: 700;
    letter-spacing: .06em;
    color: var(--green, #2F8F5B);
    border: 2px dashed var(--green, #2F8F5B);
    border-radius: 50%;
    width: 66px;
    height: 66px;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: rotate(-8deg);
    text-align: center;
    line-height: 1.1;
}

.confirm-icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--green-bg, #E5F2EA);
    color: var(--green, #2F8F5B);
    font-size: 1.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.confirm-title {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.25rem;
}

.confirm-sub {
    font-size: .8rem;
    color: var(--slate, #6B7280);
    margin-top: .3rem;
    margin-bottom: 1.4rem;
}

.confirm-panel .details-list {
    text-align: left;
}

.confirm-panel .reason-box {
    text-align: left;
}

.confirm-actions {
    margin-top: 1.4rem;
    padding-top: 1.2rem;
    border-top: 1px solid var(--line, #DCD8CB);
    display: flex;
    justify-content: center;
}


/* DETAILS LIST */

.details-list {
    margin-bottom: 0;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: .7rem 0;
    border-bottom: 1px dashed var(--line, #DCD8CB);
    font-size: .82rem;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row>span:first-child {
    color: var(--slate, #6B7280);
}


/* TYPE BADGE */

.badge-type {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .62rem;
    font-weight: 600;
    padding: .3rem .55rem;
    border-radius: 5px;
    letter-spacing: .03em;
    display: inline-block;
}

.badge-type--installment {
    background: var(--amber-bg, #F6EEDB);
    color: var(--gold-dark, #9C7726);
}

.badge-type--custom {
    background: #E4E9F4;
    color: var(--ink-2, #28395E);
}


/* REASON BOX */

.reason-box {
    padding: .85rem;
    background: var(--paper, #F2F1EA);
    border-radius: 7px;
}

.info-label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .63rem;
    font-weight: 600;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: var(--slate, #6B7280);
    margin-bottom: .3rem;
}

.reason-content {
    font-size: .82rem;
    line-height: 1.5;
    color: var(--ink, #1C2B4A);
}


/* STATUS BADGE */

.badge-status {
    font-family: 'IBM Plex Mono', monospace;
    font-size: .62rem;
    font-weight: 600;
    padding: .3rem .55rem;
    border-radius: 5px;
    letter-spacing: .03em;
    display: inline-block;
}

.badge-pending {
    background: var(--amber-bg, #F6EEDB);
    color: var(--gold-dark, #9C7726);
}


/* LAYOUT UTILITIES */

.d-flex {
    display: flex;
}

.align-items-center {
    align-items: center;
}

.gap-2 {
    gap: .5rem;
}

.mt-3 {
    margin-top: 1rem;
}


@media (max-width: 576px) {
    .content {
        padding: 1rem;
    }

    .topbar {
        padding: 1rem;
    }

    .panel {
        padding: 1.1rem 1.2rem;
    }

    .type-toggle {
        grid-template-columns: 1fr;
    }
}
</style>