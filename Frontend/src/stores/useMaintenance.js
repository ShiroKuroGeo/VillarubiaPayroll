import api from "@/api/axios";
import { showStatusAlert } from "@/utils/Swals";
import { defineStore } from "pinia";
import { ref } from "vue";

export const useMaintenanceStore = defineStore('maintenanceStore', () => {

    const maintenances = ref([]);

    const maintenanceList = async () => {
        try {
            const response = await api.get('maintenance/maintenance_list');
            maintenances.value = response.data.data
            return response.data.data
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

    const updateSettings = async (data) => {
        try {
            const response = await api.post('maintenance/update', data);

            await showStatusAlert(response.status, response.data.message);

            return response.data.data
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

    return { maintenanceList, maintenances, updateSettings }
});