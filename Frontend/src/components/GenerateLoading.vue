<template>
    <div v-if="loadingModal" class="modal-backdrop">
        <div class="salary-modal loading-modal">
            <div class="modal-body loading-body">
                <div class="spinner"></div>
                <div class="loading-text">{{ loadingSteps[loadingStepIndex] }}</div>
                <div class="progress-bar-track">
                    <div class="progress-bar-fill" :style="{ width: progressPercent + '%' }"></div>
                </div>
                <div class="loading-progress">
                    Step {{ loadingStepIndex + 1 }} of {{ loadingSteps.length }}
                </div>
                <button class="cancel-loading-btn" @click="cancelLoading">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onBeforeUnmount } from 'vue';

const loadingModal = ref(false);
const loadingStepIndex = ref(0);
const progressPercent = ref(0);
const elapsedSeconds = ref(0);

const loadingSteps = [
    'Checking cash advance deduction & approval...',
    'Checking salary is up to date...',
    'Checking employee status (not terminated)...',
    'Confirming attendance records...',
    'Checking SSS contribution deduction...',
];

const totalDuration = 50;
const stepDuration = totalDuration / loadingSteps.length;

let stepTimeout = null;
let progressInterval = null;
let cancelled = false;
let onDoneCallback = null;
let onCancelCallback = null;

function startLoading(onDone, onCancel) {
    cancelled = false;
    loadingStepIndex.value = 0;
    progressPercent.value = 0;
    elapsedSeconds.value = 0;
    loadingModal.value = true;
    onDoneCallback = onDone;
    onCancelCallback = onCancel;

    progressInterval = setInterval(() => {
        if (cancelled) return;
        elapsedSeconds.value++;
        progressPercent.value = Math.min(
            100,
            (elapsedSeconds.value / totalDuration) * 100
        );
    }, 1000);

    nextStep();
}

function nextStep() {
    stepTimeout = setTimeout(() => {
        if (cancelled) return;

        if (loadingStepIndex.value < loadingSteps.length - 1) {
            loadingStepIndex.value++;
            nextStep();
        } else {
            finishLoading();
        }
    }, stepDuration * 1000);
}

function finishLoading() {
    clearInterval(progressInterval);
    progressPercent.value = 100;
    loadingModal.value = false;
    if (typeof onDoneCallback === 'function') onDoneCallback();
}

function cancelLoading() {
    cancelled = true;
    clearTimeout(stepTimeout);
    clearInterval(progressInterval);
    loadingModal.value = false;
    if (typeof onCancelCallback === 'function') onCancelCallback();
}

// safety: clear timers if component unmounts mid-loading
onBeforeUnmount(() => {
    clearTimeout(stepTimeout);
    clearInterval(progressInterval);
});

defineExpose({ startLoading, cancelLoading });
</script>

<style scoped>
.loading-modal {
    max-width: 380px;
}

.loading-body {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 24px;
    gap: 14px;
}

.spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #d1d5db;
    border-top-color: #2563eb;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.loading-text {
    font-weight: 600;
    color: #111827;
    text-align: center;
}

.progress-bar-track {
    width: 100%;
    height: 8px;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background: #2563eb;
    border-radius: 999px;
    transition: width 1s linear;
}

.loading-progress {
    font-size: 12px;
    color: #9ca3af;
}

.cancel-loading-btn {
    margin-top: 8px;
    background: transparent;
    border: 1px solid #d1d5db;
    color: #6b7280;
    padding: 6px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
}

.cancel-loading-btn:hover {
    background: #f3f4f6;
    color: #374151;
}
</style>