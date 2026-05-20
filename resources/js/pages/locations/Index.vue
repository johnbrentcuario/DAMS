<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    DialogFooter,
    DialogDescription
} from '@/components/ui/dialog'
import { Plus, Trash2, MapPin, X, Archive, Pencil, AlertTriangle, Search, FolderOpen, Copy, Check } from 'lucide-vue-next'

const props = defineProps<{ locations: any[] }>()

// --- Search & Sort ---
const searchQuery = ref('')
const sortBy      = ref<'name' | 'files' | 'paths'>('name')

const filteredLocations = computed(() => {
    let result = [...props.locations]

    if (searchQuery.value) {
        result = result.filter(loc =>
            loc.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
    }

    if (sortBy.value === 'name') {
        result.sort((a, b) => a.name.localeCompare(b.name))
    } else if (sortBy.value === 'files') {
        result.sort((a, b) => (b.files_count ?? 0) - (a.files_count ?? 0))
    } else if (sortBy.value === 'paths') {
        result.sort((a, b) => (b.storage_paths?.length ?? 0) - (a.storage_paths?.length ?? 0))
    }

    return result
})

// --- Copy to clipboard ---
const copiedPath = ref<string | null>(null)

const copyPath = async (path: string) => {
    await navigator.clipboard.writeText(path)
    copiedPath.value = path
    setTimeout(() => copiedPath.value = null, 2000)
}

// --- State for Dialogs ---
const isCreateOpen     = ref(false)
const isEditOpen       = ref(false)
const isDeleteOpen     = ref(false)
const isViewOpen       = ref(false)
const selectedLocation = ref<any>(null)

// --- Forms ---
const createForm = useForm({
    name: '',
    color: '#6366f1',
    storage_paths: [] as string[]
})

const editForm = useForm({
    name: '',
    color: '#6366f1',
    storage_paths: [] as string[]
})

// --- Logic Helpers ---
const addPath    = (form: any) => form.storage_paths.push('')
const removePath = (form: any, index: number) => form.storage_paths.splice(index, 1)

// --- Actions ---
const submitCreate = () => {
    createForm.post('/physical-locations', {
        onSuccess: () => {
            isCreateOpen.value = false
            createForm.reset()
            createForm.color = '#6366f1'
        }
    })
}

const openView = (loc: any) => {
    selectedLocation.value = loc
    isViewOpen.value = true
}

const openEdit = (loc: any) => {
    selectedLocation.value = loc
    editForm.name          = loc.name
    editForm.color         = loc.color || '#6366f1'
    editForm.storage_paths = loc.storage_paths ? [...loc.storage_paths] : []
    isEditOpen.value       = true
}

const submitUpdate = () => {
    editForm.put(`/physical-locations/${selectedLocation.value.id}`, {
        onSuccess: () => {
            isEditOpen.value = false
        }
    })
}

const openDelete = (loc: any) => {
    selectedLocation.value = loc
    isDeleteOpen.value     = true
}

const confirmDelete = () => {
    if (!selectedLocation.value) return
    router.delete(`/physical-locations/${selectedLocation.value.id}`, {
        onSuccess: () => {
            isDeleteOpen.value     = false
            selectedLocation.value = null
        }
    })
}
</script>

<template>
    <AppLayout>
        <div
            class="relative min-h-screen bg-cover bg-center bg-fixed"
            style="background-image: url('/images/landingbg.png')"
        >
            <!-- Dark Overlay -->
            <div class="absolute inset-0 bg-black/40"></div>

            <!-- Main Content Container -->
            <div class="relative z-10 flex flex-col gap-6 p-4 sm:p-6">

                <!-- Header Block -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-white drop-shadow-md">Physical Archive Map</h1>
                        <p class="mt-1 text-xs sm:text-sm text-gray-200">
                            {{ locations.length }} location{{ locations.length !== 1 ? 's' : '' }} ·
                            {{ locations.reduce((a, b) => a + (b.files_count ?? 0), 0) }} total folders
                        </p>
                    </div>

                    <Dialog v-model:open="isCreateOpen">
                        <DialogTrigger as-child>
                            <button class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 sm:py-2 text-sm font-medium text-white shadow-lg backdrop-blur-md transition hover:bg-white/20 active:scale-95">
                                <Plus class="h-4 w-4" /> Add Location
                            </button>
                        </DialogTrigger>
                        <DialogContent class="w-[95vw] sm:max-w-[450px] max-h-[90vh] flex flex-col p-0 overflow-hidden dark:bg-slate-900 rounded-xl">
                            <DialogHeader class="p-5 pb-2 sm:p-6 sm:pb-2">
                                <DialogTitle>New Location</DialogTitle>
                            </DialogHeader>
                            <div class="flex-1 overflow-y-auto p-5 pt-2 sm:p-6 sm:pt-2 custom-scrollbar">
                                <form @submit.prevent="submitCreate" id="createLocationForm" class="space-y-4">
                                    <div class="flex gap-3 sm:gap-4">
                                        <div class="flex-1 min-w-0">
                                            <Label>Room Name</Label>
                                            <Input v-model="createForm.name" placeholder="e.g., Storage Room" required class="dark:bg-slate-950 mt-1.5" />
                                        </div>
                                        <div class="w-20 shrink-0">
                                            <Label>Color</Label>
                                            <Input type="color" v-model="createForm.color" class="h-10 p-1 cursor-pointer dark:bg-slate-950 mt-1.5" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Internal Storage Units</Label>
                                        <div class="max-h-[180px] overflow-y-auto pr-1 space-y-2 custom-scrollbar">
                                            <div v-for="(_, i) in createForm.storage_paths" :key="i" class="flex gap-2">
                                                <Input v-model="createForm.storage_paths[i]" placeholder="Drawer Name > Drawer #" class="dark:bg-slate-950 flex-1 min-w-0" />
                                                <Button type="button" variant="ghost" size="icon" @click="removePath(createForm, i)" class="text-destructive shrink-0">
                                                    <X class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                        <Button type="button" variant="outline" class="w-full text-xs mt-2 dark:border-slate-700 bg-background/50" @click="addPath(createForm)">
                                            <Plus class="h-3 w-3 mr-1" /> Add Path
                                        </Button>
                                    </div>
                                </form>
                            </div>
                            <div class="p-4 border-t bg-muted/20 dark:bg-slate-900/50">
                                <Button type="submit" form="createLocationForm" class="w-full" :disabled="createForm.processing">Create Location</Button>
                            </div>
                        </DialogContent>
                    </Dialog>
                </div>

                <!-- Search & Sort Bar -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1 min-w-0 w-full">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-300" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search locations..."
                            class="w-full rounded-xl border border-white/20 bg-white/10 py-2.5 pr-4 pl-9 text-sm text-white placeholder-gray-300 shadow-lg backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                    </div>
                    <select
                        v-model="sortBy"
                        class="w-full sm:w-[180px] shrink-0 rounded-xl border border-white/20 bg-white/10 px-3 py-2.5 text-sm text-white shadow-lg backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    >
                        <option value="name" class="text-black">Sort: Name</option>
                        <option value="files" class="text-black">Sort: Most Folders</option>
                        <option value="paths" class="text-black">Sort: Most Paths</option>
                    </select>
                </div>

                <!-- Empty State -->
                <div
                    v-if="filteredLocations.length === 0"
                    class="flex flex-col items-center justify-center py-16 px-4 rounded-2xl border border-white/20 bg-white/10 backdrop-blur-xl text-center"
                >
                    <div class="rounded-full bg-white/10 p-4 mb-4">
                        <MapPin class="h-8 w-8 text-gray-300" />
                    </div>
                    <p class="text-sm font-medium text-gray-200">
                        {{ searchQuery ? 'No locations match your search' : 'No locations yet' }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ searchQuery ? 'Try a different search term' : 'Add a location to get started' }}
                    </p>
                </div>

                <!-- Responsive Grid -->
                <div v-else class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 pb-10">
                    <div
                        v-for="loc in filteredLocations"
                        :key="loc.id"
                        class="flex flex-col justify-between rounded-2xl border border-white/20 bg-white/10 shadow-xl backdrop-blur-xl cursor-pointer transition hover:bg-white/15 hover:shadow-2xl border-t-4"
                        :style="{ borderTopColor: loc.color }"
                        @click="openView(loc)"
                    >
                        <!-- Card Top Content -->
                        <div>
                            <!-- Card Header -->
                            <div class="p-4 pb-2">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                        <MapPin class="h-4 w-4 shrink-0" :style="{ color: loc.color }" />
                                        <span class="font-semibold text-base leading-snug text-white truncate min-w-0 flex-1">{{ loc.name }}</span>
                                    </div>
                                    <div class="flex shrink-0 -mr-1 -mt-0.5" @click.stop>
                                        <button
                                            @click="openEdit(loc)"
                                            class="h-8 w-8 rounded-lg flex items-center justify-center text-gray-300 transition hover:bg-white/10 hover:text-white"
                                            title="Edit Location"
                                        >
                                            <Pencil class="h-3.5 w-3.5" />
                                        </button>
                                        <button
                                            @click="openDelete(loc)"
                                            class="h-8 w-8 rounded-lg flex items-center justify-center text-gray-300 transition hover:bg-red-400/10 hover:text-red-300"
                                            title="Delete Location"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Body Info -->
                            <div class="px-4 pb-2 space-y-2">
                                <div class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs text-gray-300">
                                    <FolderOpen class="h-3.5 w-3.5 shrink-0" />
                                    <span>{{ loc.files_count ?? 0 }} folder{{ (loc.files_count ?? 0) !== 1 ? 's' : '' }}</span>
                                    <span class="text-gray-400">·</span>
                                    <Archive class="h-3.5 w-3.5 shrink-0" />
                                    <span>{{ loc.storage_paths?.length ?? 0 }} unit{{ (loc.storage_paths?.length ?? 0) !== 1 ? 's' : '' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Storage Paths Preview Footer Layer -->
                        <div class="px-4 pb-4 pt-1 space-y-1.5 mt-auto">
                            <div
                                v-for="path in loc.storage_paths?.slice(0, 3)"
                                :key="path"
                                class="text-[11px] bg-white/10 border border-white/10 px-2 py-1 rounded-lg truncate text-gray-200"
                            >
                                {{ path }}
                            </div>
                            <div v-if="loc.storage_paths?.length > 3" class="text-[10px] text-center text-gray-400 pt-0.5">
                                + {{ loc.storage_paths.length - 3 }} more units
                            </div>
                            <div v-if="!loc.storage_paths?.length" class="text-[11px] italic text-center text-gray-400 py-2 border border-white/10 rounded-lg bg-white/5">
                                No storage units
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- VIEW DIALOG -->
        <Dialog v-model:open="isViewOpen">
            <DialogContent class="w-[95vw] sm:max-w-[500px] max-h-[85vh] flex flex-col p-0 overflow-hidden dark:bg-slate-900 rounded-xl">
                <DialogHeader class="p-5 pb-2 sm:p-6 sm:pb-2">
                    <div class="flex items-start gap-3 pt-2">
                        <div class="p-2 rounded-lg shrink-0" :style="{ backgroundColor: selectedLocation?.color + '20' }">
                            <MapPin class="h-5 w-5" :style="{ color: selectedLocation?.color }" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <DialogTitle class="truncate text-lg">{{ selectedLocation?.name }}</DialogTitle>
                            <DialogDescription class="text-xs mt-0.5">
                                {{ selectedLocation?.files_count ?? 0 }} folders ·
                                {{ selectedLocation?.storage_paths?.length ?? 0 }} storage units
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <div class="flex-1 overflow-y-auto p-5 pt-2 sm:p-6 sm:pt-2 space-y-2 custom-scrollbar">
                    <div v-if="!selectedLocation?.storage_paths?.length" class="text-sm italic text-center text-muted-foreground py-8 border border-dashed rounded-lg bg-muted/20">
                        No storage units defined.
                    </div>
                    <div
                        v-for="(path, idx) in selectedLocation?.storage_paths"
                        :key="idx"
                        class="flex items-center justify-between gap-3 p-2.5 rounded-lg border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 group transition-colors"
                    >
                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                            <Archive class="h-4 w-4 text-muted-foreground shrink-0" />
                            <span class="text-sm font-medium truncate flex-1 min-w-0">{{ path }}</span>
                        </div>
                        <button
                            @click="copyPath(path)"
                            class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-1 rounded hover:bg-muted"
                            title="Copy path"
                        >
                            <Check v-if="copiedPath === path" class="h-3.5 w-3.5 text-green-500" />
                            <Copy v-else class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>

                <DialogFooter class="p-4 border-t bg-muted/20 dark:bg-slate-900/50 flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <Button variant="secondary" @click="isViewOpen = false" class="w-full sm:w-auto">Close</Button>
                    <Button @click="() => { isViewOpen = false; openEdit(selectedLocation) }" class="w-full sm:w-auto">Edit Details</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- EDIT DIALOG -->
        <Dialog v-model:open="isEditOpen">
            <DialogContent class="w-[95vw] sm:max-w-[450px] max-h-[90vh] flex flex-col p-0 overflow-hidden dark:bg-slate-900 rounded-xl">
                <DialogHeader class="p-5 pb-2 sm:p-6 sm:pb-2">
                    <DialogTitle>Edit Location</DialogTitle>
                </DialogHeader>
                <div class="flex-1 overflow-y-auto p-5 pt-2 sm:p-6 sm:pt-2 custom-scrollbar">
                    <form @submit.prevent="submitUpdate" id="editLocationForm" class="space-y-4">
                        <div class="flex gap-3 sm:gap-4">
                            <div class="flex-1 min-w-0">
                                <Label>Room Name</Label>
                                <Input v-model="editForm.name" required class="dark:bg-slate-950 mt-1.5" />
                            </div>
                            <div class="w-20 shrink-0">
                                <Label>Color</Label>
                                <Input type="color" v-model="editForm.color" class="h-10 p-1 cursor-pointer dark:bg-slate-950 mt-1.5" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label>Internal Storage Units</Label>
                            <div class="max-h-[220px] overflow-y-auto pr-1 space-y-2 custom-scrollbar">
                                <div v-for="(_, i) in editForm.storage_paths" :key="i" class="flex gap-2">
                                    <Input v-model="editForm.storage_paths[i]" class="dark:bg-slate-950 flex-1 min-w-0" />
                                    <Button type="button" variant="ghost" size="icon" @click="removePath(editForm, i)" class="text-destructive shrink-0">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                            <Button type="button" variant="outline" class="w-full text-xs mt-2 dark:border-slate-700 bg-background/50" @click="addPath(editForm)">
                                <Plus class="h-3 w-3 mr-1" /> Add Path
                            </Button>
                        </div>
                    </form>
                </div>
                <div class="p-4 border-t bg-muted/20 dark:bg-slate-900/50">
                    <Button type="submit" form="editLocationForm" class="w-full" :disabled="editForm.processing">Save Changes</Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- DELETE DIALOG -->
        <Dialog v-model:open="isDeleteOpen">
            <DialogContent class="w-[95vw] sm:max-w-[400px] dark:bg-slate-900 rounded-xl p-5 sm:p-6">
                <DialogHeader>
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-3">
                        <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                    </div>
                    <DialogTitle class="text-center text-lg sm:text-xl">Confirm Deletion</DialogTitle>
                    <DialogDescription class="text-center pt-2 text-xs sm:text-sm break-words">
                        Are you sure you want to delete <span class="font-bold text-foreground">"{{ selectedLocation?.name }}"</span>?
                        <span v-if="selectedLocation?.files_count > 0" class="block mt-2 text-amber-600 dark:text-amber-400 font-medium">
                            ⚠ This location has {{ selectedLocation?.files_count }} folder(s) assigned to it.
                        </span>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex flex-row justify-center gap-2 pt-4">
                    <Button variant="outline" @click="isDeleteOpen = false" class="flex-1 dark:border-slate-700 text-xs sm:text-sm">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete" class="flex-1 text-xs sm:text-sm">Yes, Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.35);
}
</style>
