<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Settings</h1>
        </template>

        <div class="max-w-3xl space-y-6">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AppLayout>
</template>
