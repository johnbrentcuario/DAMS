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
        separationModeBreakdown: any[];
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
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="relative min-h-screen bg-cover bg-center bg-fixed"
            style="background-image: url('/images/landingbg.png')"
        >
            <div class="absolute inset-0 bg-black/40"></div>

            <div class="relative z-10 flex flex-col gap-4 sm:gap-6 p-4 sm:p-6">

                <!-- Header -->
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white drop-shadow-md">Dashboard</h1>
                    <p class="mt-1 text-xs sm:text-sm text-gray-200">Overview of your folder management system.</p>
                </div>

                <!-- Stats Row 1 -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-3 sm:p-4 shadow-lg backdrop-blur-xl">
                        <p class="text-xs text-gray-300">Total Folders</p>
                        <p class="text-xl sm:text-2xl font-semibold text-white mt-1">{{ stats.totalFiles }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-3 sm:p-4 shadow-lg backdrop-blur-xl">
                        <p class="text-xs text-gray-300">Employment Types</p>
                        <p class="text-xl sm:text-2xl font-semibold text-white mt-1">{{ stats.totalLists }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-3 sm:p-4 shadow-lg backdrop-blur-xl col-span-2 sm:col-span-1">
                        <p class="text-xs text-gray-300">Locations</p>
                        <p class="text-xl sm:text-2xl font-semibold text-white mt-1">{{ stats.totalLocations }}</p>
                    </div>
                </div>

                <!-- Stats Row 2 — Document Completion -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-red-400/30 bg-red-400/10 p-3 sm:p-4 shadow-lg backdrop-blur-xl">
                        <p class="text-xs text-gray-300">Missing Documents</p>
                        <p class="text-xl sm:text-2xl font-semibold text-red-300 mt-1">{{ stats.missingDocuments }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">folders incomplete</p>
                    </div>
                    <div class="rounded-2xl border border-green-400/30 bg-green-400/10 p-3 sm:p-4 shadow-lg backdrop-blur-xl">
                        <p class="text-xs text-gray-300">Complete Documents</p>
                        <p class="text-xl sm:text-2xl font-semibold text-green-300 mt-1">{{ stats.completeDocuments }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">folders complete</p>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-3 sm:p-4 shadow-lg backdrop-blur-xl col-span-2 sm:col-span-1">
                        <p class="text-xs text-gray-300">Completion Rate</p>
                        <p class="text-xl sm:text-2xl font-semibold text-white mt-1">{{ completionRate }}%</p>
                        <div class="w-full bg-white/10 rounded-full h-1.5 mt-2">
                            <div
                                class="h-full bg-white rounded-full transition-all duration-500"
                                :style="{ width: completionRate + '%' }"
                            />
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                    <!-- Employment Type Breakdown -->
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4 sm:p-5 shadow-lg backdrop-blur-xl">
                        <p class="text-xs font-medium text-gray-300 uppercase tracking-wider mb-4">By Employment Type</p>
                        <div class="space-y-3 sm:space-y-4">
                            <div v-for="list in stats.listBreakdown" :key="list.id">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-200 truncate max-w-[140px] text-xs sm:text-sm">{{ list.name }}</span>
                                    <span class="text-gray-400 text-xs shrink-0">{{ list.files_count }}</span>
                                </div>
                                <div class="w-full bg-white/10 rounded-full h-1.5">
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
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4 sm:p-5 shadow-lg backdrop-blur-xl">
                        <p class="text-xs font-medium text-gray-300 uppercase tracking-wider mb-4">By Location</p>
                        <div class="space-y-3 sm:space-y-4">
                            <div v-for="loc in stats.locationBreakdown" :key="loc.id">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-200 truncate max-w-[140px] text-xs sm:text-sm">{{ loc.name }}</span>
                                    <span class="text-gray-400 text-xs shrink-0">{{ loc.files_count }}</span>
                                </div>
                                <div class="w-full bg-white/10 rounded-full h-1.5">
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

                    <!-- Mode of Separation Breakdown -->
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4 sm:p-5 shadow-lg backdrop-blur-xl md:col-span-2 lg:col-span-1">
                        <p class="text-xs font-medium text-gray-300 uppercase tracking-wider mb-4">By Mode of Separation</p>
                        <div class="space-y-3 sm:space-y-4">
                            <div v-for="mode in stats.separationModeBreakdown" :key="mode.id">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-200 truncate max-w-[140px] text-xs sm:text-sm">{{ mode.name }}</span>
                                    <span class="text-gray-400 text-xs shrink-0">{{ mode.files_count }}</span>
                                </div>
                                <div class="w-full bg-white/10 rounded-full h-1.5">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :style="{
                                            width: getPercentage(mode.files_count) + '%',
                                            backgroundColor: mode.color ?? '#f97316'
                                        }"
                                    />
                                </div>
                            </div>
                            <p v-if="stats.separationModeBreakdown.length === 0" class="text-xs text-gray-400 italic text-center py-4">No data.</p>
                        </div>
                    </div>

                </div>

                <!-- Recently Added -->
                <div class="overflow-hidden rounded-2xl border border-white/20 bg-white/10 shadow-2xl backdrop-blur-xl pb-4">
                    <div class="flex items-center justify-between px-4 sm:px-5 py-3 sm:py-4 border-b border-white/10">
                        <p class="text-xs font-medium text-gray-300 uppercase tracking-wider">Recently Added</p>
                        <Link href="/files" class="text-xs text-gray-300 hover:text-white flex items-center gap-1 transition-colors">
                            View all <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>
                    <div class="divide-y divide-white/10">
                        <div v-if="stats.recentFiles.length === 0" class="px-5 py-8 text-center text-sm text-gray-400 italic">
                            No recent activity.
                        </div>
                        <div
                            v-for="file in stats.recentFiles"
                            :key="file.id"
                            class="flex items-center justify-between px-4 sm:px-5 py-3 transition hover:bg-white/10 gap-3"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-white truncate">{{ file.fullname }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 truncate">{{ file.list?.name }}</p>
                            </div>
                            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                                <div v-if="file.physical_location" class="hidden sm:flex items-center gap-1.5">
                                    <div class="h-1.5 w-1.5 rounded-full shrink-0" :style="{ backgroundColor: file.physical_location.color }" />
                                    <span class="text-xs text-gray-300 truncate max-w-[100px]">{{ file.physical_location.name }}</span>
                                </div>
                                <span class="text-xs text-gray-400 whitespace-nowrap">{{ formatTimeAgo(file.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
