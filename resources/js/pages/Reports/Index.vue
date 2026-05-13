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
        description: 'Folders missing required documents.',
        icon: FileX,
        accent: 'border-red-400/40',
        iconBg: 'bg-red-400/15',
        iconColor: 'text-red-300',
    },
    {
        key: 'complete-documents',
        title: 'Complete Documents',
        description: 'Folders with all required uploads.',
        icon: FileCheck,
        accent: 'border-green-400/40',
        iconBg: 'bg-green-400/15',
        iconColor: 'text-green-300',
    },
    {
        key: 'folder-summary',
        title: 'Folder Summary',
        description: 'Detailed view of all folder statuses.',
        icon: FolderOpen,
        accent: 'border-blue-400/40',
        iconBg: 'bg-blue-400/15',
        iconColor: 'text-blue-300',
    },
    {
        key: 'employment-types',
        title: 'Employment Types',
        description: 'Summary of requirements per type.',
        icon: Briefcase,
        accent: 'border-purple-400/40',
        iconBg: 'bg-purple-400/15',
        iconColor: 'text-purple-300',
    },
    {
        key: 'locations',
        title: 'Locations',
        description: 'Storage distribution by physical location.',
        icon: MapPin,
        accent: 'border-yellow-400/40',
        iconBg: 'bg-yellow-400/15',
        iconColor: 'text-yellow-300',
    },
    {
        key: 'activity-log',
        title: 'Activity Log',
        description: 'Export system audit trails and logs.',
        icon: ClipboardList,
        accent: 'border-orange-400/40',
        iconBg: 'bg-orange-400/15',
        iconColor: 'text-orange-300',
    },
    {
        key: 'users',
        title: 'Users',
        description: 'System users, roles, and join dates.',
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

const glassSelect = "w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2.5 text-sm text-white shadow-sm backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none appearance-none"
const glassInput  = "w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2.5 text-sm text-white shadow-sm backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Reports" />

        <div
            class="relative min-h-screen bg-cover bg-center bg-fixed"
            style="background-image: url('/images/landingbg.png')"
        >
            <div class="absolute inset-0 bg-black/50"></div>

            <div class="relative z-10 flex flex-col gap-6 p-4 sm:p-6 lg:p-8">

                <!-- Header -->
                <div class="mb-2">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white drop-shadow-md">Reports</h1>
                    <p class="mt-1 text-xs sm:text-sm text-gray-200 max-w-2xl">
                        Generate and export system data. Filter by specific criteria before downloading in your preferred format.
                    </p>
                </div>

                <!-- Report Cards Grid -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3 pb-20">
                    <div
                        v-for="report in reports"
                        :key="report.key"
                        :class="[
                            'flex flex-col rounded-2xl border border-white/20 bg-white/10 shadow-xl backdrop-blur-xl p-5 space-y-4 transition-transform active:scale-[0.99]',
                            'border-t-4', report.accent
                        ]"
                    >
                        <!-- Card Header -->
                        <div class="flex items-start gap-4">
                            <div :class="['shrink-0 rounded-xl p-2.5 border border-white/10', report.iconBg]">
                                <component :is="report.icon" :class="['h-5 w-5', report.iconColor]" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-semibold text-white text-base truncate">{{ report.title }}</h2>
                                <p class="text-xs text-gray-300 mt-0.5 line-clamp-2 leading-relaxed">{{ report.description }}</p>
                            </div>
                        </div>

                        <!-- Filters Section -->
                        <div class="flex-1 space-y-3">

                            <!-- Missing / Complete Documents -->
                            <div v-if="report.key === 'missing-documents' || report.key === 'complete-documents'">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5 ml-1">
                                    Employment Type
                                </label>
                                <div class="relative">
                                    <select
                                        v-model="filters[report.key as 'missing-documents' | 'complete-documents'].list_id"
                                        :class="glassSelect"
                                    >
                                        <option value="" class="text-black">All Types</option>
                                        <option v-for="list in lists" :key="list.id" :value="list.id" class="text-black">
                                            {{ list.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Folder Summary -->
                            <div v-if="report.key === 'folder-summary'" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5 ml-1">Type</label>
                                    <select v-model="filters['folder-summary'].list_id" :class="glassSelect">
                                        <option value="" class="text-black">All</option>
                                        <option v-for="list in lists" :key="list.id" :value="list.id" class="text-black">
                                            {{ list.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5 ml-1">Location</label>
                                    <select v-model="filters['folder-summary'].location_id" :class="glassSelect">
                                        <option value="" class="text-black">All</option>
                                        <option v-for="loc in locations" :key="loc.id" :value="loc.id" class="text-black">
                                            {{ loc.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Activity Log -->
                            <div v-if="report.key === 'activity-log'" class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5 ml-1">Module</label>
                                        <select v-model="filters['activity-log'].module" :class="glassSelect">
                                            <option value="" class="text-black">All</option>
                                            <option value="users" class="text-black">Users</option>
                                            <option value="files" class="text-black">Folders</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5 ml-1">Action</label>
                                        <select v-model="filters['activity-log'].action" :class="glassSelect">
                                            <option value="" class="text-black">All</option>
                                            <option value="created" class="text-black">Created</option>
                                            <option value="updated" class="text-black">Updated</option>
                                            <option value="deleted" class="text-black">Deleted</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5 ml-1">From</label>
                                        <input
                                            v-model="filters['activity-log'].date_from"
                                            type="date"
                                            :class="glassInput + ' [color-scheme:dark]'"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5 ml-1">To</label>
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
                        <div class="grid grid-cols-2 gap-2 pt-2 mt-auto">
                            <button
                                @click="download(report.key, 'excel')"
                                :disabled="loading === `${report.key}-excel`"
                                class="flex items-center justify-center gap-2 rounded-xl border border-green-400/30 bg-green-400/15 px-3 py-2.5 text-xs font-semibold text-green-300 transition hover:bg-green-400/25 active:bg-green-400/40 disabled:opacity-50"
                            >
                                <FileSpreadsheet class="h-3.5 w-3.5" />
                                <span class="hidden xs:inline">{{ loading === `${report.key}-excel` ? '...' : 'Excel' }}</span>
                                <span class="xs:hidden">Excel</span>
                            </button>
                            <button
                                @click="download(report.key, 'pdf')"
                                :disabled="loading === `${report.key}-pdf`"
                                class="flex items-center justify-center gap-2 rounded-xl border border-red-400/30 bg-red-400/15 px-3 py-2.5 text-xs font-semibold text-red-300 transition hover:bg-red-400/25 active:bg-red-400/40 disabled:opacity-50"
                            >
                                <FileText class="h-3.5 w-3.5" />
                                <span class="hidden xs:inline">{{ loading === `${report.key}-pdf` ? '...' : 'PDF' }}</span>
                                <span class="xs:hidden">PDF</span>
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom width for extra small devices button text visibility */
@media (min-width: 400px) {
    .xs\:inline { display: inline; }
    .xs\:hidden { display: none; }
}
@media (max-width: 399px) {
    .xs\:inline { display: none; }
    .xs\:hidden { display: inline; }
}

/* Ensure date picker icons are visible in dark scheme */
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
    cursor: pointer;
}
</style>
