<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    FileX, FileCheck, FolderOpen, Briefcase,
    MapPin, ClipboardList, Users, FileSpreadsheet, FileText
} from 'lucide-vue-next';

interface List {
    id: number;
    name: string;
}

interface Location {
    id: number;
    name: string;
}

const props = defineProps<{
    lists: List[];
    locations: Location[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reports', href: '/reports' },
];

const loading = ref<string | null>(null);

const filters = ref({
    'missing-documents':  { list_id: '' },
    'complete-documents': { list_id: '' },
    'folder-summary':     { list_id: '', location_id: '' },
    'employment-types':   {},
    'locations':          {},
    'activity-log':       { module: '', action: '', date_from: '', date_to: '' },
    'users':              {},
});

const reports = [
    {
        key: 'missing-documents',
        title: 'Missing Documents',
        description: 'Folders that are missing one or more required documents based on their employment type.',
        icon: FileX,
        color: 'text-red-500',
        bg: 'bg-red-50 dark:bg-red-900/20',
        border: 'border-red-100 dark:border-red-800',
    },
    {
        key: 'complete-documents',
        title: 'Complete Documents',
        description: 'Folders that have all required documents uploaded.',
        icon: FileCheck,
        color: 'text-green-500',
        bg: 'bg-green-50 dark:bg-green-900/20',
        border: 'border-green-100 dark:border-green-800',
    },
    {
        key: 'folder-summary',
        title: 'Folder Summary',
        description: 'All folders with their employment type, location, and attachment status.',
        icon: FolderOpen,
        color: 'text-blue-500',
        bg: 'bg-blue-50 dark:bg-blue-900/20',
        border: 'border-blue-100 dark:border-blue-800',
    },
    {
        key: 'employment-types',
        title: 'Employment Types',
        description: 'Each employment type with total folders and required documents.',
        icon: Briefcase,
        color: 'text-purple-500',
        bg: 'bg-purple-50 dark:bg-purple-900/20',
        border: 'border-purple-100 dark:border-purple-800',
    },
    {
        key: 'locations',
        title: 'Locations',
        description: 'Each physical location with the number of folders stored there.',
        icon: MapPin,
        color: 'text-yellow-500',
        bg: 'bg-yellow-50 dark:bg-yellow-900/20',
        border: 'border-yellow-100 dark:border-yellow-800',
    },
    {
        key: 'activity-log',
        title: 'Activity Log',
        description: 'Export system activity filtered by date range, module, or action.',
        icon: ClipboardList,
        color: 'text-orange-500',
        bg: 'bg-orange-50 dark:bg-orange-900/20',
        border: 'border-orange-100 dark:border-orange-800',
    },
    {
        key: 'users',
        title: 'Users',
        description: 'All users with their roles, ID numbers, and join dates.',
        icon: Users,
        color: 'text-indigo-500',
        bg: 'bg-indigo-50 dark:bg-indigo-900/20',
        border: 'border-indigo-100 dark:border-indigo-800',
    },
];

function download(type: string, format: 'excel' | 'pdf') {
    loading.value = `${type}-${format}`;

    const params = new URLSearchParams({
        type,
        format,
        ...Object.fromEntries(
            Object.entries(filters.value[type as keyof typeof filters.value] ?? {})
                .filter(([, v]) => v !== '')
        ),
    });

    window.location.href = `/reports/export?${params.toString()}`;

    setTimeout(() => {
        loading.value = null;
    }, 2000);
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Reports" />

        <div class="flex flex-col gap-6 p-6">

            <!-- Header -->
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Reports</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Generate and export reports in Excel or PDF format.
                </p>
            </div>

            <!-- Report Cards -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div
                    v-for="report in reports"
                    :key="report.key"
                    :class="['rounded-xl border p-5 space-y-4 bg-white dark:bg-gray-800', report.border]"
                >
                    <!-- Card Header -->
                    <div class="flex items-start gap-3">
                        <div :class="['rounded-lg p-2.5', report.bg]">
                            <component :is="report.icon" :class="['h-5 w-5', report.color]" />
                        </div>
                        <div class="min-w-0">
                            <h2 class="font-semibold text-gray-900 dark:text-white text-sm">{{ report.title }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ report.description }}</p>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="space-y-2">

                        <!-- Missing / Complete Documents: list filter -->
                        <div v-if="report.key === 'missing-documents' || report.key === 'complete-documents'">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Filter by Employment Type
                            </label>
                            <select
                                v-model="filters[report.key as 'missing-documents' | 'complete-documents'].list_id"
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                                <option value="">All Employment Types</option>
                                <option v-for="list in lists" :key="list.id" :value="list.id">
                                    {{ list.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Folder Summary: list + location filter -->
                        <div v-if="report.key === 'folder-summary'" class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    Employment Type
                                </label>
                                <select
                                    v-model="filters['folder-summary'].list_id"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                >
                                    <option value="">All</option>
                                    <option v-for="list in lists" :key="list.id" :value="list.id">
                                        {{ list.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    Location
                                </label>
                                <select
                                    v-model="filters['folder-summary'].location_id"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                >
                                    <option value="">All</option>
                                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                        {{ loc.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Activity Log: module, action, date range -->
                        <div v-if="report.key === 'activity-log'" class="space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Module</label>
                                    <select
                                        v-model="filters['activity-log'].module"
                                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    >
                                        <option value="">All</option>
                                        <option value="users">Users</option>
                                        <option value="files">Folders</option>
                                        <option value="employment-types">Employment Types</option>
                                        <option value="locations">Locations</option>
                                        <option value="settings">Settings</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Action</label>
                                    <select
                                        v-model="filters['activity-log'].action"
                                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    >
                                        <option value="">All</option>
                                        <option value="created">Created</option>
                                        <option value="updated">Updated</option>
                                        <option value="deleted">Deleted</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Date From</label>
                                    <input
                                        v-model="filters['activity-log'].date_from"
                                        type="date"
                                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Date To</label>
                                    <input
                                        v-model="filters['activity-log'].date_to"
                                        type="date"
                                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Export Buttons -->
                    <div class="flex items-center gap-2 pt-1">
                        <button
                            @click="download(report.key, 'excel')"
                            :disabled="loading === `${report.key}-excel`"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-700 disabled:opacity-60 transition-colors"
                        >
                            <FileSpreadsheet class="h-3.5 w-3.5" />
                            {{ loading === `${report.key}-excel` ? 'Downloading...' : 'Export Excel' }}
                        </button>
                        <button
                            @click="download(report.key, 'pdf')"
                            :disabled="loading === `${report.key}-pdf`"
                            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-xs font-medium text-white hover:bg-red-700 disabled:opacity-60 transition-colors"
                        >
                            <FileText class="h-3.5 w-3.5" />
                            {{ loading === `${report.key}-pdf` ? 'Downloading...' : 'Export PDF' }}
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </AppLayout>
</template>
