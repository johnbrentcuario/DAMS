<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    BarChart3,
    HardDrive,
    MapPin,
    ArrowRight,
    FileStack,
    Layers,
    Clock
} from 'lucide-vue-next';

const props = defineProps<{
    stats: {
        totalFiles: number;
        totalLists: number;
        totalLocations: number;
        listBreakdown: any[];
        recentFiles: any[];
    };
    locations: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
];

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
</script>

<template>
    <Head title="Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-8 p-8 bg-slate-50/50">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight text-indigo-950">Dashboard</h1>
                    <p class="text-sm text-slate-500 font-medium italic">Overview of your Folder management statistics.</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/files">
                        <Button variant="outline" class="bg-white border-slate-200 text-indigo-950 shadow-sm">
                            Manage Folder
                        </Button>
                    </Link>
                    <Link href="/physical-locations">
                        <Button class="bg-indigo-600 hover:bg-indigo-700 text-white border-none shadow-md">
                            Locations
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-5 transition-all hover:shadow-md">
                    <div class="p-3 bg-blue-50 rounded-lg shrink-0">
                        <FileStack class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Folders</p>
                        <p class="text-2xl font-bold text-slate-900 leading-none">{{ stats.totalFiles }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-5 transition-all hover:shadow-md">
                    <div class="p-3 bg-purple-50 rounded-lg shrink-0">
                        <Layers class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Employment Types</p>
                        <p class="text-2xl font-bold text-slate-900 leading-none">{{ stats.totalLists }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex items-center gap-5 transition-all hover:shadow-md">
                    <div class="p-3 bg-emerald-50 rounded-lg shrink-0">
                        <HardDrive class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Location</p>
                        <p class="text-2xl font-bold text-slate-900 leading-none">{{ stats.totalLocations }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-12 items-start">

                <Card class="lg:col-span-7 border-slate-200 shadow-sm flex flex-col h-[500px]">
                    <CardHeader class="border-b bg-white rounded-t-xl py-5 shrink-0">
                        <div class="flex items-center gap-2">
                            <BarChart3 class="h-5 w-5 text-indigo-500" />
                            <CardTitle class="text-base font-semibold text-slate-800">Volume by Category</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="p-6 bg-white flex-1 overflow-y-auto custom-scrollbar">
                        <div class="space-y-6">
                            <div v-for="list in stats.listBreakdown" :key="list.id">
                                <div class="flex justify-between items-center mb-2 text-sm">
                                    <span class="font-medium flex items-center gap-2 text-slate-700">
                                        <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: list.color }"></div>
                                        {{ list.name }}
                                    </span>
                                    <span class="text-slate-400 font-medium">
                                        {{ list.files_count }} files ({{ getPercentage(list.files_count) }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full transition-all duration-700 ease-out"
                                         :style="{
                                            width: getPercentage(list.files_count) + '%',
                                            backgroundColor: list.color
                                         }">
                                    </div>
                                </div>
                            </div>
                            <div v-if="stats.listBreakdown.length === 0" class="text-center py-20 text-slate-400 italic text-sm">
                                No active categories found.
                            </div>
                        </div>
                    </CardContent>
                    <div class="p-5 bg-slate-50 border-t border-slate-100 shrink-0 text-right">
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">Distribution Overview</span>
                    </div>
                </Card>

                <Card class="lg:col-span-5 border-none shadow-md bg-slate-900 text-white flex flex-col h-[500px]">
                    <CardHeader class="shrink-0 py-5">
                        <CardTitle class="text-base text-slate-100 flex items-center gap-2">
                            <Clock class="h-5 w-5 text-blue-400" />
                            Recently Added
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="flex-1 overflow-y-auto custom-scrollbar-dark px-6">
                        <div class="space-y-4 pb-4">
                            <div v-for="file in stats.recentFiles" :key="file.id"
                                 class="p-4 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all group cursor-default">
                                <div class="flex justify-between items-start">
                                    <div class="space-y-1 overflow-hidden">
                                        <p class="font-semibold text-white group-hover:text-blue-400 transition-colors truncate pr-2">
                                            {{ file.fullname }}
                                        </p>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[9px] bg-slate-800 px-2 py-0.5 rounded text-slate-300 border border-slate-700 uppercase tracking-tighter">
                                                {{ file.list?.name }}
                                            </span>
                                            <span class="text-[10px] text-slate-500 font-medium">
                                                {{ formatTimeAgo(file.created_at) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div v-if="file.physical_location" class="flex flex-col items-end shrink-0">
                                        <MapPin class="h-3.5 w-3.5" :style="{ color: file.physical_location.color }" />
                                        <span class="text-[9px] text-slate-400 mt-1 uppercase tracking-tighter italic">
                                            {{ file.physical_location.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="stats.recentFiles.length === 0" class="text-center py-20 text-slate-500 text-sm italic">
                                No recent activity found.
                            </div>
                        </div>
                    </CardContent>
                    <div class="p-5 bg-slate-800/50 border-t border-white/5 shrink-0">
                        <Link href="/files" class="flex items-center justify-between text-xs font-bold uppercase tracking-widest text-blue-400 hover:text-blue-300 transition-colors">
                            View All Records
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>
                </Card>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Standard Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

/* Dark Mode Scrollbar */
.custom-scrollbar-dark::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-dark::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-dark::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
</style>
