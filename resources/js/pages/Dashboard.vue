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
        <Head title="Dashboard" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="flex flex-1 flex-col gap-8 p-8 bg-slate-50/50 dark:bg-slate-950/50 transition-colors duration-300">

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-indigo-950 dark:text-indigo-100">Dashboard</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium italic">Overview of your Folder management statistics.</p>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div v-for="(stat, index) in [
                        { label: 'Total Folders', value: stats.totalFiles, icon: FileStack, color: 'blue' },
                        { label: 'Employment Types', value: stats.totalLists, icon: Layers, color: 'purple' },
                        { label: 'Location', value: stats.totalLocations, icon: HardDrive, color: 'emerald' }
                    ]" :key="index"
                    class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-5 transition-all hover:shadow-md">
                        <div :class="`p-3 bg-${stat.color}-50 dark:bg-${stat.color}-900/20 rounded-lg shrink-0`">
                            <component :is="stat.icon" :class="`h-6 w-6 text-${stat.color}-600 dark:text-${stat.color}-400`" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ stat.label }}</p>
                            <p class="text-2xl font-bold text-slate-900 dark:text-white leading-none">{{ stat.value }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-12 items-start">
                    <Card class="lg:col-span-7 border-slate-200 dark:border-slate-800 shadow-sm flex flex-col h-[500px] dark:bg-slate-900">
                        <CardHeader class="border-b bg-white dark:bg-slate-900 rounded-t-xl py-5 shrink-0 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <BarChart3 class="h-5 w-5 text-indigo-500" />
                                <CardTitle class="text-base font-semibold text-slate-800 dark:text-slate-100">Volume by Category</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent class="p-6 bg-white dark:bg-slate-900 flex-1 overflow-y-auto custom-scrollbar">
                            <div class="space-y-6">
                                <div v-for="list in stats.listBreakdown" :key="list.id">
                                    <div class="flex justify-between items-center mb-2 text-sm">
                                        <span class="font-medium flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                            <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: list.color }"></div>
                                            {{ list.name }}
                                        </span>
                                        <span class="text-slate-400 dark:text-slate-500 font-medium">
                                            {{ list.files_count }} files ({{ getPercentage(list.files_count) }}%)
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-full transition-all duration-700 ease-out"
                                            :style="{
                                                width: getPercentage(list.files_count) + '%',
                                                backgroundColor: list.color
                                            }">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                        <div class="p-5 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 shrink-0 text-right">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-widest">Distribution Overview</span>
                        </div>
                    </Card>

                <Card class="lg:col-span-5 border-slate-200 dark:border-slate-800 shadow-sm flex flex-col h-[500px] bg-white dark:bg-slate-900 transition-all">
        <CardHeader class="border-b bg-white dark:bg-slate-900 rounded-t-xl py-5 shrink-0 dark:border-slate-800">
            <CardTitle class="text-base font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <Clock class="h-5 w-5 text-indigo-500" />
                Recently Added
            </CardTitle>
        </CardHeader>

        <CardContent class="flex-1 overflow-y-auto custom-scrollbar px-6 bg-white dark:bg-slate-900">
            <div class="space-y-4 py-4">
                <div v-for="file in stats.recentFiles" :key="file.id"
                    class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all group cursor-default">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1 overflow-hidden">
                            <p class="font-semibold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate pr-2">
                                {{ file.fullname }}
                            </p>
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] bg-white dark:bg-slate-950 px-2 py-0.5 rounded text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 uppercase tracking-tighter">
                                    {{ file.list?.name }}
                                </span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                                    {{ formatTimeAgo(file.created_at) }}
                                </span>
                            </div>
                        </div>
                        <div v-if="file.physical_location" class="flex flex-col items-end shrink-0">
                            <MapPin class="h-3.5 w-3.5" :style="{ color: file.physical_location.color }" />
                            <span class="text-[9px] text-slate-400 dark:text-slate-500 mt-1 uppercase tracking-tighter italic">
                                {{ file.physical_location.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="stats.recentFiles.length === 0" class="text-center py-20 text-slate-400 dark:text-slate-500 text-sm italic">
                    No recent activity found.
                </div>
            </div>
        </CardContent>

        <div class="p-5 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 shrink-0">
            <Link href="/files" class="flex items-center justify-between text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
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
/* Custom Scrollbar Styling */
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

/* Light Mode Thumb */
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0; /* slate-200 */
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1; /* slate-300 */
}

/* Dark Mode Thumb */
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155; /* slate-700 */
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #475569; /* slate-600 */
}

/* Firefox Support */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #e2e8f0 transparent;
}

.dark .custom-scrollbar {
    scrollbar-color: #334155 transparent;
}
</style>
