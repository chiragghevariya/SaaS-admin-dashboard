<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    plan_name:  String,
    plan_price: Number,
});
</script>

<template>
    <Head title="Thank You" />
    <AppLayout>
        <div class="max-w-lg mx-auto py-16 text-center">

            <!-- Success icon -->
            <div class="w-20 h-20 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <!-- Heading -->
            <h1 class="text-3xl font-bold text-gray-900 mb-3">You're all set!</h1>
            <p class="text-gray-500 mb-2">
                Thank you for subscribing
                <template v-if="plan_name">
                    to the <span class="font-semibold text-gray-700">{{ plan_name }}</span> plan
                    <template v-if="plan_price"> (${{ plan_price }}/mo)</template>
                </template>.
            </p>
            <p class="text-gray-400 text-sm mb-10">
                Your subscription is now active. You can manage it at any time from the billing portal.
            </p>

            <!-- CTA -->
            <Link
                :href="route('billing.portal')"
                class="inline-flex items-center gap-2 bg-indigo-600 text-white px-8 py-3 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors"
            >
                Go to Billing Portal
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </Link>
        </div>
    </AppLayout>
</template>
