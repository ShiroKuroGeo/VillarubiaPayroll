import api from "@/api/axios";
import { showStatusAlert } from "@/utils/Swals";
import { defineStore } from "pinia";

export const useOverviewStore = defineStore('useOverview', () => {
    const cardOverview = async (data) => {
        try {
            const overview = await api.post('overview/card_overview', data);

            return overview.data.data;
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

    return {
        cardOverview
    }
});