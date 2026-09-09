import api from "@/api/axios";
import { showStatusAlert } from "@/utils/Swals";
import { defineStore } from "pinia";

export const useAttendanceStore = defineStore('attendanceStore', () => {


    const createAttendace = async (data) => {
        try {
            const saveAttendance = await api.post('attendance/create', data);

            await showStatusAlert(saveAttendance.status, saveAttendance.data.message);
        } catch (err) {
            const status = err.response?.status || 500;

            const message =
                err.response?.data?.message ||
                err.response?.data?.error ||
                err.message ||
                'An unexpected error occurred.';

            showStatusAlert(status, message);

            return status;
        }
    }

    const importBiometrics = async (data) => {
        try {
            const importBiometric = await api.post('attendance/import', data);

            await showStatusAlert(importBiometric.status, importBiometric.data.message);
        } catch (err) {
            const status = err.response?.status || 500;

            const message =
                err.response?.data?.message ||
                err.response?.data?.error ||
                err.message ||
                'An unexpected error occurred.';

            showStatusAlert(status, message);

            return status;
        }
    }

    const attendances = async () => {
        try {
            const attendances = await api.get('attendance/list');

            return attendances.data;
        } catch (err) {
            const status = err.response?.status || 500;

            const message =
                err.response?.data?.message ||
                err.response?.data?.error ||
                err.message ||
                'An unexpected error occurred.';

            showStatusAlert(status, message);

            return status;
        }
    }

    return { importBiometrics, attendances, createAttendace }
});