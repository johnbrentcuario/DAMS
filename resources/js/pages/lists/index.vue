<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    DialogDescription,
    DialogFooter
} from '@/components/ui/dialog'
import {
    Plus, Pencil, Trash2, CheckCircle2, X,
    AlertTriangle, Folder, Lock, ExternalLink, Search
} from 'lucide-vue-next'
import { ref, computed } from 'vue'

const props = defineProps<{
    lists: Array<{
        id: number
        name: string
        color?: string
        files_count?: number
        complete_count?: number
        completion_rate?: number
        requirements?: string[]
        created_at: string
    }>
}>()

// --- Search & Sort ---
const searchQuery = ref('')
const sortBy      = ref<'name' | 'files' | 'completion' | 'requirements'>('name')

const filteredLists = computed(() => {
    let result = [...props.lists]

    if (searchQuery.value) {
        result = result.filter(l =>
            l.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
    }

    if (sortBy.value === 'name') {
        result.sort((a, b) => a.name.localeCompare(b.name))
    } else if (sortBy.value === 'files') {
        result.sort((a, b) => (b.files_count ?? 0) - (a.files_count ?? 0))
    } else if (sortBy.value === 'completion') {
        result.sort((a, b) => (b.completion_rate ?? 0) - (a.completion_rate ?? 0))
    } else if (sortBy.value === 'requirements') {
        result.sort((a, b) => (b.requirements?.length ?? 0) - (a.requirements?.length ?? 0))
    }

    return result
})

/* Modal States */
const isCreateDialogOpen = ref(false)
const isEditDialogOpen   = ref(false)
const isDeleteDialogOpen = ref(false)
const isViewDialogOpen   = ref(false)

const editingList    = ref<any>(null)
const listToDelete   = ref<any>(null)
const viewList       = ref<any>(null)
const deletingListId = ref<number | null>(null)

const isDeletionBlocked = computed(() => {
    return (listToDelete.value?.files_count ?? 0) > 0
})

/* Forms */
const createForm = useForm({
    name: '',
    color: '#6366f1',
    requirements: [] as string[]
})

const editForm = useForm({
    id: 0,
    name: '',
    color: '#6366f1',
    requirements: [] as string[]
})

const addRequirement    = (form: any) => form.requirements.push('')
const removeRequirement = (form: any, index: number) => form.requirements.splice(index, 1)

const openViewDialog = (list: any) => {
    viewList.value         = list
    isViewDialogOpen.value = true
}

const openEditDialog = (list: any) => {
    editingList.value      = list
    editForm.id            = list.id
    editForm.name          = list.name
    editForm.color         = list.color || '#6366f1'
    editForm.requirements  = list.requirements ? [...list.requirements] : []
    isEditDialogOpen.value = true
}

const openDeleteDialog = (list: any) => {
    listToDelete.value       = list
    isDeleteDialogOpen.value = true
}

const createList = () => {
    createForm.post('/lists', {
        preserveScroll: true,
        onSuccess: () => {
            isCreateDialogOpen.value = false
            createForm.reset()
        }
    })
}

const updateList = () => {
    editForm.put(`/lists/${editForm.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditDialogOpen.value = false
            editForm.reset()
        }
    })
}

const confirmDelete = () => {
    if (!listToDelete.value || isDeletionBlocked.value) return
    deletingListId.value = listToDelete.value.id
    router.delete(`/lists/${listToDelete.value.id}`, {
        onFinish: () => {
            deletingListId.value     = null
            isDeleteDialogOpen.value = false
            listToDelete.value       = null
        }
    })
}

const completionColor = (rate: number) => {
    if (rate >= 80) return '#22c55e'
    if (rate >= 50) return '#f59e0b'
    return '#ef4444'
}
</script>

<template>
    <AppLayout>
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
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white drop-shadow-md">Employment Types</h1>
                        <p class="mt-1 text-sm text-gray-200">
                            {{ lists.length }} type{{ lists.length !== 1 ? 's' : '' }} ·
                            {{ lists.reduce((a, b) => a + (b.files_count ?? 0), 0) }} total folders
                        </p>
                    </div>

                    <Dialog v-model:open="isCreateDialogOpen">
                        <DialogTrigger as-child>
                            <button class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white shadow-lg backdrop-blur-md transition hover:bg-white/20 active:scale-95">
                                <Plus class="h-4 w-4" /> Create New
                            </button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[400px] max-h-[90vh] flex flex-col p-0 overflow-hidden dark:bg-slate-900">
                            <DialogHeader class="p-6 pb-2">
                                <DialogTitle>Create Employment Type</DialogTitle>
                            </DialogHeader>
                            <div class="flex-1 overflow-y-auto p-6 pt-2 custom-scrollbar">
                                <form @submit.prevent="createList" id="createForm" class="space-y-4">
                                    <div class="grid grid-cols-4 gap-4">
                                        <div class="col-span-3 space-y-2">
                                            <Label>Name</Label>
                                            <Input v-model="createForm.name" placeholder="e.g. Permanent" required class="dark:bg-slate-950" />
                                        </div>
                                        <div class="col-span-1 space-y-2">
                                            <Label>Color</Label>
                                            <Input type="color" v-model="createForm.color" class="h-10 p-1 cursor-pointer dark:bg-slate-950" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label>File Checklist</Label>
                                        <div class="space-y-2 border rounded-lg p-3 bg-muted/30 dark:bg-slate-950/50">
                                            <div v-for="(_, index) in createForm.requirements" :key="index" class="flex gap-2">
                                                <Input v-model="createForm.requirements[index]" placeholder="e.g. ID Copy" class="bg-background h-8 text-sm" />
                                                <Button type="button" variant="ghost" size="icon" @click="removeRequirement(createForm, index)" class="h-8 w-8 shrink-0 text-muted-foreground hover:text-destructive">
                                                    <X class="h-4 w-4" />
                                                </Button>
                                            </div>
                                            <Button type="button" variant="outline" size="sm" class="w-full mt-1 bg-background text-xs" @click="addRequirement(createForm)">
                                                <Plus class="h-3 w-3 mr-1" /> Add Item
                                            </Button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="p-4 border-t bg-muted/20 dark:bg-slate-900/50">
                                <Button type="submit" form="createForm" class="w-full" :disabled="createForm.processing">
                                    {{ createForm.processing ? 'Creating...' : 'Save Employment Type' }}
                                </Button>
                            </div>
                        </DialogContent>
                    </Dialog>
                </div>

                <!-- Search & Sort — single line, glass style -->
                <div class="flex items-center gap-3">
                    <div class="relative flex-1 min-w-0">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-300" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search employment types..."
                            class="w-full rounded-xl border border-white/20 bg-white/10 py-2 pr-4 pl-9 text-sm text-white placeholder-gray-300 shadow-lg backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                    </div>
                    <select
                        v-model="sortBy"
                        class="rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white shadow-lg backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        style="width: 200px; flex-shrink: 0;"
                    >
                        <option value="name" class="text-black">Sort: Name</option>
                        <option value="files" class="text-black">Sort: Most Folders</option>
                        <option value="completion" class="text-black">Sort: Completion Rate</option>
                        <option value="requirements" class="text-black">Sort: Most Requirements</option>
                    </select>
                </div>

                <!-- Empty state -->
                <div
                    v-if="filteredLists.length === 0"
                    class="flex flex-col items-center justify-center py-20 rounded-2xl border border-white/20 bg-white/10 backdrop-blur-xl"
                >
                    <Search v-if="searchQuery" class="h-8 w-8 text-gray-300 mb-3" />
                    <p class="text-gray-200 text-sm font-medium">
                        {{ searchQuery ? 'No types match your search' : 'No employment types found.' }}
                    </p>
                    <p v-if="searchQuery" class="text-xs text-gray-400 mt-1">Try a different search term</p>
                </div>

                <!-- Cards Grid -->
<div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 pb-10">
    <div
        v-for="list in filteredLists"
        :key="list.id"
        class="flex flex-col rounded-2xl border border-white/20 bg-white/10 shadow-xl backdrop-blur-xl cursor-pointer transition hover:bg-white/15 hover:shadow-2xl border-t-4"
        :style="{ borderTopColor: list.color || '#6366f1' }"
        @click="openViewDialog(list)"
    >
        <!-- Card Header -->
        <div class="p-5 pb-0 shrink-0">
            <div class="flex items-start justify-between gap-3">
                <span class="font-semibold text-lg leading-snug truncate min-w-0 flex-1 text-white">
                    {{ list.name }}
                </span>
                <div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-white/10 text-xs font-medium text-gray-200 shrink-0 border border-white/20">
                    <Folder class="h-3.5 w-3.5" />
                    {{ list.files_count || 0 }}
                </div>
            </div>

            <!-- Completion Rate Bar -->
            <div v-if="(list.files_count ?? 0) > 0" class="mt-3.5">
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-xs text-gray-300">Completion</span>
                    <span class="text-xs font-semibold" :style="{ color: completionColor(list.completion_rate ?? 0) }">
                        {{ list.complete_count }}/{{ list.files_count }} ({{ list.completion_rate }}%)
                    </span>
                </div>
                <div class="w-full bg-white/10 rounded-full h-2">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        :style="{
                            width: (list.completion_rate ?? 0) + '%',
                            backgroundColor: completionColor(list.completion_rate ?? 0)
                        }"
                    />
                </div>
            </div>
            <div v-else class="mt-3.5">
                <span class="text-xs text-gray-400 italic">No folders assigned</span>
            </div>
        </div>

        <!-- Card Body -->
        <div class="px-5 pb-5 pt-4">
            <p class="text-[10px] font-bold text-gray-300 uppercase tracking-wider">
                Checklist ({{ list.requirements?.length ?? 0 }})
            </p>

            <!-- Action bar -->
            <div class="flex gap-2 mt-4 pt-4 border-t border-white/10" @click.stop>
                <button
                    @click="openEditDialog(list)"
                    class="h-9 w-9 shrink-0 rounded-lg border border-white/20 bg-white/10 flex items-center justify-center text-gray-200 transition hover:bg-white/20"
                >
                    <Pencil class="h-4 w-4" />
                </button>
                <button
                    @click="openDeleteDialog(list)"
                    class="h-9 w-9 shrink-0 rounded-lg border border-red-400/30 bg-red-400/10 flex items-center justify-center text-red-300 transition hover:bg-red-400/20"
                >
                    <Trash2 class="h-4 w-4" />
                </button>
                <Link :href="`/files?list_id=${list.id}`" class="flex-1 min-w-0" @click.stop>
                    <button class="w-full h-9 rounded-lg border border-white/20 bg-white/10 flex items-center justify-center gap-1.5 text-xs font-medium text-gray-200 transition hover:bg-white/20">
                        <ExternalLink class="h-4 w-4 shrink-0" />
                        <span class="truncate">View Folders</span>
                    </button>
                </Link>
            </div>
        </div>
    </div>
</div>

            </div><!-- /relative z-10 -->
        </div><!-- /bg wrapper -->

        <!-- ══════════════════════════
             DIALOGS — default shadcn
        ══════════════════════════ -->

        <!-- VIEW DIALOG -->
        <Dialog v-model:open="isViewDialogOpen">
            <DialogContent class="sm:max-w-[400px] max-h-[85vh] flex flex-col p-0 overflow-hidden dark:bg-slate-900">
                <DialogHeader class="p-6 pb-2">
                    <div class="flex items-center gap-2 mb-2 min-w-0">
                        <div class="w-4 h-4 rounded-full shrink-0" :style="{ backgroundColor: viewList?.color }"></div>
                        <DialogTitle class="truncate">{{ viewList?.name }}</DialogTitle>
                    </div>
                    <DialogDescription class="text-xs">
                        Checking configuration and requirements for this role.
                    </DialogDescription>
                    <div class="grid grid-cols-3 gap-2 mt-2">
                        <div class="flex flex-col items-center p-2.5 rounded-lg bg-muted/50 border">
                            <span class="text-lg font-bold">{{ viewList?.files_count ?? 0 }}</span>
                            <span class="text-[10px] text-muted-foreground">Folders</span>
                        </div>
                        <div class="flex flex-col items-center p-2.5 rounded-lg bg-muted/50 border">
                            <span class="text-lg font-bold">{{ viewList?.requirements?.length ?? 0 }}</span>
                            <span class="text-[10px] text-muted-foreground">Requirements</span>
                        </div>
                        <div class="flex flex-col items-center p-2.5 rounded-lg bg-muted/50 border">
                            <span class="text-lg font-bold" :style="{ color: completionColor(viewList?.completion_rate ?? 0) }">
                                {{ viewList?.completion_rate ?? 0 }}%
                            </span>
                            <span class="text-[10px] text-muted-foreground">Complete</span>
                        </div>
                    </div>
                    <div v-if="(viewList?.files_count ?? 0) > 0" class="mt-2">
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                            <div
                                class="h-full rounded-full transition-all duration-500"
                                :style="{
                                    width: (viewList?.completion_rate ?? 0) + '%',
                                    backgroundColor: completionColor(viewList?.completion_rate ?? 0)
                                }"
                            />
                        </div>
                        <p class="text-[10px] text-muted-foreground mt-1">
                            {{ viewList?.complete_count }} of {{ viewList?.files_count }} folders have all documents
                        </p>
                    </div>
                </DialogHeader>
                <div class="flex-1 overflow-y-auto p-6 pt-2 custom-scrollbar">
                    <div class="space-y-2">
                        <Label class="text-[10px] uppercase tracking-wider text-muted-foreground">Checklist</Label>
                        <div class="border rounded-md divide-y dark:divide-slate-800 bg-background overflow-hidden">
                            <div v-for="req in viewList?.requirements" :key="req" class="p-2.5 text-sm flex items-center gap-3">
                                <CheckCircle2 class="h-4 w-4 shrink-0" :style="{ color: viewList?.color }" />
                                <span class="leading-tight">{{ req }}</span>
                            </div>
                            <div v-if="!viewList?.requirements?.length" class="p-4 text-center text-xs text-muted-foreground italic">
                                No requirements defined for this type.
                            </div>
                        </div>
                    </div>
                </div>
                <DialogFooter class="p-4 border-t bg-muted/20 dark:bg-slate-900/50">
                    <Link :href="`/files?list_id=${viewList?.id}`" class="w-full">
                        <Button class="w-full">
                            <ExternalLink class="h-4 w-4 mr-2" />
                            Go to Folders
                        </Button>
                    </Link>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- EDIT DIALOG -->
        <Dialog v-model:open="isEditDialogOpen">
            <DialogContent class="sm:max-w-[400px] max-h-[90vh] flex flex-col p-0 overflow-hidden dark:bg-slate-900">
                <DialogHeader class="p-6 pb-2">
                    <DialogTitle>Edit {{ editingList?.name }}</DialogTitle>
                </DialogHeader>
                <div class="flex-1 overflow-y-auto p-6 pt-2 custom-scrollbar">
                    <form @submit.prevent="updateList" id="editForm" class="space-y-4">
                        <div class="grid grid-cols-4 gap-4">
                            <div class="col-span-3 space-y-2">
                                <Label>Name</Label>
                                <Input v-model="editForm.name" required class="dark:bg-slate-950 h-9" />
                            </div>
                            <div class="col-span-1 space-y-2">
                                <Label>Color</Label>
                                <Input type="color" v-model="editForm.color" class="h-9 p-1 cursor-pointer dark:bg-slate-950" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label>Checklist</Label>
                            <div class="space-y-2 border rounded-lg p-3 bg-muted/30 dark:bg-slate-950/50">
                                <div v-for="(_, index) in editForm.requirements" :key="index" class="flex gap-2">
                                    <Input v-model="editForm.requirements[index]" class="bg-background h-8 text-sm" />
                                    <Button type="button" variant="ghost" size="icon" @click="removeRequirement(editForm, index)" class="h-8 w-8 shrink-0 text-muted-foreground hover:text-destructive">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                                <Button type="button" variant="outline" size="sm" class="w-full mt-1 bg-background text-xs" @click="addRequirement(editForm)">
                                    <Plus class="h-3 w-3 mr-1" /> Add Requirement
                                </Button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="p-4 border-t bg-muted/20 dark:bg-slate-900/50">
                    <Button type="submit" form="editForm" class="w-full" :disabled="editForm.processing">
                        {{ editForm.processing ? 'Updating...' : 'Update Checklist' }}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- DELETE DIALOG -->
        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent class="sm:max-w-[380px] dark:bg-slate-900">
                <DialogHeader v-if="!isDeletionBlocked">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-2">
                        <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                    </div>
                    <DialogTitle class="text-center">Delete Employment Type</DialogTitle>
                    <DialogDescription class="text-center text-xs">
                        Delete <span class="font-bold text-foreground">"{{ listToDelete?.name }}"</span>? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogHeader v-else>
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30 mb-2">
                        <Lock class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <DialogTitle class="text-center">Deletion Restricted</DialogTitle>
                    <DialogDescription class="text-center text-xs pt-2">
                        You cannot delete <span class="font-bold text-foreground">"{{ listToDelete?.name }}"</span> because it has
                        <span class="font-bold text-foreground">{{ listToDelete?.files_count }} connected folders</span>.
                        Please remove or reassign the folders before deleting this type.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex justify-center gap-2 pt-2">
                    <template v-if="!isDeletionBlocked">
                        <Button variant="outline" size="sm" @click="isDeleteDialogOpen = false" class="flex-1 dark:border-slate-700">Cancel</Button>
                        <Button variant="destructive" size="sm" @click="confirmDelete" :disabled="!!deletingListId" class="flex-1">
                            {{ deletingListId ? 'Deleting...' : 'Delete' }}
                        </Button>
                    </template>
                    <Button v-else variant="outline" size="sm" @click="isDeleteDialogOpen = false" class="w-full dark:border-slate-700">
                        Understood
                    </Button>
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
