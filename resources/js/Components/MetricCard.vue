<script setup>
defineProps({
    label:   { type: String, required: true },
    value:   { type: [String, Number], required: true },
    trend:   { type: String, default: null },
    trendUp: { type: Boolean, default: true },
    icon:    { type: String, default: null },
    prefix:  { type: String, default: '' },
    suffix:  { type: String, default: '' },
});
</script>

<template>
    <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ label }}</p>
            <span v-if="icon" class="text-2xl">{{ icon }}</span>
        </div>
        <div class="mt-2 flex items-end justify-between">
            <p class="text-3xl font-bold text-gray-900">
                <span class="text-xl font-semibold text-gray-400">{{ prefix }}</span>{{ value }}<span class="text-xl font-semibold text-gray-400">{{ suffix }}</span>
            </p>
            <span
                v-if="trend"
                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                :class="trendUp ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'"
            >
                {{ trendUp ? '▲' : '▼' }} {{ trend }}
            </span>
        </div>
    </div>
</template>
