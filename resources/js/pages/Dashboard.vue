<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type AppPageProps } from '@/types';
import {
    FileStack, Layers, MapPin, Clock,
    Users, FileCheck, FileX, User
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
        recentFiles: any[]; // Ensure your backend includes the 'user' relationship here
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
        <!-- Background Wrapper -->
        <div
            class="relative min-h-screen bg-cover bg-center bg-fixed"
            style="background-image: url('/images/landingbg.png')"
        >
            <!-- Dark Overlay -->
            <div class="absolute inset-0 bg-black/40"></div>

            <!-- Main Content Container -->
            <div class="relative z-10 flex flex-col gap-6 p-6">

                <!-- Header -->
                <div class="flex justify-between items-end">
                    <div>
                        <h1 class="text-3xl font-bold text-white drop-shadow-md">Dashboard</h1>
                        <p class="mt-1 text-sm text-gray-200">Overview of the Digital Archive & Mapping System.</p>
                    </div>
                </div>

                <!-- Stats Row 1 -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-5 shadow-lg backdrop-blur-md">
                        <p class="text-xs font-medium text-gray-300 uppercase tracking-wider">Total Folders</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ stats.totalFiles }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-5 shadow-lg backdrop-blur-md">
                        <p class="text-xs font-medium text-gray-300 uppercase tracking-wider">Employment Types</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ stats.totalLists }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-5 shadow-lg backdrop-blur-md">
                        <p class="text-xs font-medium text-gray-300 uppercase tracking-wider">Locations</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ stats.totalLocations }}</p>
                    </div>
                </div>

                <!-- Stats Row 2 -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-red-400/30 bg-red-400/10 p-5 shadow-lg backdrop-blur-md">
                        <p class="text-xs font-medium text-red-200 uppercase tracking-wider">Incomplete</p>
                        <div class="flex items-center gap-2 mt-1 text-red-400">
                            <FileX class="h-5 w-5" />
                            <p class="text-3xl font-bold">{{ stats.missingDocuments }}</p>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-green-400/30 bg-green-400/10 p-5 shadow-lg backdrop-blur-md">
                        <p class="text-xs font-medium text-green-200 uppercase tracking-wider">Complete</p>
                        <div class="flex items-center gap-2 mt-1 text-green-400">
                            <FileCheck class="h-5 w-5" />
                            <p class="text-3xl font-bold">{{ stats.completeDocuments }}</p>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-5 shadow-lg backdrop-blur-md">
                        <p class="text-xs font-medium text-gray-300 uppercase tracking-wider">Completion Rate</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ completionRate }}%</p>
                        <div class="w-full bg-white/10 rounded-full h-1.5 mt-3">
                            <div
                                class="h-full bg-blue-500 rounded-full transition-all duration-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]"
                                :style="{ width: completionRate + '%' }"
                            />
                        </div>
                    </div>
                </div>

                <!-- Three-Column Grid -->
                <div class="grid gap-6 lg:grid-cols-12">

                    <!-- Employment Type Breakdown -->
                    <div class="lg:col-span-3 rounded-2xl border border-white/20 bg-white/10 p-6 shadow-xl backdrop-blur-md">
                        <p class="text-xs font-bold text-gray-200 uppercase tracking-widest mb-5">Employment Types</p>
                        <div class="space-y-5">
                            <div v-for="list in stats.listBreakdown" :key="list.id">
                                <div class="flex justify-between text-sm mb-1.5 text-white">
                                    <span class="truncate pr-2">{{ list.name }}</span>
                                    <span class="text-xs font-bold">{{ list.files_count }}</span>
                                </div>
                                <div class="w-full bg-white/5 rounded-full h-1.5">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :style="{
                                            width: getPercentage(list.files_count) + '%',
                                            backgroundColor: list.color ?? '#6366f1'
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Breakdown -->
                    <div class="lg:col-span-3 rounded-2xl border border-white/20 bg-white/10 p-6 shadow-xl backdrop-blur-md">
                        <p class="text-xs font-bold text-gray-200 uppercase tracking-widest mb-5">By Location</p>
                        <div class="space-y-5">
                            <div v-for="loc in stats.locationBreakdown" :key="loc.id">
                                <div class="flex justify-between text-sm mb-1.5 text-white">
                                    <span class="truncate pr-2">{{ loc.name }}</span>
                                    <span class="text-xs font-bold">{{ loc.files_count }}</span>
                                </div>
                                <div class="w-full bg-white/5 rounded-full h-1.5">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :style="{
                                            width: getPercentage(loc.files_count) + '%',
                                            backgroundColor: loc.color ?? '#3b82f6'
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recently Added Section -->
                    <div class="lg:col-span-6 rounded-2xl border border-white/20 bg-white/10 p-6 shadow-xl backdrop-blur-md">
                        <div class="flex items-center justify-between mb-5">
                            <p class="text-xs font-bold text-gray-200 uppercase tracking-widest">Recently Added</p>
                            <Link href="/files" class="text-[10px] font-bold text-blue-400 hover:underline uppercase tracking-tighter">View All</Link>
                        </div>

                        <div class="overflow-hidden">
                            <div class="flex flex-col gap-3">
                                <div v-for="file in stats.recentFiles" :key="file.id" class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/5">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="shrink-0 p-2 rounded-lg bg-blue-500/20 text-blue-400">
                                            <FileStack class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-white truncate">{{ file.name }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[10px] text-white uppercase tracking-tight">{{ file.list?.name ?? 'N/A' }}</span>
                                                <span class="text-[10px] text-white">•</span>
                                                <span class="text-[10px] text-white italic">{{ formatTimeAgo(file.created_at) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contributor Display -->
                                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 border border-white/5 shrink-0">
                                        <User class="h-3 w-3 text-gray-400" />
                                        <div class="text-right">
                                            <p class="text-[10px] text-white uppercase leading-none">Added By</p>
                                            <p class="text-xs font-semibold text-gray-200 leading-tight">
                                                {{ file.user?.name ?? 'System' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="stats.recentFiles.length === 0" class="py-10 text-center text-xs text-gray-400 italic">
                                    No recent activity found.
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /grid -->
            </div><!-- /relative content -->
        </div><!-- /bg wrapper -->
    </AppLayout>
</template>
