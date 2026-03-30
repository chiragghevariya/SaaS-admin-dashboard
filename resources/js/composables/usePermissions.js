import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const page = usePage();

    const user = computed(() => page.props.auth.user);
    const roles = computed(() => user.value?.roles ?? []);
    const permissions = computed(() => user.value?.permissions ?? []);

    const hasRole = (role) => roles.value.includes(role);
    const can = (permission) => permissions.value.includes(permission);

    const isAdmin = computed(() => hasRole('admin') || hasRole('super_admin'));
    const isSuperAdmin = computed(() => hasRole('super_admin'));
    const isMember = computed(() => hasRole('member') && !isAdmin.value);

    return { hasRole, can, isAdmin, isSuperAdmin, isMember, roles, permissions };
}
