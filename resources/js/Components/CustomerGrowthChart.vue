<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: { type: Array, required: true }, // [{month, new, churned}]
});

const series = computed(() => [
    { name: 'New Customers',     data: props.data.map(d => d.new) },
    { name: 'Churned Customers', data: props.data.map(d => d.churned) },
]);

const chartOptions = computed(() => ({
    chart: {
        type: 'bar',
        height: 240,
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    plotOptions: {
        bar: { borderRadius: 4, columnWidth: '50%' },
    },
    dataLabels: { enabled: false },
    colors: ['#6366f1', '#f87171'],
    xaxis: {
        categories: props.data.map(d => d.month),
        axisBorder: { show: false },
        axisTicks:  { show: false },
        labels: { style: { colors: '#9ca3af', fontSize: '12px' } },
    },
    yaxis: {
        labels: { style: { colors: '#9ca3af', fontSize: '12px' } },
    },
    grid: {
        borderColor: '#f3f4f6',
        strokeDashArray: 4,
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        labels: { colors: '#6b7280' },
    },
    tooltip: {
        y: { formatter: (val) => val + ' customers' },
    },
}));
</script>

<template>
    <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Customer Growth</h3>
        <apexchart type="bar" height="240" :options="chartOptions" :series="series" />
    </div>
</template>
