import api from "@/api/axios";
import { showStatusAlert } from "@/utils/Swals";
import { defineStore } from "pinia";

export const useCashAdvanceStore = defineStore('cashAdvanceStore', () => {

    const createCashAdvance = async (data) => {
        try {

            const createCashAdvance = await api.post('cash_advance/create', data);

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

    // employee_id
    // status
    // per_page

    const getCashAdvances = async (data) => {
        try {

            const listCashAdvance = await api.post('cash_advance/list', data);

            return listCashAdvance.data.data;

        } catch (err) {
            const status = err.response?.status || 500;

            const message =
                err.response?.data?.message ||
                err.response?.data?.error ||
                err.message ||
                'An unexpected error occurred.';

            showStatusAlert(status, message);

            return message;
        }
    }

    const reviewCashAdvance = async (data) => {
        try {
            const reviewRequest = await api.post('cash_advance/review', data);
            await showStatusAlert(reviewRequest.status, reviewRequest.data.message);
            return reviewRequest.data.data;

        } catch (err) {
            const status = err.response?.status || 500;

            const message =
                err.response?.data?.message ||
                err.response?.data?.error ||
                err.message ||
                'An unexpected error occurred.';

            showStatusAlert(status, message);

            return message;
        }
    }

    const attachToPayroll = async (data) => {
        try {
            // attachToPayroll
        } catch (err) {
            const status = err.response?.status || 500;

            const message =
                err.response?.data?.message ||
                err.response?.data?.error ||
                err.message ||
                'An unexpected error occurred.';

            showStatusAlert(status, message);

            return message;
        }
    }

    return { createCashAdvance, getCashAdvances, reviewCashAdvance, attachToPayroll }


});