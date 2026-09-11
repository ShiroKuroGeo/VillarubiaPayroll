import api from "@/api/axios";
import { showStatusAlert } from "@/utils/Swals";
import { defineStore } from "pinia";

export const usePayrollStore = defineStore('payrollStore', () => {
    const generatePayroll = async () => {
        try {

            const createCashAdvance = await api.post('payroll/generate');

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

    const payrollList = async (data) => {
        try {
            const payrollLists = await api.post('payroll/list', data);

            return payrollLists.data;
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

    async function exportPayslips() {
        try {
            const response = await api.get('payslip/export', {
                responseType: 'blob',
            })

            const blob = new Blob([response.data], {
                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            })

            const url = window.URL.createObjectURL(blob)
            const link = document.createElement('a')
            link.href = url
            link.download = `payslips-${Date.now()}.xlsx`
            document.body.appendChild(link)
            link.click()
            link.remove()
            window.URL.revokeObjectURL(url)
        } catch (err) {
            const status = err.response?.status || 500

            let message = 'An unexpected error occurred.'

            try {

                const errorData = err.response?.data
                if (errorData instanceof Blob) {

                    const text = await errorData.text()

                    const json = JSON.parse(text)

                    message =
                        json.message ||
                        json.error ||
                        message

                } else {

                    message =
                        errorData?.message ||
                        errorData?.error ||
                        err.message ||
                        message

                }

            } catch (parseError) {

                message =
                    err.message ||
                    message
            }

            showStatusAlert(
                status,
                message
            )

            return status
        }
    }

    const updatePayroll = async (data) => {
        try {
            const response = await api.post('payroll/update', data);

            await showStatusAlert(response.status, response.data.message);
            return response.data;

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

    return { generatePayroll, payrollList, exportPayslips, updatePayroll }
});