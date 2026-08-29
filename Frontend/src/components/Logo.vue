<template>
    <div class="brand ">
        <div class="brand-mark">
            A&P
        </div>
        <div>
            <div class="brand-name">
                Villarubia A&P
            </div>
            <div class="brand-sub">
                Time Attendance & Payroll
            </div>
        </div>
    </div>
</template>

<style scoped>
.brand {
    display: flex;
    align-items: center;

    gap: 12px;

    margin-bottom: 40px;
}

.brand-mark {
    width: 40px;
    height: 40px;
    font-size: 12px;
    border-radius: 9px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: linear-gradient(155deg,
            #C79A3D,
            #9C7726);

    color: #1C2B4A;

    font-family: Georgia, serif;

    font-weight: 700;
}

.brand-name {
    font-family: Georgia, serif;

    font-size: 20px;
    font-weight: 600;

    color: #1C2B4A;
}

.brand-sub {
    font-size: 10px;

    letter-spacing: .12em;

    text-transform: uppercase;

    color: #6B7280;
}
</style>