import api from "@/api/axios";
import { showStatusAlert } from "@/utils/Swals";
import { defineStore } from "pinia";

export const usePayrollStore = defineStore('payrollStore', () => {
    const generatePayroll = async () => {
        try {

            const createCashAdvance = await api.post('payroll/generate', data);

            await showStatusAlert(createCashAdvance.status, createCashAdvance.data.message);
            return createCashAdvance;

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

    return { generatePayroll }
});