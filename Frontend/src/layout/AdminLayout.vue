<template>
    <div class="app-shell">

        <div v-if="sidebarOpen" class="scrim d-lg-none" @click="sidebarOpen = false"></div>

        <slot name="sidebar" :sidebar-open="sidebarOpen" :active-nav="activeNav"
            :set-active-nav="(k) => (activeNav = k)">
            <AdminSidebar v-model="sidebarOpen" v-model:activeNav="activeNav" />
        </slot>

        <slot name="main" :toggle-sidebar="() => (sidebarOpen = !sidebarOpen)" :active-nav="activeNav">
            <RouterView @toggle-sidebar="sidebarOpen = !sidebarOpen" />
        </slot>

    </div>
</template>


<script setup>

import { ref } from 'vue'

import AdminSidebar from '@/components/Sidebar.vue'


const sidebarOpen = ref(false)

const activeNav = ref('dashboard')

</script>


<style>
:root {
    --ink: #1C2B4A;
    --ink-2: #28395E;
    --ink-soft: #3C4E75;

    --paper: #F2F1EA;
    --paper-2: #FBFAF6;

    --line: #DCD8CB;

    --gold: #C79A3D;
    --gold-dark: #9C7726;

    --green: #2F8F5B;
    --green-bg: #E5F2EA;

    --red: #C24D3B;
    --red-bg: #F7E9E6;

    --amber-bg: #F6EEDB;

    --slate: #6B7280;
    --slate-light: #98A1AD;
}

body {
    margin: 0;
    background: var(--paper);
    font-family: 'Inter', sans-serif;
    color: var(--ink);
}
</style>


<style scoped>
.app-shell {
    display: flex;
    min-height: 100vh;
    position: relative;
}


.scrim {
    position: fixed;
    inset: 0;

    background: rgba(28, 43, 74, .45);

    z-index: 1040;
}


.d-lg-none {
    display: block;
}


@media (min-width: 992px) {

    .d-lg-none {
        display: none;
    }

}
</style>
:::

## Then your router controls the page

Your router should be:

```js
{
path: '/admin',
component: AdminLayout,

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
],
}
