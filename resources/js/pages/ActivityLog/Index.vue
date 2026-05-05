<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Search, X } from 'lucide-vue-next';

interface ActivityLog {
    id: number;
    user_id: number | null;
    user_name: string;
    user_role: string;
    action: 'created' | 'updated' | 'deleted';
    module: string;
    description: string;
    ip_address: string | null;
    created_at: string;
}

interface PaginatedLogs {
    data: ActivityLog[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Filters {
    search?: string;
    module?: string;
    action?: string;
}

const props = defineProps<{
    logs: PaginatedLogs;
    filters: Filters;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activity Log', href: '/activity-log' },
];

const search = ref(props.filters.search ?? '');
const module = ref(props.filters.module ?? '');
const action = ref(props.filters.action ?? '');

function applyFilters() {
    router.get('/activity-log', {
        search: search.value  || undefined,
        module: module.value  || undefined,
        action: action.value  || undefined,
    }, { preserveState: true, replace: true });
}

function clearFilters() {
    search.value = '';
    module.value = '';
    action.value = '';
    router.get('/activity-log', {}, { preserveState: false });
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

const actionStyles: Record<string, string> = {
    created: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    updated: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    deleted: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
};

const moduleLabels: Record<string, string> = {
    'users':            'Users',
    'files':            'Files',
    'employment-types': 'Employment Types',
    'locations':        'Locations',
    'settings':         'Settings',
};

const roleStyles: Record<string, string> = {
    admin: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    staff: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Activity Log" />

        <div class="flex flex-col gap-6 p-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Activity Log</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ logs.total }} total records
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <!-- Search -->
                <div class="relative flex-1 min-w-[200px]">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search description or user..."
                        class="w-full rounded-lg border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-400"
                        @keyup.enter="applyFilters"
                    />
                </div>

                <!-- Module filter -->
                <select
                    v-model="module"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                    @change="applyFilters"
                >
                    <option value="">All Modules</option>
                    <option value="users">Users</option>
                    <option value="files">Files</option>
                    <option value="employment-types">Employment Types</option>
                    <option value="locations">Locations</option>
                    <option value="settings">Settings</option>
                </select>

                <!-- Action filter -->
                <select
                    v-model="action"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                    @change="applyFilters"
                >
                    <option value="">All Actions</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                </select>

                <!-- Clear button -->
                <button
                    v-if="filters.search || filters.module || filters.action"
                    @click="clearFilters"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                >
                    <X class="h-4 w-4" />
                    Clear
                </button>
            </div>

            <!-- Table -->
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60">
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">User</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Action</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Module</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Description</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">IP Address</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-if="logs.data.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-gray-400 dark:text-gray-500">
                                No activity logs found.
                            </td>
                        </tr>
                        <tr
                            v-for="log in logs.data"
                            :key="log.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors"
                        >
                            <!-- User -->
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ log.user_name }}</div>
                                <span
                                    :class="['inline-block rounded-full px-2 py-0.5 text-xs font-medium mt-0.5', roleStyles[log.user_role] ?? 'bg-gray-100 text-gray-600']"
                                >
                                    {{ log.user_role }}
                                </span>
                            </td>

                            <!-- Action -->
                            <td class="px-4 py-3">
                                <span
                                    :class="['inline-block rounded-full px-2.5 py-0.5 text-xs font-medium capitalize', actionStyles[log.action] ?? 'bg-gray-100 text-gray-600']"
                                >
                                    {{ log.action }}
                                </span>
                            </td>

                            <!-- Module -->
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                                    {{ moduleLabels[log.module] ?? log.module }}
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300 max-w-xs">
                                <div class="space-y-0.5">
                                    <template v-for="(part, index) in log.description.split(' | ')" :key="index">
                                        <div :class="index === 0 ? 'font-medium text-gray-900 dark:text-white' : 'text-xs text-gray-500 dark:text-gray-400'">
                                            {{ part }}
                                        </div>
                                    </template>
                                </div>
                            </td>

                            <!-- IP Address -->
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs">
                                {{ log.ip_address ?? '—' }}
                            </td>

                            <!-- Date -->
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">
                                {{ formatDate(log.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="logs.last_page > 1" class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                <span>Page {{ logs.current_page }} of {{ logs.last_page }}</span>
                <div class="flex gap-1">
                    <Link
                        v-for="link in logs.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        :class="[
                            'px-3 py-1.5 rounded-md transition-colors',
                            link.active ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700',
                            !link.url ? 'pointer-events-none opacity-40' : '',
                        ]"
                        v-html="link.label"
                        preserve-scroll
                    />
                </div>
            </div>

        </div>
    </AppLayout>
</template>
