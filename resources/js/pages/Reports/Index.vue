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
        accent: 'border-red-400/40',
        iconBg: 'bg-red-400/15',
        iconColor: 'text-red-300',
    },
    {
        key: 'complete-documents',
        title: 'Complete Documents',
        description: 'Folders that have all required documents uploaded.',
        icon: FileCheck,
        accent: 'border-green-400/40',
        iconBg: 'bg-green-400/15',
        iconColor: 'text-green-300',
    },
    {
        key: 'folder-summary',
        title: 'Folder Summary',
        description: 'All folders with their employment type, location, and attachment status.',
        icon: FolderOpen,
        accent: 'border-blue-400/40',
        iconBg: 'bg-blue-400/15',
        iconColor: 'text-blue-300',
    },
    {
        key: 'employment-types',
        title: 'Employment Types',
        description: 'Each employment type with total folders and required documents.',
        icon: Briefcase,
        accent: 'border-purple-400/40',
        iconBg: 'bg-purple-400/15',
        iconColor: 'text-purple-300',
    },
    {
        key: 'locations',
        title: 'Locations',
        description: 'Each physical location with the number of folders stored there.',
        icon: MapPin,
        accent: 'border-yellow-400/40',
        iconBg: 'bg-yellow-400/15',
        iconColor: 'text-yellow-300',
    },
    {
        key: 'activity-log',
        title: 'Activity Log',
        description: 'Export system activity filtered by date range, module, or action.',
        icon: ClipboardList,
        accent: 'border-orange-400/40',
        iconBg: 'bg-orange-400/15',
        iconColor: 'text-orange-300',
    },
    {
        key: 'users',
        title: 'Users',
        description: 'All users with their roles, ID numbers, and join dates.',
        icon: Users,
        accent: 'border-indigo-400/40',
        iconBg: 'bg-indigo-400/15',
        iconColor: 'text-indigo-300',
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

/* Shared glass select class */
const glassSelect = "w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white shadow-sm backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
const glassInput  = "w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white shadow-sm backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Reports" />

        <!-- ── Same background as Activity Log ── -->
        <div
            class="relative min-h-screen bg-cover bg-center bg-fixed"
            style="background-image: url('/images/landingbg.png')"
        >
            <!-- Dark Overlay -->
            <div class="absolute inset-0 bg-black/40"></div>

            <!-- Main Content -->
            <div class="relative z-10 flex flex-col gap-6 p-6">

                <!-- Header -->
                <div>
                    <h1 class="text-3xl font-bold text-white drop-shadow-md">Reports</h1>
                    <p class="mt-1 text-sm text-gray-200">
                        Generate and export reports in Excel or PDF format.
                    </p>
                </div>

                <!-- Report Cards -->
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 pb-10">
                    <div
                        v-for="report in reports"
                        :key="report.key"
                        :class="[
                            'rounded-2xl border border-white/20 bg-white/10 shadow-xl backdrop-blur-xl p-5 space-y-4',
                            'border-t-2', report.accent
                        ]"
                    >
                        <!-- Card Header -->
                        <div class="flex items-start gap-3">
                            <div :class="['rounded-xl p-2.5 border border-white/10', report.iconBg]">
                                <component :is="report.icon" :class="['h-5 w-5', report.iconColor]" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-semibold text-white text-sm">{{ report.title }}</h2>
                                <p class="text-xs text-gray-300 mt-0.5">{{ report.description }}</p>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="space-y-2">

                            <!-- Missing / Complete Documents: list filter -->
                            <div v-if="report.key === 'missing-documents' || report.key === 'complete-documents'">
                                <label class="block text-xs font-medium text-gray-300 mb-1">
                                    Filter by Employment Type
                                </label>
                                <select
                                    v-model="filters[report.key as 'missing-documents' | 'complete-documents'].list_id"
                                    :class="glassSelect"
                                >
                                    <option value="" class="text-black">All Employment Types</option>
                                    <option v-for="list in lists" :key="list.id" :value="list.id" class="text-black">
                                        {{ list.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Folder Summary: list + location filter -->
                            <div v-if="report.key === 'folder-summary'" class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-300 mb-1">Employment Type</label>
                                    <select v-model="filters['folder-summary'].list_id" :class="glassSelect">
                                        <option value="" class="text-black">All</option>
                                        <option v-for="list in lists" :key="list.id" :value="list.id" class="text-black">
                                            {{ list.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-300 mb-1">Location</label>
                                    <select v-model="filters['folder-summary'].location_id" :class="glassSelect">
                                        <option value="" class="text-black">All</option>
                                        <option v-for="loc in locations" :key="loc.id" :value="loc.id" class="text-black">
                                            {{ loc.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Activity Log: module, action, date range -->
                            <div v-if="report.key === 'activity-log'" class="space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-300 mb-1">Module</label>
                                        <select v-model="filters['activity-log'].module" :class="glassSelect">
                                            <option value="" class="text-black">All</option>
                                            <option value="users" class="text-black">Users</option>
                                            <option value="files" class="text-black">Folders</option>
                                            <option value="employment-types" class="text-black">Employment Types</option>
                                            <option value="locations" class="text-black">Locations</option>
                                            <option value="settings" class="text-black">Settings</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-300 mb-1">Action</label>
                                        <select v-model="filters['activity-log'].action" :class="glassSelect">
                                            <option value="" class="text-black">All</option>
                                            <option value="created" class="text-black">Created</option>
                                            <option value="updated" class="text-black">Updated</option>
                                            <option value="deleted" class="text-black">Deleted</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-300 mb-1">Date From</label>
                                        <input
                                            v-model="filters['activity-log'].date_from"
                                            type="date"
                                            :class="glassInput + ' [color-scheme:dark]'"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-300 mb-1">Date To</label>
                                        <input
                                            v-model="filters['activity-log'].date_to"
                                            type="date"
                                            :class="glassInput + ' [color-scheme:dark]'"
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
                                class="inline-flex items-center gap-2 rounded-xl border border-green-400/30 bg-green-400/15 px-3 py-2 text-xs font-medium text-green-300 transition hover:bg-green-400/25 disabled:opacity-50"
                            >
                                <FileSpreadsheet class="h-3.5 w-3.5" />
                                {{ loading === `${report.key}-excel` ? 'Downloading...' : 'Export Excel' }}
                            </button>
                            <button
                                @click="download(report.key, 'pdf')"
                                :disabled="loading === `${report.key}-pdf`"
                                class="inline-flex items-center gap-2 rounded-xl border border-red-400/30 bg-red-400/15 px-3 py-2 text-xs font-medium text-red-300 transition hover:bg-red-400/25 disabled:opacity-50"
                            >
                                <FileText class="h-3.5 w-3.5" />
                                {{ loading === `${report.key}-pdf` ? 'Downloading...' : 'Export PDF' }}
                            </button>
                        </div>

                    </div>
                </div>

            </div><!-- /relative z-10 -->
        </div><!-- /bg wrapper -->

    </AppLayout>
</template>
