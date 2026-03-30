<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name:   props.user.name,
    email:  props.user.email,
    status: props.user.status,
});

function submit() {
    form.patch(route('users.update', props.user.id));
}
</script>

<template>
    <Head title="Edit User" />
    <AppLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Edit User</h1>
        </template>

        <div class="max-w-lg">
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input v-model="form.name" type="text" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="form.email" type="email" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select v-model="form.status" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <a :href="route('users.index')" class="flex-1 text-center border border-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" :disabled="form.processing" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-indigo-700 disabled:opacity-50">
                            {{ form.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
