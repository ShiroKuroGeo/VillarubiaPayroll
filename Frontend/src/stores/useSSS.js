import api from "@/api/axios";
import { showStatusAlert } from "@/utils/Swals";
import { defineStore } from "pinia";

export const useSSSStore = defineStore('useSSS', () => {

    const createSSSContribution = async (data) => {
        try {
            const createSSS = await api.post('sss/create', data)

            await showStatusAlert(createSSS.status, createSSS.data.message);

            return createSSS.data;
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

    const getSSSDeductionsRecords = async (data) => {
        try {
            const getSSSDeductionsRecords = await api.post('sss/getRecords', data)

            return getSSSDeductionsRecords.data;
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

    const getSSSDeductionsHistory = async (data) => {
        try {
            const getSSSDeductionsHistory = await api.post('sss/getHistory', data)

            return getSSSDeductionsHistory.data;

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

    const removeSSSDeduction = async (data) => {
        try {
            const removeSSSDeduct = await api.post('sss/removeSSS', data)

            return removeSSSDeduct.data;
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

    const updateSSSDeduction = async (data) => {
        try {
            const updateSSSDeduct = await api.post('sss/updateSSS', data)

            await showStatusAlert(updateSSSDeduct.status, updateSSSDeduct.data.message);
            return updateSSSDeduct.data;
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

    return { createSSSContribution, getSSSDeductionsRecords, getSSSDeductionsHistory, removeSSSDeduction, updateSSSDeduction }

});