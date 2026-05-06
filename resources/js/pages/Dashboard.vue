<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type AppPageProps } from '@/types';
import {
    FileStack, Layers, MapPin, FileX, FileCheck,
    ArrowRight, Clock, BarChart3, Users, ClipboardList,
    FileBarChart2
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    stats: {
        totalFiles: number;
        totalLists: number;
        totalLocations: number;
        missingDocuments: number;
        completeDocuments: number;
        listBreakdown: any[];
        locationBreakdown: any[];
        recentFiles: any[];
    };
    locations: any[];
}>();

const page = usePage<AppPageProps>();
const isAdmin = computed(() => page.props.auth.user?.role === 'admin');
const breadcrumbs: BreadcrumbItem[] = [];

const formatTimeAgo = (dateString: string) => {
    const now = new Date();
    const then = new Date(dateString);
    const seconds = Math.floor((now.getTime() - then.getTime()) / 1000);
    if (seconds < 60) return 'just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    if (days === 1) return 'yesterday';
    return `${days}d ago`;
};

const getPercentage = (count: number) => {
    if (props.stats.totalFiles === 0) return 0;
    return Math.round((count / props.stats.totalFiles) * 100);
};

const completionRate = computed(() => {
    const total = props.stats.completeDocuments + props.stats.missingDocuments;
    if (total === 0) return 0;
    return Math.round((props.stats.completeDocuments / total) * 100);
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">

            <!-- Header -->
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Dashboard</h1>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">Overview of your folder management system.</p>
            </div>

            <!-- Stats Row 1 -->
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                <div class="rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Total Folders</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">{{ stats.totalFiles }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Employment Types</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">{{ stats.totalLists }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Locations</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">{{ stats.totalLocations }}</p>
                </div>
            </div>

            <!-- Stats Row 2 — Document Completion -->
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                <div class="rounded-xl border border-red-100 dark:border-red-900/30 bg-white dark:bg-gray-800 p-4">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Missing Documents</p>
                    <p class="text-2xl font-semibold text-red-500 mt-1">{{ stats.missingDocuments }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">folders incomplete</p>
                </div>
                <div class="rounded-xl border border-green-100 dark:border-green-900/30 bg-white dark:bg-gray-800 p-4">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Complete Documents</p>
                    <p class="text-2xl font-semibold text-green-500 mt-1">{{ stats.completeDocuments }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">folders complete</p>
                </div>
                <div class="rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Completion Rate</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">{{ completionRate }}%</p>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1 mt-2">
                        <div
                            class="h-full bg-gray-900 dark:bg-white rounded-full transition-all duration-500"
                            :style="{ width: completionRate + '%' }"
                        />
                    </div>
                </div>
            </div>

            <!-- Charts + Recent -->
            <div class="grid gap-4 lg:grid-cols-12">

                <!-- Employment Type Breakdown -->
                <div class="lg:col-span-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">By Employment Type</p>
                    <div class="space-y-4">
                        <div v-for="list in stats.listBreakdown" :key="list.id">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-gray-300 truncate max-w-[140px]">{{ list.name }}</span>
                                <span class="text-gray-400 dark:text-gray-500 text-xs shrink-0">{{ list.files_count }}</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :style="{
                                        width: getPercentage(list.files_count) + '%',
                                        backgroundColor: list.color ?? '#6366f1'
                                    }"
                                />
                            </div>
                        </div>
                        <p v-if="stats.listBreakdown.length === 0" class="text-xs text-gray-400 italic text-center py-4">No data.</p>
                    </div>
                </div>

                <!-- Location Breakdown -->
                <div class="lg:col-span-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">By Location</p>
                    <div class="space-y-4">
                        <div v-for="loc in stats.locationBreakdown" :key="loc.id">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-gray-300 truncate max-w-[140px]">{{ loc.name }}</span>
                                <span class="text-gray-400 dark:text-gray-500 text-xs shrink-0">{{ loc.files_count }}</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :style="{
                                        width: getPercentage(loc.files_count) + '%',
                                        backgroundColor: loc.color ?? '#6366f1'
                                    }"
                                />
                            </div>
                        </div>
                        <p v-if="stats.locationBreakdown.length === 0" class="text-xs text-gray-400 italic text-center py-4">No data.</p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="lg:col-span-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Quick Actions</p>
                    <div class="space-y-1">
                        <Link href="/files" class="flex items-center justify-between py-2.5 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                            <div class="flex items-center gap-2.5">
                                <FileStack class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">Folders</span>
                            </div>
                            <ArrowRight class="h-3.5 w-3.5 text-gray-300 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors" />
                        </Link>
                        <Link href="/lists" class="flex items-center justify-between py-2.5 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                            <div class="flex items-center gap-2.5">
                                <Layers class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">Employment Types</span>
                            </div>
                            <ArrowRight class="h-3.5 w-3.5 text-gray-300 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors" />
                        </Link>
                        <Link href="/physical-locations" class="flex items-center justify-between py-2.5 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                            <div class="flex items-center gap-2.5">
                                <MapPin class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">Locations</span>
                            </div>
                            <ArrowRight class="h-3.5 w-3.5 text-gray-300 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors" />
                        </Link>
                        <template v-if="isAdmin">
                            <div class="border-t border-gray-100 dark:border-gray-700 my-1" />
                            <Link href="/users" class="flex items-center justify-between py-2.5 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                <div class="flex items-center gap-2.5">
                                    <Users class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">User Management</span>
                                </div>
                                <ArrowRight class="h-3.5 w-3.5 text-gray-300 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors" />
                            </Link>
                            <Link href="/activity-log" class="flex items-center justify-between py-2.5 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                <div class="flex items-center gap-2.5">
                                    <ClipboardList class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Activity Log</span>
                                </div>
                                <ArrowRight class="h-3.5 w-3.5 text-gray-300 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors" />
                            </Link>
                            <Link href="/reports" class="flex items-center justify-between py-2.5 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                <div class="flex items-center gap-2.5">
                                    <FileBarChart2 class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Reports</span>
                                </div>
                                <ArrowRight class="h-3.5 w-3.5 text-gray-300 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors" />
                            </Link>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Recently Added -->
            <div class="rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Recently Added</p>
                    <Link href="/files" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex items-center gap-1 transition-colors">
                        View all <ArrowRight class="h-3 w-3" />
                    </Link>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    <div v-if="stats.recentFiles.length === 0" class="px-5 py-8 text-center text-sm text-gray-400 italic">
                        No recent activity.
                    </div>
                    <div
                        v-for="file in stats.recentFiles"
                        :key="file.id"
                        class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
                    >
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ file.fullname }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ file.list?.name }}</p>
                        </div>
                        <div class="flex items-center gap-4 shrink-0 ml-4">
                            <div v-if="file.physical_location" class="flex items-center gap-1">
                                <div class="h-1.5 w-1.5 rounded-full" :style="{ backgroundColor: file.physical_location.color }" />
                                <span class="text-xs text-gray-400 dark:text-gray-500">{{ file.physical_location.name }}</span>
                            </div>
                            <span class="text-xs text-gray-300 dark:text-gray-600">{{ formatTimeAgo(file.created_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
