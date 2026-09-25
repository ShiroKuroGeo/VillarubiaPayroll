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
                <input v-model="startDate" type="date" class="date-chip" />
                -
                <input v-model="endDate" type="date" class="date-chip" />
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
                                    {{ presentCount + lateCount }} of {{ totalEmployeeCount }} employees
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
                            Daily Attendance Log
                        </div>
                        <div class="panel-sub">
                            Track employee check-ins, check-outs, and hours worked
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <div class="search-box">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-3-3" />
                            </svg>
                            <input v-model="searchQuery" type="text" placeholder="Search employee..." />
                        </div>

                        <div class="select-wrapper">
                            <select v-model="searchQuery" class="custom-select" id="employee-search" name="employee-search">
                                <option v-for="item in employeeNameList" :key="item.value" :value="item.value">
                                    {{ item.label }}
                                </option>
                            </select>
                            <span class="select-arrow"></span>
                        </div>

                        <!-- Action Button -->
                        <div>
                            <button class="add-btn" @click="openAddModal">
                                + Import Biometrics
                            </button>
                        </div>
                    </div>
                </div>
                <div class="filter-row">
                    <button v-for="filter in statusFilters" :key="filter.key" class="filter-pill" :class="{
                        active: statusFilter === filter.key
                    }" @click="statusFilter = filter.key">
                        {{ filter.label }}
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table-ledger salary-table" v-if="paginatedAttendanceData.length">
                        <thead>
                            <tr>
                                <th rowspan="2">Employee</th>
                                <th rowspan="2" class="text-center">Date</th>
                                <th colspan="2" class="text-center" style="background-color: #FEFCE8; color: #854D0E; text-align: center;">Before Noon</th>
                                <th colspan="2" class="text-center" style="background-color: #EFF6FF; color: #1E40AF; text-align: center;">After Noon</th>
                                <th colspan="2" class="text-center" style="background-color: #F0FDF4; color: #166534; text-align: center;">Overtime</th>
                                <th rowspan="2" class="text-center">Worked Hrs</th>
                                <th rowspan="2" class="text-center">Overtime Hrs</th>
                                <th rowspan="2" class="text-center">Status</th>
                                <th rowspan="2" class="text-center">Action</th>
                            </tr>
                            <tr>
                                <th style="background-color: #FEFCE8; color: #854D0E; text-align: center;">Time In</th>
                                <th style="background-color: #FEFCE8; color: #854D0E; text-align: center;">Time Out</th>
                                <th style="background-color: #EFF6FF; color: #1E40AF; text-align: center;">Time In</th>
                                <th style="background-color: #EFF6FF; color: #1E40AF; text-align: center;">Time Out</th>
                                <th style="background-color: #F0FDF4; color: #166534; text-align: center;">Time In</th>
                                <th style="background-color: #F0FDF4; color: #166534; text-align: center;">Time Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="record in paginatedAttendanceData" :key="record.id">
                                <td class="money" width="150">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm">
                                            <img v-if="record.image" :src="storageImage(record.image)" :alt="record.employeeName" />
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

                                <td class="text-center money">
                                    {{ formatDate(record.date) }}
                                </td>

                                <td class="text-center money" style="background-color: #FEFCE8; color: #854D0E; text-align: center;" :class="{ deduction: record.status === 'late' }">
                                    {{ formatTime(record.amTimeIn) || '—' }}
                                </td>
                                <td class="text-center money" style="background-color: #FEFCE8; color: #854D0E; text-align: center;">
                                    {{ formatTime(record.amTimeOut) || '—' }}
                                </td>

                                <td class="text-center money" style="background-color: #EFF6FF; color: #1E40AF; text-align: center;">
                                    {{ formatTime(record.pmTimeIn) || '—' }}
                                </td>
                                <td class="text-center money" style="background-color: #EFF6FF; color: #1E40AF; text-align: center;">
                                    {{ formatTime(record.pmTimeOut) || '—' }}
                                </td>
                                <td class="text-center money" style="background-color: #F0FDF4; color: #166534; text-align: center;">
                                    {{ formatTime(record.overIn) || '—' }}
                                </td>
                                <td class="text-center money" style="background-color: #F0FDF4; color: #166534; text-align: center;">
                                    {{ formatTime(record.overOut) || '—' }}
                                </td>
                                <td class="text-center money allowance">
                                    {{ record.hoursWorked }} hrs
                                </td>
                                <td class="text-center money">
                                    {{ record.overtimeHours || 0 }} hrs
                                </td>
                                <td class="text-center">
                                    <span class="badge-status" :class="badgeClass(record.status)">
                                        {{ formatStatus(record.status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="action-group">
                                        <button class="action-btn edit-btn" @click="openEditModal(record)">
                                            Edit
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
                <div class="pagination-bar" v-if="filteredAttendanceData.length">
                    <div class="pagination-info">
                        Showing {{ paginationStart }}–{{ paginationEnd }} of {{ filteredAttendanceData.length }}
                    </div>
                    <div class="pagination-controls">
                        <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">
                            Prev
                        </button>
                        <button v-for="page in pageNumbers" :key="page" class="page-btn" :class="{ active: page === currentPage }" @click="currentPage = page">
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
            <div class="att-modal">
                <div class="att-modal__header">
                    <div class="att-modal__heading">
                        <span class="att-modal__dot" :class="editingAttendance ? 'is-editing' : 'is-new'" />
                        <div>
                            <div class="att-modal__title">
                                {{ editingAttendance ? 'Edit attendance' : 'Add attendance' }}
                            </div>
                            <div class="att-modal__sub">
                                Log employee check-in and check-out
                            </div>
                        </div>
                    </div>
                    <button class="att-modal__close" @click="closeModal" aria-label="Close">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M2 2L14 14M14 2L2 14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <div class="att-modal__body">
                    <div class="import-panel" v-if="!editingAttendance">
                        <label class="import-panel__label" for="biometric-file">
                            Biometric attendance file
                        </label>

                        <label for="biometric-file" class="import-panel__drop">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 3v10m0 0l-3.5-3.5M10 13l3.5-3.5M4 16.5h12" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>{{ selectedBiometricFile ? selectedBiometricFile.name : 'Choose a file or drop it here' }}</span>
                            <input id="biometric-file" ref="biometricFileInput" type="file" accept=".csv,.xls,.xlsx,.txt" class="import-panel__input" @change="handleBiometricFile" />
                        </label>

                        <p class="import-panel__hint">
                            Upload the file exported from the biometric machine — supports .csv, .xls, .xlsx, .txt
                        </p>
                    </div>

                    <div v-else class="edit-form">
                        <div class="employee-row">
                            <div class="avatar-md">
                                <img v-if="attendanceForm.image" :src="storageImage(attendanceForm.image)" :alt="attendanceForm.employeeName" />
                                <span v-else>{{ initials(attendanceForm.employeeName) }}</span>
                            </div>
                            <div>
                                <div class="employee-row__name">
                                    {{ attendanceForm.employeeName }}
                                </div>
                                <div class="employee-row__id">
                                    Employee #{{ attendanceForm.employeeId.toString().padStart(4, '0') }}
                                </div>
                            </div>
                            <input type="text" hidden v-model="attendanceForm.employeeId">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Date</label>
                                <input v-model="attendanceForm.date" type="date" class="form-control" />
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <div class="status-select" :class="`is-${statusKey(attendanceForm.status)}`">
                                    <select v-model="attendanceForm.status" class="form-control">
                                        <option value="Present">Present</option>
                                        <option value="Late">Late</option>
                                        <option value="Half Day">Half day</option>
                                        <option value="Absent">Absent</option>
                                        <option value="Leave">On leave</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- BEFORE NOON -->
                        <div class="shift-section shift-section--am">
                            <div class="shift-section__label">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <circle cx="7" cy="7" r="3.2" stroke="currentColor" stroke-width="1.3" />
                                    <path d="M7 1v1.4M7 11.6V13M1 7h1.4M11.6 7H13M2.8 2.8l1 1M10.2 10.2l1 1M2.8 11.2l1-1M10.2 3.8l1-1" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                                </svg>
                                Before noon
                            </div>
                            <div class="form-row">
                                <!-- Before Noon IN -->
                                <div class="form-group">
                                    <div class="label-row d-flex justify-content-between align-items-center mb-1">
                                        <label class="input-label mb-0">TIME IN</label>
                                        <button v-if="attendanceForm.before_noon_in" type="button" class="btn-clear-time" @click="handleRemoveTime('before_noon_in')">
                                            REMOVE
                                        </button>
                                    </div>
                                    <input v-model="attendanceForm.before_noon_in" type="time" class="form-control" :disabled="isTimeDisabled" />
                                </div>

                                <!-- Before Noon OUT -->
                                <div class="form-group">
                                    <div class="label-row d-flex justify-content-between align-items-center mb-1">
                                        <label class="input-label mb-0">TIME OUT</label>
                                        <button v-if="attendanceForm.before_noon_out" type="button" class="btn-clear-time" @click="handleRemoveTime('before_noon_out')">
                                            REMOVE
                                        </button>
                                    </div>
                                    <input v-model="attendanceForm.before_noon_out" type="time" class="form-control" :disabled="isTimeDisabled" />
                                </div>
                            </div>
                        </div>

                        <!-- AFTER NOON -->
                        <div class="shift-section shift-section--pm">
                            <div class="shift-section__label">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <path d="M9.5 2.2A5 5 0 1011.8 9a4 4 0 01-2.3-6.8z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round" />
                                </svg>
                                After noon
                            </div>
                            <div class="form-row">
                                <!-- After Noon IN -->
                                <div class="form-group">
                                    <div class="label-row d-flex justify-content-between align-items-center mb-1">
                                        <label class="input-label mb-0">TIME IN</label>
                                        <button v-if="attendanceForm.after_noon_in" type="button" class="btn-clear-time" @click="handleRemoveTime('after_noon_in')">
                                            REMOVE
                                        </button>
                                    </div>
                                    <input v-model="attendanceForm.after_noon_in" type="time" class="form-control" :disabled="isTimeDisabled" />
                                </div>

                                <!-- After Noon OUT -->
                                <div class="form-group">
                                    <div class="label-row d-flex justify-content-between align-items-center mb-1">
                                        <label class="input-label mb-0">TIME OUT</label>
                                        <button v-if="attendanceForm.after_noon_out" type="button" class="btn-clear-time" @click="handleRemoveTime('after_noon_out')">
                                            REMOVE
                                        </button>
                                    </div>
                                    <input v-model="attendanceForm.after_noon_out" type="time" class="form-control" :disabled="isTimeDisabled" />
                                </div>
                            </div>
                        </div>

                        <!-- OVERTIME (OT) -->
                        <div class="shift-section shift-section--ot">
                            <div class="shift-section__label">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <path d="M7.8 1L2.5 8h3.2l-.9 5L10 6H6.8l1-5z" stroke="currentColor" stroke-width="1.1" stroke-linejoin="round" />
                                </svg>
                                Overtime
                            </div>
                            <div class="form-row">
                                <!-- Overtime IN -->
                                <div class="form-group">
                                    <div class="label-row d-flex justify-content-between align-items-center mb-1">
                                        <label class="input-label mb-0">TIME IN</label>
                                        <button v-if="attendanceForm.overtime_in" type="button" class="btn-clear-time" @click="handleRemoveTime('overtime_in')">
                                            REMOVE
                                        </button>
                                    </div>
                                    <input v-model="attendanceForm.overtime_in" type="time" class="form-control" :disabled="isTimeDisabled" />
                                </div>

                                <!-- Overtime OUT -->
                                <div class="form-group">
                                    <div class="label-row d-flex justify-content-between align-items-center mb-1">
                                        <label class="input-label mb-0">TIME OUT</label>
                                        <button v-if="attendanceForm.overtime_out" type="button" class="btn-clear-time" @click="handleRemoveTime('overtime_out')">
                                            REMOVE
                                        </button>
                                    </div>
                                    <input v-model="attendanceForm.overtime_out" type="time" class="form-control" :disabled="isTimeDisabled" />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <input v-model="attendanceForm.notes" type="text" class="form-control" placeholder="Optional remarks" />
                        </div>

                        <div class="summary-strip">
                            <div>
                                <div class="summary-strip__label">Total hours worked</div>
                                <div class="summary-strip__value">
                                    {{ formHoursWorked }} <span>hrs</span>
                                </div>
                            </div>
                            <div class="summary-strip__breakdown">
                                <span><i class="dot dot--am" />{{ formatTime(attendanceForm.before_noon_in) || '--:--' }}–{{ formatTime(attendanceForm.before_noon_out) || '--:--' }}</span>
                                <span><i class="dot dot--pm" />{{ formatTime(attendanceForm.after_noon_in) || '--:--' }}–{{ formatTime(attendanceForm.after_noon_out) || '--:--' }}</span>
                                <span><i class="dot dot--ot" />{{ formatTime(attendanceForm.overtime_in) || '--:--' }}–{{ formatTime(attendanceForm.overtime_out) || '--:--' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="att-modal__footer">
                    <button class="btn btn--ghost" @click="closeModal">
                        Cancel
                    </button>
                    <button class="btn btn--primary" @click="saveBiometrics">
                        {{ importing
                            ? 'Importing…'
                            : editingAttendance
                                ? 'Save changes'
                                : 'Import biometrics'
                        }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>

import { useAttendanceStore } from '@/stores/useAttendance';
import { useEmployeeStore } from '@/stores/useEmployee';
import { storageImage } from '@/utils/image';
import { showConfirm } from '@/utils/Swals';
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

const attendanceStore = useAttendanceStore();
const employeeStore = useEmployeeStore();

const liveClock = ref('--:--:--')
const selectedBiometricFile = ref(null);
const importing = ref(false);
let clockTimer = null
const employeeNameList = ref([]);

function tickClock() {
    liveClock.value =
        new Date().toLocaleTimeString(
            'en-US',
            {
                hour12: true
            }
        )

}

const formatDate = (date) => {
    if (!date) return '';

    return new Date(
        `${date}T00:00:00`
    ).toLocaleDateString(
        'en-US',
        {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        }
    );
};

const isTimeDisabled = computed(() => {
    return this.attendanceForm.status === 'Absent' || this.attendanceForm.status === 'Leave';
});

const statusKey = (status) => {
    return (status || '').toLowerCase().replace(' ', '');
};

const initials = (name) => {
    if (!name) return '';
    return name.trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase();
}

const today = new Date()

const endDate = ref(formatLocalDate(today))

const start = new Date(today)
start.setDate(today.getDate() - 6)
const startDate = ref(formatLocalDate(start))

function formatLocalDate(date) {
    return date.toLocaleDateString('en-CA')
}

const formattedSelectedDate = computed(() => {

    if (!startDate.value && !endDate.value) {
        return ''
    }

    const formatDate = (dateString) => {

        if (!dateString) {
            return ''
        }

        const date = new Date(
            `${dateString}T00:00:00`
        )

        return date.toLocaleDateString(
            'en-US',
            {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            }
        )
    }

    if (startDate.value && !endDate.value) {
        return formatDate(startDate.value)
    }

    if (!startDate.value && endDate.value) {
        return formatDate(endDate.value)
    }

    if (startDate.value === endDate.value) {
        return formatDate(startDate.value)
    }

    return `${formatDate(startDate.value)} - ${formatDate(endDate.value)}`
})

const handleBiometricFile = async (event) => {
    const file = event.target.files[0];

    if (!file) {
        selectedBiometricFile.value = null;
        return;
    }

    selectedBiometricFile.value = file;

    await getAttendance();
};

const attendanceData = ref([])

const searchQuery = ref('')

const statusFilter = ref('all')

const statusFilters = [

    {
        key: 'all',
        label: 'All'
    },

    {
        key: 'Present',
        label: 'Present'
    },

    {
        key: 'Late',
        label: 'Late'
    },

    {
        key: 'On Leave',
        label: 'On Leave'
    },

    {
        key: 'Absent',
        label: 'Absent'
    }

]

const recordsForSelectedDate = computed(() => {

    return attendanceData.value.filter(
        record => {

            if (
                !startDate.value ||
                !endDate.value
            ) {
                return true
            }

            return (
                record.date >= startDate.value &&
                record.date <= endDate.value
            )
        }
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
            record.phoneNumber
                .toLowerCase()
                .includes(search)


        const matchesStatus =
            statusFilter.value === 'all' ||
            record.status === statusFilter.value


        return matchesSearch && matchesStatus

    })

})

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

watch(
    [
        startDate,
        endDate,
        searchQuery,
        statusFilter,
        pageSize,
    ],
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


const totalEmployeeCount = computed(() => {

    return attendanceData.value.length

})


const presentCount = computed(() => {

    return recordsForSelectedDate.value.filter(
        record =>
            record.status === 'Present'
    ).length

})


const onTimeCount = computed(() => presentCount.value)

const lateCount = computed(() => {

    return recordsForSelectedDate.value.filter(
        record =>
            record.status === 'Late'
    ).length

})

const absentCount = computed(() => {

    return recordsForSelectedDate.value.filter(
        record =>
            record.status === 'Absent'
    ).length

})


const leaveCount = computed(() => {

    return recordsForSelectedDate.value.filter(
        record =>
            record.status === 'Leave'
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

const showModal = ref(false)

const editingAttendance = ref(false)


const attendanceForm = ref(
    createEmptyForm()
)


function createEmptyForm() {
    return {
        id: null,

        employeeId: '',

        date: startDate.value || '',

        timeIn: '',

        timeOut: '',

        overtimeHours: 0,

        status: 'Present',

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

function openAddModal() {

    editingAttendance.value = false

    attendanceForm.value = createEmptyForm()

    showModal.value = true

}

function openEditModal(record) {

    editingAttendance.value = true

    attendanceForm.value = {
        id: record.id,
        employeeId: record.employeeId,
        image: record.image,
        employeeName: record.employeeName,
        date: record.date,
        timeIn: record.timeIn,
        timeOut: record.timeOut,
        before_noon_in: record.amTimeIn,
        before_noon_out: record.amTimeOut,
        after_noon_in: record.pmTimeIn,
        after_noon_out: record.pmTimeOut,
        overtime_in: record.overIn,
        overtime_out: record.overOut,
        overtimeHours: record.overtimeHours,
        status: record.status,
        notes: record.notes
    }
    showModal.value = true
}

function closeModal() {
    showModal.value = false
}

const toHMS = (value) => (value && value.length === 5 ? `${value}:00` : value);

const saveBiometrics = async () => {
    if (editingAttendance.value) {
        await attendanceStore.updateAttendance({
            ...attendanceForm.value,
            before_noon_in: toHMS(attendanceForm.value.before_noon_in),
            before_noon_out: toHMS(attendanceForm.value.before_noon_out),
            after_noon_in: toHMS(attendanceForm.value.after_noon_in),
            after_noon_out: toHMS(attendanceForm.value.after_noon_out),
            overtime_in: toHMS(attendanceForm.value.overtime_in),
            overtime_out: toHMS(attendanceForm.value.overtime_out),
        });
        await getAttendance();
        closeModal()
    } else {
        const formData = new FormData();
        formData.append('file', selectedBiometricFile.value);
        await attendanceStore.importBiometrics(formData);
        await getAttendance();
        closeModal()
    }
}

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

const handleRemoveTime = async (fieldKey) => {
    const confirm = await showConfirm(
        'Cancel Time',
        'Do you wish to remove this time TAP via Biometric?',
        'Yes. Please'
    );

    if (confirm) {
        attendanceForm.value[fieldKey] = '';
    }
}

const formatTime = (time) => {
    if (!time) return '';

    const [hours, minutes] = time.split(':');

    const date = new Date();

    date.setHours(
        Number(hours),
        Number(minutes),
        0
    );

    return date.toLocaleTimeString(
        'en-US',
        {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        }
    );
};

const getAttendance = async () => {
    const lists = await attendanceStore.attendances();
    attendanceData.value = lists.data;
}

watch(
    () => attendanceForm.value?.before_noon_in,
    (timeIn) => {
        if (!editingAttendance.value || !timeIn) return

        const [hours, minutes] = timeIn.split(':').map(Number)

        if ((hours === 8 && minutes >= 1) || (hours > 8 && hours < 12)) {
            attendanceForm.value.status = 'Late'
        } else if (hours < 8 || (hours === 8 && minutes === 0)) {
            attendanceForm.value.status = 'Present'
        }
    }
)

const employeelists = async () => {
    const employees = await employeeStore.allEmployees();

    const mapping = employees.data.data.map(ar => ({
        value: ar.name,
        label: ar.name,
    }));

    employeeNameList.value = [
        {
            value: '',
            label: 'All',
        },
        ...mapping
    ]
}

onMounted(async () => {

    tickClock()

    clockTimer =
        setInterval(
            tickClock,
            1000
        )
    await getAttendance();
    await employeelists();
})

onBeforeUnmount(() => {

    clearInterval(
        clockTimer
    )

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

.avatar-md {

    width:
        100px;

    height:
        100px;

    margin-bottom: 10px;

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

.avatar-md img {

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


.phoneNumber {

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
        9;
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

.import-section {
    padding: 1.25rem;
    border: 1px solid var(--border, #E4E1D8);
    border-radius: 12px;
    background: var(--surface, #FAF9F5);
}

.import-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--ink, #1F1D1A);
    margin-bottom: 0.5rem;
}

.file-input {
    display: block;
    width: 100%;
    font-size: 0.875rem;
    color: var(--slate, #6B6455);
    padding: 0.5rem 0;
}

.file-input::file-selector-button {
    padding: 0.5rem 1rem;
    margin-right: 0.75rem;
    border: 1px solid var(--border, #E4E1D8);
    border-radius: 8px;
    background: #FFFFFF;
    color: var(--ink, #1F1D1A);
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}

.file-input::file-selector-button:hover {
    background: var(--surface-hover, #F1EEE5);
    border-color: var(--gold, #C9A24B);
}

.selected-file {
    margin-top: 0.75rem;
    padding: 0.625rem 0.875rem;
    border-radius: 8px;
    background: var(--surface-hover, #F1EEE5);
    font-size: 0.8125rem;
    color: var(--ink, #1F1D1A);
}

.selected-file strong {
    font-weight: 600;
    margin-right: 0.375rem;
}

.import-description {
    margin-top: 0.75rem;
    font-size: 0.8125rem;
    color: var(--slate, #6B6455);
    line-height: 1.5;
}

.att-modal {
    --ink: #1e1b16;
    --muted: #7a7469;
    --paper: #ffffff;
    --line: #e6e2da;
    --canvas: #faf9f7;
    --am: #b8791f;
    --am-tint: #fbf1de;
    --pm: #35577f;
    --pm-tint: #e9f0f7;
    --ot: #1f7a66;
    --ot-tint: #e4f3ef;
    --present: #2e7d53;
    --late: #b8791f;
    --halfday: #8a6d3b;
    --absent: #b4423a;
    --leave: #6c5b92;
    font-family: 'Inter', -apple-system, 'Segoe UI', sans-serif;
    color: var(--ink);
    background: var(--paper);
    width: min(660px, 92vw);
    max-height: 90vh;
    overflow-y: auto;
    border-radius: 12px;
    box-shadow: 0 24px 60px rgba(30, 27, 22, 0.22);
}

.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(30, 27, 22, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

/* ---- header ---- */
.att-modal__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--line);
}

.att-modal__heading {
    display: flex;
    gap: 10px;
    align-items: flex-start;
}

.att-modal__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-top: 7px;
    flex-shrink: 0;
}

.att-modal__dot.is-new {
    background: var(--present);
}

.att-modal__dot.is-editing {
    background: var(--am);
}

.att-modal__title {
    font-size: 17px;
    font-weight: 650;
    letter-spacing: -0.01em;
}

.att-modal__sub {
    font-size: 13px;
    color: var(--muted);
    margin-top: 2px;
}

.att-modal__close {
    border: none;
    background: transparent;
    color: var(--muted);
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.att-modal__close:hover {
    background: var(--canvas);
    color: var(--ink);
}

/* ---- body ---- */
.att-modal__body {
    padding: 20px 24px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.form-group {
    margin-bottom: 14px;
}

.form-group label {
    display: block;
    font-size: 12.5px;
    font-weight: 500;
    color: var(--muted);
    margin-bottom: 5px;
}

.form-control {
    width: 100%;
    border: 1px solid var(--line);
    border-radius: 7px;
    padding: 8px 10px;
    font-size: 14px;
    font-family: inherit;
    color: var(--ink);
    background: var(--paper);
    transition: border-color 0.15s;
}

.form-control:focus {
    outline: none;
    border-color: var(--pm);
    box-shadow: 0 0 0 3px rgba(53, 87, 127, 0.12);
}

.form-control:disabled {
    background: var(--canvas);
    color: var(--muted);
}

/* ---- employee identity ---- */
.employee-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}

.avatar-md {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--canvas);
    border: 1px solid var(--line);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 650;
    color: var(--muted);
    overflow: hidden;
}

.avatar-md img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.employee-row__name {
    font-size: 14.5px;
    font-weight: 650;
}

.employee-row__id {
    font-size: 12.5px;
    color: var(--muted);
}

/* ---- status select ---- */
.status-select select {
    font-weight: 550;
}

.status-select.is-present select {
    color: var(--present);
}

.status-select.is-late select {
    color: var(--late);
}

.status-select.is-halfday select {
    color: var(--halfday);
}

.status-select.is-absent select {
    color: var(--absent);
}

.status-select.is-leave select {
    color: var(--leave);
}

/* ---- shift sections (color = time of day, not decoration) ---- */
.shift-section {
    border-left: 3px solid transparent;
    border-radius: 0 8px 8px 0;
    padding: 12px 14px 2px;
    margin-bottom: 12px;
}

.shift-section__label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 10px;
}

.shift-section--am {
    background: var(--am-tint);
    border-left-color: var(--am);
}

.shift-section--am .shift-section__label {
    color: var(--am);
}

.shift-section--pm {
    background: var(--pm-tint);
    border-left-color: var(--pm);
}

.shift-section--pm .shift-section__label {
    color: var(--pm);
}

.shift-section--ot {
    background: var(--ot-tint);
    border-left-color: var(--ot);
}

.shift-section--ot .shift-section__label {
    color: var(--ot);
}

/* ---- summary strip ---- */
.summary-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--canvas);
    border: 1px solid var(--line);
    border-radius: 8px;
    padding: 14px 16px;
    margin-top: 4px;
    flex-wrap: wrap;
    gap: 10px;
}

.summary-strip__label {
    font-size: 11.5px;
    color: var(--muted);
    font-weight: 500;
}

.summary-strip__value {
    font-size: 24px;
    font-weight: 650;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.02em;
}

.summary-strip__value span {
    font-size: 13px;
    font-weight: 500;
    color: var(--muted);
}

.summary-strip__breakdown {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 12px;
    color: var(--muted);
    font-variant-numeric: tabular-nums;
}

.summary-strip__breakdown span {
    display: flex;
    align-items: center;
    gap: 6px;
}

.dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}

.dot--am {
    background: var(--am);
}

.dot--pm {
    background: var(--pm);
}

.dot--ot {
    background: var(--ot);
}

/* ---- import panel ---- */
.import-panel__label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

.import-panel__drop {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1.5px dashed var(--line);
    border-radius: 9px;
    padding: 18px 16px;
    color: var(--muted);
    font-size: 13.5px;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
}

.import-panel__drop:hover {
    border-color: var(--pm);
    background: var(--pm-tint);
    color: var(--pm);
}

.import-panel__input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.import-panel__hint {
    font-size: 12px;
    color: var(--muted);
    margin-top: 8px;
}

/* ---- footer ---- */
.att-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px 20px;
    border-top: 1px solid var(--line);
}

.btn {
    border-radius: 7px;
    padding: 9px 18px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid transparent;
}

.btn--ghost {
    background: transparent;
    border-color: var(--line);
    color: var(--ink);
}

.btn--ghost:hover {
    background: var(--canvas);
}

.btn--primary {
    background: var(--ink);
    color: var(--paper);
}

.btn--primary:hover {
    background: #34302a;
}

@media (prefers-reduced-motion: reduce) {

    .form-control,
    .import-panel__drop,
    .btn {
        transition: none;
    }
}

.label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.input-label {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    color: #6b7280;
}

.btn-clear-time {
    background: #fee2e2;
    color: #991b1b;
    border: none;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    padding: 2px 6px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-clear-time:hover {
    background: #fecaca;
    color: #7f1d1d;
}

.search-box,
.custom-select,
.add-btn {
    height: 40px;
    font-size: 14px;
    border-radius: 8px;
    box-sizing: border-box;
}

.search-box {
    position: relative;
    display: flex;
    align-items: center;
    min-width: 220px;
}

.search-box svg {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}

.search-box input {
    width: 100%;
    height: 100%;
    padding: 0 12px 0 26px;
    border-radius: 8px;
    color: #1e293b;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.select-wrapper {
    position: relative;
    min-width: 180px;
}

.custom-select {
    width: 100%;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-color: #ffffff;
    border: 1px solid #cbd5e1;
    padding: 0 36px 0 14px;
    color: #1e293b;
    cursor: pointer;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.custom-select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}

.select-arrow {
    position: absolute;
    top: 50%;
    right: 14px;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #64748b;
    pointer-events: none;
    transition: transform 0.2s ease;
}

.select-wrapper:focus-within .select-arrow {
    transform: translateY(-50%) rotate(180deg);
}

/* 3. Action Button */
.add-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 16px;
    background-color: #6366f1;
    color: #ffffff;
    border: none;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.1s ease;
    white-space: nowrap;
}

.add-btn:hover {
    background-color: #4f46e5;
}

.add-btn:active {
    transform: scale(0.98);
}
</style>