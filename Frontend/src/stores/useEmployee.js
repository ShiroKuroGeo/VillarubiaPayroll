import api from "@/api/axios";
import { showStatusAlert } from "@/utils/Swals";
import { defineStore } from "pinia";

export const useEmployeeStore = defineStore('employeeStore', () => {

    const jobTypes = async () => {
        try {
            const types = await api.get('employee/list_job_types');

            return types.data.data;
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

    const createEmployee = async (data) => {
        try {

            const formData = new FormData()

            formData.append('job_id', data.job_id)
            formData.append('first_name', data.first_name)
            formData.append('last_name', data.last_name)
            formData.append('email', data.email)
            formData.append('phone_number', data.phone_number)
            formData.append('location', data.location)
            formData.append('status', data.status)
            formData.append('date_hired', data.date_hired)

            if (data.image instanceof File) {
                formData.append('image', data.image)
            }

            const createEmployee = await api.post('employee/create', formData);

            await showStatusAlert(createEmployee.status, createEmployee.data.message);

            return createEmployee.data;
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

    const listEmployee = async (data) => {
        try {
            const listEmployee = await api.post('employee/list', data); 

            return listEmployee.data;
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

    const updateEmployee = async (data) => {
        try {
            const updateEmployee = await api.post('employee/update', data);
            await showStatusAlert(updateEmployee.status, updateEmployee.data.message);
            return updateEmployee.data;
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

    const removeEmployee = async (data) => {
        try {
            const removeEmployee = await api.post('employee/remove', data);
            await showStatusAlert(removeEmployee.status, removeEmployee.data.message);
            return removeEmployee.data;
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

    const allEmployees = async () => {
        try {
            return api.get('employee/all');
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

    return { jobTypes, createEmployee, listEmployee, updateEmployee, removeEmployee, allEmployees }
});