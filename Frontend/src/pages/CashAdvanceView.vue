<template>
    <div class="main d-flex justify-content-center align-items-center vh-100">
        <div class="content">
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
                                <select name="employeeid" id="employeeid" class="form-select"
                                    v-model="form.employeeUID">
                                    <option value="0">Select</option>
                                    <option v-for="value in employees" :value="value.id">{{ value.name }}</option>
                                </select>
                            </div>

                            <div class="form-group full">
                                <label> Amount </label>
                                <input v-model="form.amount" type="number" min="1" max="3000" step="1" required
                                    placeholder="e.g. 3000" />
                                <div v-if="form.amount > 3000" class="field-hint field-hint--error">
                                    Amount exceeds the limit of cash advance {{ cashAdvanceLimit }}.
                                </div>
                            </div>

                            <div class="form-group full">
                                <label>Reason</label>
                                <textarea v-model="form.reason" rows="4" required
                                    placeholder="Briefly describe what this advance is for..."></textarea>
                            </div>
                        </div>
                        <div class="form-footer">
                            <button type="button" class="btn btn-secondary-ledger" @click="resetForm">
                                Clear
                            </button>
                            <button type="submit" class="btn btn-primary-ledger"
                                :disabled="form.amount === 0 || form.amount > 3000 || form.employeeUID === 0">
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
                            <strong>{{ formatCurrency(lastSubmitted?.amount || 0) }}</strong>
                        </div>

                        <div class="detail-row">
                            <span>Payment Method</span>
                            <strong>{{ lastSubmitted?.paymentMethod }}</strong>
                        </div>

                        <div class="detail-row">
                            <span>Submitted</span>
                            <strong>{{ formatDate(lastSubmitted?.requestDate) }} · {{ lastSubmitted?.requestTime
                                }}</strong>
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
                        <div class="reason-content">{{ lastSubmitted?.reason }}</div>
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

import {
    ref
} from 'vue'

const employees = ref([
    {
        id: 1,
        name: 'Shiro 1',
    },
    {
        id: 2,
        name: 'Shiro 2',
    },
    {
        id: 3,
        name: 'Shiro 3',
    },
])

const form = ref({
    employeeUID: 0,
    amount: 0,
    reason: '',
});

const cashAdvanceLimit = ref(3000);


function resetForm() {
    Object.assign(form.value, {
        employeeUID: 0,
        amount: null,
        reason: '',
    })

}

const submitted = ref(false)

const lastSubmitted = ref(null)


function submitRequest() {

    if (!form.value.amount || form.value.amount > availableLimit.value) {
        return
    }


    lastSubmitted.value = {

        amount: form.value.amount,

        reason: form.value.reason.trim(),

        paymentMethod: form.value.paymentMethod,

        requestDate: getTodayDate(),

        requestTime:
            new Date().toLocaleTimeString('en-US', { hour12: true })

    }

    submitted.value = true

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


function getTodayDate() {

    const date = new Date()

    const year = date.getFullYear()

    const month = String(date.getMonth() + 1).padStart(2, '0')

    const day = String(date.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`

}

</script>


<style scoped>
.main {
    display: flex;
    flex: 1;
    min-width: 0;
}

.content {
    padding: 1.75rem;
}

.form-wrap {
    width: 100%;
    max-width: 640px;
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
}
</style>