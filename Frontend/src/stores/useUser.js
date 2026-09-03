import api from "@/api/axios";
import { showStatusAlert } from "@/utils/Swals";
import { defineStore } from "pinia";
import { useRouter } from "vue-router";

export const useUserStore = defineStore('userStore', () => {

    const router = useRouter();

    const login = async (formData) => {
        try {
            const login_user = await api.post('login', formData);

            await showStatusAlert(login_user.status, login_user.data.message);

            localStorage.setItem('auth_token', login_user.data.data.token);

            return login_user.data;
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

    const logout = async () => {
        try {
            await api.post('user/logout');
        } catch (error) {
            console.error('Logout error on backend:', error);
        } finally {
            localStorage.removeItem('auth_token');
            router.push({ name: 'login' });
        }
    };

    return { login, logout }
});