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
    router.get(
        '/activity-log',
        {
            search: search.value || undefined,
            module: module.value || undefined,
            action: action.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function clearFilters() {
    search.value = '';
    module.value = '';
    action.value = '';

    router.get('/activity-log', {}, { preserveState: false });
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatDateFull(dateStr: string) {
    return new Date(dateStr).toLocaleString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
}

const actionStyles: Record<string, string> = {
    created: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    updated: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    deleted: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
};

const moduleLabels: Record<string, string> = {
    users: 'Users',
    files: 'Folders',
    'employment-types': 'Employment Types',
    locations: 'Locations',
    settings: 'Settings',
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

        <div
            class="relative min-h-screen bg-cover bg-center bg-fixed"
            style="background-image: url('/images/landingbg.png')"
        >
            <!-- Dark Overlay -->
            <div class="absolute inset-0 bg-black/40"></div>

            <!-- Main Content -->
            <div class="relative z-10 flex flex-col gap-6 p-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white drop-shadow-md">
                            Activity Log
                        </h1>

                        <p class="mt-1 text-sm text-gray-200">
                            {{ logs.total }} total records
                        </p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap gap-3">
                    <!-- Search -->
                    <div class="relative min-w-[200px] flex-1">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-300"
                        />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search description or user..."
                            class="w-full rounded-xl border border-white/20 bg-white/10 py-2 pr-4 pl-9 text-sm text-white placeholder-gray-300 shadow-lg backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <!-- Module -->
                    <select
                        v-model="module"
                        class="rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white shadow-lg backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        @change="applyFilters"
                    >
                        <option value="" class="text-black">
                            All Modules
                        </option>

                        <option value="users" class="text-black">
                            Users
                        </option>

                        <option value="files" class="text-black">
                            Folders
                        </option>

                        <option value="employment-types" class="text-black">
                            Employment Types
                        </option>

                        <option value="locations" class="text-black">
                            Locations
                        </option>

                        <option value="settings" class="text-black">
                            Settings
                        </option>
                    </select>

                    <!-- Action -->
                    <select
                        v-model="action"
                        class="rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white shadow-lg backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        @change="applyFilters"
                    >
                        <option value="" class="text-black">
                            All Actions
                        </option>

                        <option value="created" class="text-black">
                            Created
                        </option>

                        <option value="updated" class="text-black">
                            Updated
                        </option>

                        <option value="deleted" class="text-black">
                            Deleted
                        </option>
                    </select>

                    <!-- Clear -->
                    <button
                        v-if="filters.search || filters.module || filters.action"
                        @click="clearFilters"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white shadow-lg backdrop-blur-md transition hover:bg-white/20"
                    >
                        <X class="h-4 w-4" />
                        Clear
                    </button>
                </div>

                <!-- Table -->
                <div
                    class="overflow-hidden rounded-2xl border border-white/20 bg-white/10 shadow-2xl backdrop-blur-xl"
                >
                    <table class="w-full table-fixed text-sm">
                        <thead>
                            <tr
                                class="border-b border-white/10 bg-white/10"
                            >
                                <th
                                    class="w-[150px] px-4 py-3 text-left font-medium text-gray-200"
                                >
                                    User
                                </th>

                                <th
                                    class="w-[90px] px-4 py-3 text-left font-medium text-gray-200"
                                >
                                    Action
                                </th>

                                <th
                                    class="w-[140px] px-4 py-3 text-left font-medium text-gray-200"
                                >
                                    Module
                                </th>

                                <th
                                    class="px-4 py-3 text-left font-medium text-gray-200"
                                >
                                    Description
                                </th>

                                <th
                                    class="w-[115px] px-4 py-3 text-left font-medium text-gray-200"
                                >
                                    IP Address
                                </th>

                                <th
                                    class="w-[145px] px-4 py-3 text-left font-medium text-gray-200"
                                >
                                    Date
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/10">
                            <tr v-if="logs.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-4 py-10 text-center text-gray-300"
                                >
                                    No activity logs found.
                                </td>
                            </tr>

                            <tr
                                v-for="log in logs.data"
                                :key="log.id"
                                class="cursor-pointer transition hover:bg-white/10"
                                @click="openDrawer(log)"
                            >
                                <!-- User -->
                                <td class="px-4 py-3">
                                    <div
                                        class="max-w-[130px] truncate font-medium text-white"
                                    >
                                        {{ log.user_name }}
                                    </div>

                                    <span
                                        :class="[
                                            'mt-0.5 inline-block rounded-full px-2 py-0.5 text-xs font-medium',
                                            roleStyles[log.user_role] ??
                                                'bg-gray-100 text-gray-600',
                                        ]"
                                    >
                                        {{ log.user_role }}
                                    </span>
                                </td>

                                <!-- Action -->
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-block rounded-full px-2.5 py-0.5 text-xs font-medium capitalize',
                                            actionStyles[log.action] ??
                                                'bg-gray-100 text-gray-600',
                                        ]"
                                    >
                                        {{ log.action }}
                                    </span>
                                </td>

                                <!-- Module -->
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-block max-w-full truncate rounded-md bg-white/10 px-2 py-0.5 text-xs font-medium text-white"
                                    >
                                        {{ moduleLabels[log.module] ?? log.module }}
                                    </span>
                                </td>

                                <!-- Description -->
                                <td class="px-4 py-3">
                                    <div class="min-w-0">
                                        <div
                                            class="max-w-[220px] truncate font-medium text-white"
                                        >
                                            {{
                                                descriptionParts(
                                                    log.description,
                                                )[0]
                                            }}
                                        </div>

                                        <div
                                            v-if="
                                                descriptionParts(log.description)
                                                    .length > 1
                                            "
                                            class="mt-0.5 text-xs text-blue-300"
                                        >
                                            +{{
                                                descriptionParts(log.description)
                                                    .length - 1
                                            }}
                                            more — click to view
                                        </div>
                                    </div>
                                </td>

                                <!-- IP -->
                                <td
                                    class="truncate px-4 py-3 font-mono text-xs text-gray-300"
                                >
                                    {{ log.ip_address ?? '—' }}
                                </td>

                                <!-- Date -->
                                <td
                                    class="px-4 py-3 text-xs whitespace-nowrap text-gray-300"
                                >
                                    {{ formatDate(log.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="logs.last_page > 1"
                    class="flex items-center justify-between text-sm text-gray-200"
                >
                    <span>
                        Page {{ logs.current_page }} of {{ logs.last_page }}
                    </span>

                    <div class="flex gap-1">
                        <Link
                            v-for="link in logs.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            :class="[
                                'rounded-lg px-3 py-1.5 transition',
                                link.active
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white/10 text-white hover:bg-white/20',
                                !link.url
                                    ? 'pointer-events-none opacity-40'
                                    : '',
                            ]"
                            v-html="link.label"
                            preserve-scroll
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Drawer -->
        <Teleport to="body">
            <!-- Backdrop -->
            <Transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-300"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="drawerOpen"
                    class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
                    @click="closeDrawer"
                />
            </Transition>

            <!-- Panel -->
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-x-full"
                leave-active-class="transition-transform duration-300 ease-in"
                leave-to-class="translate-x-full"
            >
                <div
                    v-if="drawerOpen && selectedLog"
                    class="fixed top-0 right-0 z-50 flex h-full w-full max-w-md flex-col border-l border-white/20 bg-white/10 shadow-2xl backdrop-blur-2xl"
                >
                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-white/10 px-6 py-4"
                    >
                        <h2 class="text-lg font-semibold text-white">
                            Log Details
                        </h2>

                        <button
                            @click="closeDrawer"
                            class="rounded-md p-1.5 text-gray-300 transition hover:bg-white/10 hover:text-white"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="flex-1 space-y-6 overflow-y-auto px-6 py-5">
                        <!-- Badges -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                :class="[
                                    'inline-block rounded-full px-3 py-1 text-xs font-medium capitalize',
                                    actionStyles[selectedLog.action],
                                ]"
                            >
                                {{ selectedLog.action }}
                            </span>

                            <span
                                class="inline-block rounded-md bg-white/10 px-3 py-1 text-xs font-medium text-white"
                            >
                                {{
                                    moduleLabels[selectedLog.module] ??
                                    selectedLog.module
                                }}
                            </span>
                        </div>

                        <!-- User -->
                        <div
                            class="space-y-3 rounded-2xl border border-white/10 bg-white/5 p-4"
                        >
                            <div
                                class="flex items-center gap-2 text-xs font-semibold tracking-wider text-gray-300 uppercase"
                            >
                                <User class="h-3.5 w-3.5" />
                                Performed By
                            </div>

                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <span
                                    class="min-w-0 truncate text-sm font-medium text-white"
                                >
                                    {{ selectedLog.user_name }}
                                </span>

                                <span
                                    :class="[
                                        'inline-block rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        roleStyles[selectedLog.user_role],
                                    ]"
                                >
                                    {{ selectedLog.user_role }}
                                </span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div
                            class="space-y-3 rounded-2xl border border-white/10 bg-white/5 p-4"
                        >
                            <div
                                class="flex items-center gap-2 text-xs font-semibold tracking-wider text-gray-300 uppercase"
                            >
                                <FileText class="h-3.5 w-3.5" />
                                Description
                            </div>

                            <div class="space-y-3">
                                <template
                                    v-for="(
                                        part, index
                                    ) in descriptionParts(
                                        selectedLog.description,
                                    )"
                                    :key="index"
                                >
                                    <div
                                        v-if="index === 0"
                                        class="text-sm font-semibold break-words text-white"
                                    >
                                        {{ part }}
                                    </div>

                                    <div
                                        v-else
                                        class="flex flex-col gap-0.5"
                                    >
                                        <template
                                            v-if="part.includes(': ')"
                                        >
                                            <span
                                                class="text-xs font-semibold tracking-wide text-gray-400 uppercase"
                                            >
                                                {{ part.split(': ')[0] }}
                                            </span>

                                            <span
                                                class="text-sm break-words text-gray-200"
                                            >
                                                {{
                                                    part
                                                        .split(': ')
                                                        .slice(1)
                                                        .join(': ')
                                                }}
                                            </span>
                                        </template>

                                        <span
                                            v-else
                                            class="text-sm break-words text-gray-200"
                                        >
                                            {{ part }}
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Details -->
                        <div
                            class="space-y-3 rounded-2xl border border-white/10 bg-white/5 p-4"
                        >
                            <div
                                class="flex items-center gap-2 text-xs font-semibold tracking-wider text-gray-300 uppercase"
                            >
                                <Tag class="h-3.5 w-3.5" />
                                Details
                            </div>

                            <div class="space-y-3 text-sm">
                                <div
                                    class="flex items-center justify-between gap-4"
                                >
                                    <span
                                        class="flex shrink-0 items-center gap-1.5 text-gray-300"
                                    >
                                        <Monitor class="h-3.5 w-3.5" />
                                        IP Address
                                    </span>

                                    <span
                                        class="font-mono text-xs text-white"
                                    >
                                        {{ selectedLog.ip_address ?? '—' }}
                                    </span>
                                </div>

                                <div class="border-t border-white/10"></div>

                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <span
                                        class="flex shrink-0 items-center gap-1.5 text-gray-300"
                                    >
                                        <Clock class="h-3.5 w-3.5" />
                                        Date & Time
                                    </span>

                                    <span
                                        class="text-right text-xs break-words text-white"
                                    >
                                        {{
                                            formatDateFull(
                                                selectedLog.created_at,
                                            )
                                        }}
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
