<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import MetricCard from '@/Components/MetricCard.vue';
import RevenueChart from '@/Components/RevenueChart.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    total_users:   Number,
    active_users:  Number,
    mrr:           Number,
    churn_rate:    Number,
    revenue_chart: Array,
});

const formatMrr = (val) => val?.toLocaleString('en-US') ?? '0';
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Dashboard</h1>
        </template>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4 mb-6">
            <MetricCard label="Total Users" :value="total_users" icon="👥" />
            <MetricCard label="Active Users" :value="active_users" icon="✅" />
            <MetricCard label="MRR" :value="formatMrr(mrr)" prefix="$" icon="💰" />
            <MetricCard label="Churn Rate" :value="churn_rate" suffix="%" :trend-up="false" icon="📉" />
        </div>

        <RevenueChart :data="revenue_chart" />
    </AppLayout>
</template>
