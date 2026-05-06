<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Search, X, Clock, Monitor, User, Tag, FileText } from 'lucide-vue-next';

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

const selectedLog = ref<ActivityLog | null>(null);
const drawerOpen = ref(false);

function openDrawer(log: ActivityLog) {
    selectedLog.value = log;
    drawerOpen.value = true;
}

function closeDrawer() {
    drawerOpen.value = false;
    setTimeout(() => {
        selectedLog.value = null;
    }, 300);
}

function applyFilters() {
    router.get('/activity-log', {
        search: search.value || undefined,
        module: module.value || undefined,
        action: action.value || undefined,
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

function formatDateFull(dateStr: string) {
    return new Date(dateStr).toLocaleString('en-US', {
        weekday: 'long',
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
    });
}

const actionStyles: Record<string, string> = {
    created: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    updated: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    deleted: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
};

const moduleLabels: Record<string, string> = {
    'users':            'Users',
    'files':            'Folders',
    'employment-types': 'Employment Types',
    'locations':        'Locations',
    'settings':         'Settings',
};

const roleStyles: Record<string, string> = {
    admin: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    staff: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
};

function descriptionParts(description: string) {
    return description.split(' | ');
}
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

                <select
                    v-model="module"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                    @change="applyFilters"
                >
                    <option value="">All Modules</option>
                    <option value="users">Users</option>
                    <option value="files">Folders</option>
                    <option value="employment-types">Employment Types</option>
                    <option value="locations">Locations</option>
                    <option value="settings">Settings</option>
                </select>

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
                <table class="w-full text-sm table-fixed">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60">
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 w-[150px]">User</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 w-[90px]">Action</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 w-[140px]">Module</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Description</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 w-[115px]">IP Address</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 w-[145px]">Date</th>
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
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors cursor-pointer"
                            @click="openDrawer(log)"
                        >
                            <!-- User -->
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white truncate max-w-[130px]">
                                    {{ log.user_name }}
                                </div>
                                <span :class="['inline-block rounded-full px-2 py-0.5 text-xs font-medium mt-0.5', roleStyles[log.user_role] ?? 'bg-gray-100 text-gray-600']">
                                    {{ log.user_role }}
                                </span>
                            </td>

                            <!-- Action -->
                            <td class="px-4 py-3">
                                <span :class="['inline-block rounded-full px-2.5 py-0.5 text-xs font-medium capitalize', actionStyles[log.action] ?? 'bg-gray-100 text-gray-600']">
                                    {{ log.action }}
                                </span>
                            </td>

                            <!-- Module -->
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300 truncate max-w-full">
                                    {{ moduleLabels[log.module] ?? log.module }}
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="px-4 py-3">
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-900 dark:text-white truncate max-w-[220px]">
                                        {{ descriptionParts(log.description)[0] }}
                                    </div>
                                    <div v-if="descriptionParts(log.description).length > 1" class="text-xs text-blue-500 dark:text-blue-400 mt-0.5">
                                        +{{ descriptionParts(log.description).length - 1 }} more — click to view
                                    </div>
                                </div>
                            </td>

                            <!-- IP Address -->
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs truncate">
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

        <!-- Drawer Backdrop -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-300"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="drawerOpen"
                    class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm"
                    @click="closeDrawer"
                />
            </Transition>

            <!-- Drawer Panel -->
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-x-full"
                leave-active-class="transition-transform duration-300 ease-in"
                leave-to-class="translate-x-full"
            >
                <div
                    v-if="drawerOpen && selectedLog"
                    class="fixed right-0 top-0 z-50 h-full w-full max-w-md bg-white dark:bg-gray-900 shadow-2xl flex flex-col"
                >
                    <!-- Drawer Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Log Details</h2>
                        <button
                            @click="closeDrawer"
                            class="rounded-md p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Drawer Body -->
                    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">

                        <!-- Action + Module badges -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <span :class="['inline-block rounded-full px-3 py-1 text-xs font-medium capitalize', actionStyles[selectedLog.action] ?? 'bg-gray-100 text-gray-600']">
                                {{ selectedLog.action }}
                            </span>
                            <span class="inline-block rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                {{ moduleLabels[selectedLog.module] ?? selectedLog.module }}
                            </span>
                        </div>

                        <!-- Performed By -->
                        <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4 space-y-3">
                            <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                                <User class="h-3.5 w-3.5" />
                                Performed By
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-medium text-gray-900 dark:text-white truncate min-w-0 text-sm">
                                    {{ selectedLog.user_name }}
                                </span>
                                <span :class="['inline-block rounded-full px-2.5 py-0.5 text-xs font-medium shrink-0', roleStyles[selectedLog.user_role] ?? 'bg-gray-100 text-gray-600']">
                                    {{ selectedLog.user_role }}
                                </span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4 space-y-3">
                            <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                                <FileText class="h-3.5 w-3.5" />
                                Description
                            </div>
                            <div class="space-y-3">
                                <template v-for="(part, index) in descriptionParts(selectedLog.description)" :key="index">
                                    <!-- Main action title -->
                                    <div v-if="index === 0" class="font-semibold text-gray-900 dark:text-white text-sm break-words">
                                        {{ part }}
                                    </div>
                                    <!-- Detail rows -->
                                    <div v-else class="flex flex-col gap-0.5">
                                        <template v-if="part.includes(': ')">
                                            <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">
                                                {{ part.split(': ')[0] }}
                                            </span>
                                            <span class="text-sm text-gray-700 dark:text-gray-300 break-words">
                                                {{ part.split(': ').slice(1).join(': ') }}
                                            </span>
                                        </template>
                                        <span v-else class="text-sm text-gray-700 dark:text-gray-300 break-words">
                                            {{ part }}
                                        </span>
                                    </div>
                                    <!-- Divider between items -->
                                    <div v-if="index > 0 && index < descriptionParts(selectedLog.description).length - 1" class="border-t border-gray-100 dark:border-gray-700" />
                                </template>
                            </div>
                        </div>

                        <!-- Meta Details -->
                        <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4 space-y-3">
                            <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                                <Tag class="h-3.5 w-3.5" />
                                Details
                            </div>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-gray-500 dark:text-gray-400 flex items-center gap-1.5 shrink-0">
                                        <Monitor class="h-3.5 w-3.5" />
                                        IP Address
                                    </span>
                                    <span class="font-mono text-gray-900 dark:text-white text-xs">
                                        {{ selectedLog.ip_address ?? '—' }}
                                    </span>
                                </div>
                                <div class="border-t border-gray-100 dark:border-gray-700" />
                                <div class="flex items-start justify-between gap-4">
                                    <span class="text-gray-500 dark:text-gray-400 flex items-center gap-1.5 shrink-0">
                                        <Clock class="h-3.5 w-3.5" />
                                        Date & Time
                                    </span>
                                    <span class="text-gray-900 dark:text-white text-xs text-right break-words">
                                        {{ formatDateFull(selectedLog.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </Transition>
        </Teleport>

    </AppLayout>
</template>
