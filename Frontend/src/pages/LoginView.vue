<template>
    <div class="login-page">
        <div class="login-card">
            <Logo />
            <div class="login-header">
                <h1>Welcome back</h1>
                <p>Sign in to your administrator account.</p>
            </div>
            <form @submit.prevent="login">
                <div class="form-group">
                    <label>Email</label>
                    <input v-model="email" type="email" placeholder="example@example.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input v-model="password" type="password" placeholder="••••••••" required>
                </div>
                <div v-if="error" class="error-message">
                    {{ error }}
                </div>
                <button type="submit" class="login-button">
                    Sign in
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import Logo from '@/components/Logo.vue'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/useUser'
import { showStatusAlert, showSuccess } from '@/utils/Swals'

const router = useRouter()
const userStore = useUserStore();

const email = ref('')
const password = ref('')
const error = ref('')

const login = async () => {
    const login_user = await userStore.login({
        'email': email.value,
        'password': password.value,
    });

    if(login_user.data.role === 'admin') {
       router.push({ name: 'admin.dashboard' });
       return;
    } else {
        showStatusAlert(409, 'You have no access in administration dashboard.');
    }
}

</script>

<style scoped>
.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #F2F1EA;
    padding: 24px;
}

.login-card {
    width: 100%;
    max-width: 420px;
    background: #FBFAF6;
    border: 1px solid #DCD8CB;
    border-radius: 12px;
    padding: 40px;
}

.login-header {
    margin-bottom: 28px;
}

.login-header h1 {
    margin: 0 0 6px;
    font-family: Georgia, serif;
    font-size: 30px;
    color: #1C2B4A;
}

.login-header p {
    margin: 0;
    color: #6B7280;
    font-size: 14px;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    color: #28395E;
    font-size: 13px;
    font-weight: 600;
}

.form-group input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #DCD8CB;
    border-radius: 7px;
    background: #fff;
    color: #1C2B4A;
    outline: none;
}

.form-group input:focus {
    border-color: #C79A3D;
    box-shadow: 0 0 0 3px rgba(199, 154, 61, .12);
}

.login-button {
    width: 100%;
    padding: 12px;
    margin-top: 8px;
    border: 1px solid #1C2B4A;
    border-radius: 7px;
    background: #1C2B4A;
    color: #F3DFA6;
    font-weight: 600;
    cursor: pointer;
}

.login-button:hover {
    background: #28395E;
    color: white;
}

.error-message {
    padding: 10px 12px;
    margin-bottom: 14px;
    border-radius: 6px;
    background: #F7E9E6;
    color: #C24D3B;
    font-size: 13px;
}
</style>