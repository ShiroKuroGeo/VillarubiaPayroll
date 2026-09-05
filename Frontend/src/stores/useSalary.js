import api from "@/api/axios";
import { showStatusAlert } from "@/utils/Swals";
import { defineStore } from "pinia";

export const useSalaryStore = defineStore('salaryStore', () => {

    const createSalary = async (data) => {
        try {
            const createSalary = await api.post('salary/create', data);

            await showStatusAlert(createSalary.status, createSalary.data.message);
            return createSalary.data;
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

    const updateSalary = async (data) => {
        try {
            const updateSalary = await api.post('salary/update', data);

            await showStatusAlert(updateSalary.status, updateSalary.data.message);
            
            return updateSalary.data;
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

    const listSalaries = async () => {
        try {
            const listSalaries = await api.get('salary/list');

            return listSalaries.data;
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

    const reviewSalary = async (data) => {
        try {
            const reviewSalary = await api.post('salary/review', data);

            return reviewSalary.data;
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

    return { createSalary, updateSalary, listSalaries, reviewSalary }

});