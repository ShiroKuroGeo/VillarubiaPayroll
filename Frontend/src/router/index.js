import { createRouter, createWebHistory } from 'vue-router'

import Login from '@/pages/LoginView.vue'
import AdminLayout from '@/layout/AdminLayout.vue'
import Dashboard from '@/pages/DashboardView.vue'
import Attendance from '@/pages/AttendanceView.vue'
import Payroll from '@/pages/PayrollView.vue'
import CashAdvanceView from '@/pages/CashAdvanceView.vue'
import setting from '@/pages/Setting.vue'

import EmployeeManagement from '@/pages/Managements/EmployeeManagement.vue'
import PayrollManagement from '@/pages/Managements/PayrollManagement.vue'
import CashAdvanceManagement from '@/pages/Managements/CashAdvanceManagement.vue'
import AttendanceManagement from '@/pages/Managements/AttendanceManagement.vue'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),

    routes: [
        {
            path: '/',
            name: 'login',
            component: Login,
        },
        {
            path: '/ca',
            name: 'cashAdvance',
            component: CashAdvanceView,
        },

        {
            path: '/admin',
            component: AdminLayout,
            // meta: {
            //     requiresAuth: true,
            // },

            children: [
                {
                    path: '',
                    name: 'admin.dashboard',
                    component: Dashboard,
                },
                {
                    path: 'attendance',
                    name: 'admin.attendance',
                    component: Attendance,
                },
                {
                    path: 'payroll',
                    name: 'admin.payroll',
                    component: Payroll,
                },
                {
                    path: 'management/employee',
                    name: 'admin.employeeManagement',
                    component: EmployeeManagement,
                },
                {
                    path: 'management/payroll',
                    name: 'admin.payrollManagement',
                    component: PayrollManagement,
                },
                {
                    path: 'management/cashAdvance',
                    name: 'admin.cashAdvanceManagement',
                    component: CashAdvanceManagement,
                },
                {
                    path: 'management/attendance',
                    name: 'admin.attendanceManagement',
                    component: AttendanceManagement,
                },
                {
                    path: 'setting',
                    name: 'admin.setting',
                    component: setting,
                },
            ],
        },
    ],
})

router.beforeEach((to) => {
    const isAuthenticated =
        localStorage.getItem('admin_authenticated') === 'true'

    if (to.meta.requiresAuth && !isAuthenticated) {
        return {
            name: 'login',
        }
    }

    if (to.name === 'login' && isAuthenticated) {
        return {
            name: 'admin.dashboard',
        }
    }
})

export default router
