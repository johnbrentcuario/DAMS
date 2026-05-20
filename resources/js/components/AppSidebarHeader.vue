<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Search, X, FolderOpen, Users, MapPin, Scissors } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

// ─── Global Search ────────────────────────────────────────────────────────────
const searchOpen    = ref(false)
const searchQuery   = ref('')
const searchResults = ref<any[]>([])
const searchLoading = ref(false)
const searchInput   = ref<HTMLInputElement | null>(null)
const activeIndex   = ref(-1)

const typeIcons: Record<string, any> = {
    folder:            FolderOpen,
    user:              Users,
    location:          MapPin,
    'separation-mode': Scissors,
}

const typeColors: Record<string, string> = {
    folder:            'text-blue-500 bg-blue-50 dark:bg-blue-900/20',
    user:              'text-purple-500 bg-purple-50 dark:bg-purple-900/20',
    location:          'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20',
    'separation-mode': 'text-orange-500 bg-orange-50 dark:bg-orange-900/20',
}

const typeLabels: Record<string, string> = {
    folder:            'Inactive 201 File',
    user:              'User',
    location:          'Location',
    'separation-mode': 'Separation',
}

let searchTimeout: ReturnType<typeof setTimeout> | null = null

watch(searchQuery, (val) => {
    activeIndex.value = -1
    if (searchTimeout) clearTimeout(searchTimeout)
    if (val.length < 2) {
        searchResults.value = []
        searchLoading.value = false
        return
    }
    searchLoading.value = true
    searchTimeout = setTimeout(async () => {
        try {
            const res = await axios.get('/search', { params: { q: val } })
            searchResults.value = res.data
        } catch (e) {
            searchResults.value = []
        } finally {
            searchLoading.value = false
        }
    }, 300)
})

const openSearch = () => {
    searchOpen.value = true
    setTimeout(() => searchInput.value?.focus(), 50)
}

const closeSearch = () => {
    searchOpen.value    = false
    searchQuery.value   = ''
    searchResults.value = []
    activeIndex.value   = -1
}

const goToResult = (result: any) => {
    closeSearch()
    if (result.type === 'folder') {
        router.get('/files', { search: result.title })
    } else {
        router.visit(result.url)
    }
}

const handleKeydown = (e: KeyboardEvent) => {
    if (!searchOpen.value) return
    if (e.key === 'Escape') { closeSearch(); return }
    if (e.key === 'ArrowDown') {
        e.preventDefault()
        activeIndex.value = Math.min(activeIndex.value + 1, searchResults.value.length - 1)
    }
    if (e.key === 'ArrowUp') {
        e.preventDefault()
        activeIndex.value = Math.max(activeIndex.value - 1, -1)
    }
    if (e.key === 'Enter' && activeIndex.value >= 0) {
        goToResult(searchResults.value[activeIndex.value])
    }
}

const handleGlobalKeydown = (e: KeyboardEvent) => {
    if (
        e.key === '/' &&
        !searchOpen.value &&
        !['INPUT', 'TEXTAREA'].includes((e.target as HTMLElement).tagName)
    ) {
        e.preventDefault()
        openSearch()
    }
}

onMounted(() => window.addEventListener('keydown', handleGlobalKeydown))
onUnmounted(() => window.removeEventListener('keydown', handleGlobalKeydown))
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2 flex-1">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <!-- Search Bar -->
        <button
            @click="openSearch"
            class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 transition-colors w-52"
        >
            <Search class="h-3.5 w-3.5 shrink-0" />
            <span class="flex-1 text-left text-xs">Search...</span>
            <kbd class="rounded bg-gray-200 dark:bg-gray-700 px-1 py-0.5 text-[10px]">/</kbd>
        </button>

        <!-- Mobile Search Icon -->
        <button
            @click="openSearch"
            class="flex md:hidden items-center justify-center h-8 w-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        >
            <Search class="h-4 w-4" />
        </button>
    </header>

    <!-- Global Search Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-150"
            leave-to-class="opacity-0"
        >
            <div
                v-if="searchOpen"
                class="fixed inset-0 z-50 flex items-start justify-center pt-[15vh] px-4"
                @keydown="handleKeydown"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeSearch" />

                <!-- Search Panel -->
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 -translate-y-2"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-to-class="opacity-0 scale-95 -translate-y-2"
                >
                    <div
                        v-if="searchOpen"
                        class="relative w-full max-w-lg bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                    >
                        <!-- Search Input -->
                        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 dark:border-gray-800">
                            <Search class="h-4 w-4 text-gray-400 shrink-0" />
                            <input
                                ref="searchInput"
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search Inactive 201 Files, Users, Locations, and Separation Modes..."
                                class="flex-1 bg-transparent text-sm text-gray-900 dark:text-white placeholder-gray-400 outline-none"
                                @keydown="handleKeydown"
                            />
                            <button
                                @click="closeSearch"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                            >
                                <X class="h-4 w-4" />
                            </button>
                        </div>

                        <!-- Results -->
                        <div class="max-h-[400px] overflow-y-auto">

                            <!-- Loading -->
                            <div v-if="searchLoading" class="px-4 py-8 text-center text-sm text-gray-400">
                                Searching...
                            </div>

                            <!-- No results -->
                            <div
                                v-else-if="searchQuery.length >= 2 && searchResults.length === 0 && !searchLoading"
                                class="px-4 py-8 text-center text-sm text-gray-400"
                            >
                                No results found for "<span class="font-medium text-gray-600 dark:text-gray-300">{{ searchQuery }}</span>"
                            </div>

                            <!-- Results list -->
                            <div v-else-if="searchResults.length > 0" class="py-2">
                                <button
                                    v-for="(result, index) in searchResults"
                                    :key="`${result.type}-${result.id}`"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-left transition-colors"
                                    :class="index === activeIndex
                                        ? 'bg-gray-100 dark:bg-gray-800'
                                        : 'hover:bg-gray-50 dark:hover:bg-gray-800/50'"
                                    @click="goToResult(result)"
                                    @mouseenter="activeIndex = index"
                                >
                                    <div :class="['rounded-md p-1.5 shrink-0', typeColors[result.type]]">
                                        <component :is="typeIcons[result.type]" class="h-3.5 w-3.5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ result.title }}
                                        </div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500 truncate">
                                            {{ result.subtitle }}
                                            <span v-if="result.meta" class="ml-1">· {{ result.meta }}</span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500 uppercase shrink-0">
                                        {{ typeLabels[result.type] }}
                                    </span>
                                </button>
                            </div>

                            <!-- Idle state -->
                            <div v-else class="px-4 py-6 text-center text-xs text-gray-400 space-y-1">
                                <p>Type at least 2 characters to search</p>
                                <p>Search across Inactive 201 Files, Users, Locations, and Separation Modes</p>
                            </div>

                        </div>

                        <!-- Footer -->
                        <div class="flex items-center gap-3 px-4 py-2.5 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 text-xs text-gray-400">
                            <span><kbd class="rounded bg-gray-200 dark:bg-gray-700 px-1 py-0.5">↑↓</kbd> navigate</span>
                            <span><kbd class="rounded bg-gray-200 dark:bg-gray-700 px-1 py-0.5">Enter</kbd> select</span>
                            <span><kbd class="rounded bg-gray-200 dark:bg-gray-700 px-1 py-0.5">Esc</kbd> close</span>
                            <span class="ml-auto"><kbd class="rounded bg-gray-200 dark:bg-gray-700 px-1 py-0.5">/</kbd> open</span>
                        </div>

                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
