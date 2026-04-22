<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
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
import { type BreadcrumbItem } from '@/types'
import {
    Plus,
    Pencil,
    Trash2,
    CheckCircle2,
    X,
    AlertTriangle,
    Folder,
    Lock
} from 'lucide-vue-next'
import { ref, computed } from 'vue'

const breadcrumbs: BreadcrumbItem[] = []

const props = defineProps<{
    lists: Array<{
        id: number
        name: string
        color?: string
        files_count?: number
        requirements?: string[]
        created_at: string
    }>
}>()

/* Modal States */
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)

const editingList = ref<any>(null)
const listToDelete = ref<any>(null)
const deletingListId = ref<number | null>(null)

/* Computed check to see if deletion is allowed */
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

/* Requirement Helpers */
const addRequirement = (form: any) => {
    form.requirements.push('')
}

const removeRequirement = (form: any, index: number) => {
    form.requirements.splice(index, 1)
}

/* Actions */
const openEditDialog = (list: any) => {
    editingList.value = list
    editForm.id = list.id
    editForm.name = list.name
    editForm.color = list.color || '#6366f1'
    editForm.requirements = list.requirements ? [...list.requirements] : []
    isEditDialogOpen.value = true
}

const openDeleteDialog = (list: any) => {
    listToDelete.value = list
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
            deletingListId.value = null
            isDeleteDialogOpen.value = false
            listToDelete.value = null
        }
    })
}
</script>

<template>
    <Head title="Employment Types" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Employment Types</h1>
                    <p class="text-sm text-muted-foreground">Manage checklists for different roles.</p>
                </div>

                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button size="sm">
                            <Plus class="h-4 w-4 mr-1" />
                            Create New
                        </Button>
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

            <div v-if="lists.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card v-for="list in lists" :key="list.id"
                    class="flex flex-col h-[280px] shadow-sm hover:shadow-md transition-shadow border-t-4 bg-card dark:bg-slate-900 dark:border-slate-800"
                    :style="{ borderTopColor: list.color || '#6366f1' }">

                    <CardHeader class="p-4 pb-2 shrink-0">
                        <div class="flex items-start justify-between gap-2">
                            <CardTitle class="text-base truncate text-card-foreground leading-tight">{{ list.name }}</CardTitle>
                            <div class="flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-muted text-[10px] font-medium text-muted-foreground shrink-0 border">
                                <Folder class="h-3 w-3" />
                                {{ list.files_count || 0 }}
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent class="flex-1 flex flex-col min-h-0 px-4 pb-4 pt-0">
                        <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-wider mb-2 shrink-0">Requirements:</p>

                        <div class="flex-1 overflow-y-auto pr-1 custom-scrollbar">
                            <ul v-if="list.requirements?.length" class="space-y-1.5">
                                <li v-for="req in list.requirements" :key="req" class="text-xs flex items-start gap-2 text-slate-600 dark:text-slate-300">
                                    <CheckCircle2 class="h-3 w-3 mt-0.5 shrink-0" :style="{ color: list.color || '#10b981' }" />
                                    <span class="leading-snug truncate">{{ req }}</span>
                                </li>
                            </ul>
                            <p v-else class="text-[11px] text-muted-foreground italic py-3 border border-dashed rounded-md text-center bg-muted/30">
                                No items set.
                            </p>
                        </div>

                        <div class="flex gap-1.5 mt-3 pt-3 border-t dark:border-slate-800 shrink-0">
                            <Link :href="`/files?list_id=${list.id}`" class="flex-1">
                                <Button variant="outline" size="sm" class="w-full h-8 text-xs dark:border-slate-700">View Files</Button>
                            </Link>
                            <Button variant="outline" size="icon" @click="openEditDialog(list)" class="h-8 w-8 shrink-0 hover:bg-muted dark:border-slate-700">
                                <Pencil class="h-3.5 w-3.5" />
                            </Button>
                            <Button variant="destructive" size="icon" @click="openDeleteDialog(list)" class="h-8 w-8 shrink-0">
                                <Trash2 class="h-3.5 w-3.5" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-20 border-2 border-dashed rounded-xl bg-muted/20 dark:border-slate-800">
                 <p class="text-muted-foreground">No employment types found.</p>
            </div>

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
                                <Label>File Checklist</Label>
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
                            You cannot delete <span class="font-bold text-foreground">"{{ listToDelete?.name }}"</span> because it currently has
                            <span class="font-bold text-foreground">{{ listToDelete?.files_count }} connected files</span>.
                            Please remove or reassign the folders before deleting this type.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter class="sm:justify-center gap-2 pt-2">
                        <Button v-if="!isDeletionBlocked" variant="outline" size="sm" @click="isDeleteDialogOpen = false" class="flex-1 dark:border-slate-700">
                            Cancel
                        </Button>
                        <Button v-if="!isDeletionBlocked" variant="destructive" size="sm" @click="confirmDelete" :disabled="!!deletingListId" class="flex-1">
                            {{ deletingListId ? 'Deleting...' : 'Delete' }}
                        </Button>

                        <Button v-else variant="outline" size="sm" @click="isDeleteDialogOpen = false" class="w-full dark:border-slate-700">
                            Understood
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: hsl(var(--muted-foreground) / 0.2);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--muted-foreground) / 0.4);
}
</style>
