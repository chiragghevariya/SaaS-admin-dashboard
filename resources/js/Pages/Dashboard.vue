<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import MetricCard from '@/Components/MetricCard.vue';
import RevenueChart from '@/Components/RevenueChart.vue';
import CustomerGrowthChart from '@/Components/CustomerGrowthChart.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    total_users:         Number,
    active_users:        Number,
    mrr:                 Number,
    churn_rate:          Number,
    revenue_chart:       Array,
    plan_features:       Object,
    advanced_analytics:  Object,
});

const formatMrr    = (val) => val?.toLocaleString('en-US') ?? '0';
const formatGrowth = (val) => (val >= 0 ? '+' : '') + val + '%';
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Dashboard</h1>
        </template>

        <!-- Basic metrics (all plans) -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4 mb-6">
            <MetricCard label="Total Users"  :value="total_users"        icon="👥" />
            <MetricCard label="Active Users" :value="active_users"       icon="✅" />
            <MetricCard label="MRR"          :value="formatMrr(mrr)"     prefix="$" icon="💰" />
            <MetricCard label="Churn Rate"   :value="churn_rate"         suffix="%" :trend-up="false" icon="📉" />
        </div>

        <RevenueChart :data="revenue_chart" class="mb-6" />

        <!-- ── Advanced Analytics (Pro / Enterprise) ── -->
        <template v-if="plan_features?.advanced_analytics && advanced_analytics">
            <div class="flex items-center gap-2 mb-4 mt-2">
                <h2 class="text-sm font-semibold text-gray-700">Advanced Analytics</h2>
                <span class="text-[10px] font-semibold bg-indigo-100 text-indigo-700 rounded px-1.5 py-0.5 uppercase tracking-wide">Pro</span>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4 mb-6">
                <MetricCard
                    label="Revenue Growth"
                    :value="Math.abs(advanced_analytics.revenue_growth)"
                    suffix="%"
                    :trend="Math.abs(advanced_analytics.revenue_growth) + '%'"
                    :trend-up="advanced_analytics.revenue_growth >= 0"
                    icon="📈"
                />
                <MetricCard
                    label="New Customers"
                    :value="advanced_analytics.new_customers"
                    icon="🆕"
                />
                <MetricCard
                    label="Churned"
                    :value="advanced_analytics.churned_customers"
                    icon="📉"
                    :trend-up="false"
                />
                <MetricCard
                    label="Avg Rev / User"
                    :value="advanced_analytics.avg_revenue_per_user"
                    prefix="$"
                    icon="💡"
                />
            </div>

            <CustomerGrowthChart :data="advanced_analytics.customer_chart" />
        </template>

        <!-- Upgrade prompt for Starter plan -->
        <div v-else-if="plan_features && !plan_features.advanced_analytics" class="mt-6 rounded-xl bg-indigo-50 border border-indigo-100 px-6 py-5 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-900 mb-0.5">Unlock Advanced Analytics</p>
                <p class="text-xs text-indigo-600">Get revenue growth trends, customer acquisition & churn charts, and per-user revenue — available on Pro and above.</p>
            </div>
            <Link :href="route('billing.plans')" class="ml-6 shrink-0 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition-colors">
                Upgrade
            </Link>
        </div>
    </AppLayout>
</template>
