<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: { type: Array, required: true }, // [{month: 'Jan 2025', mrr: 4200}, ...]
});

const series = computed(() => [{
    name: 'MRR',
    data: props.data.map(d => d.mrr),
}]);

const chartOptions = computed(() => ({
    chart: {
        type: 'area',
        height: 280,
        toolbar: { show: false },
        sparkline: { enabled: false },
        fontFamily: 'inherit',
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.05,
            stops: [0, 100],
        },
    },
    colors: ['#6366f1'],
    xaxis: {
        categories: props.data.map(d => d.month),
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: { style: { colors: '#9ca3af', fontSize: '12px' } },
    },
    yaxis: {
        labels: {
            style: { colors: '#9ca3af', fontSize: '12px' },
            formatter: (val) => '$' + val.toLocaleString(),
        },
    },
    grid: {
        borderColor: '#f3f4f6',
        strokeDashArray: 4,
    },
    tooltip: {
        y: { formatter: (val) => '$' + val.toLocaleString() },
    },
}));
</script>

<template>
    <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Monthly Recurring Revenue</h3>
        <apexchart
            type="area"
            height="280"
            :options="chartOptions"
            :series="series"
        />
    </div>
</template>
