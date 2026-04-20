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
import { dashboard } from '@/routes'
import {
    Plus,
    Pencil,
    Trash2,
    CheckCircle2,
    X,
    AlertTriangle
} from 'lucide-vue-next'
import { ref } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Employment Type', href: '/lists' }
]

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
    if (!listToDelete.value) return

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
                    <h1 class="text-3xl font-bold text-foreground">Employment Types</h1>
                    <p class="text-muted-foreground">Manage types and their required document checklists.</p>
                </div>

                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button>
                            <Plus class="h-4 w-4 mr-2" />
                            Create New
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[450px] max-h-[90vh] flex flex-col p-0 overflow-hidden dark:bg-slate-900">
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
                                    <Label>File Checklist (Requirements)</Label>
                                    <div class="space-y-2 border rounded-lg p-3 bg-muted/30 dark:bg-slate-950/50">
                                        <div v-for="(_, index) in createForm.requirements" :key="index" class="flex gap-2">
                                            <Input v-model="createForm.requirements[index]" placeholder="e.g. ID Copy" class="bg-background" />
                                            <Button type="button" variant="ghost" size="icon" @click="removeRequirement(createForm, index)" class="shrink-0 text-muted-foreground hover:text-destructive">
                                                <X class="h-4 w-4" />
                                            </Button>
                                        </div>
                                        <Button type="button" variant="outline" size="sm" class="w-full mt-2 bg-background" @click="addRequirement(createForm)">
                                            <Plus class="h-3 w-3 mr-2" /> Add Item
                                        </Button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="p-6 border-t bg-muted/20 dark:bg-slate-900/50">
                            <Button type="submit" form="createForm" class="w-full" :disabled="createForm.processing">
                                {{ createForm.processing ? 'Creating...' : 'Save Employment Type' }}
                            </Button>
                        </div>
                    </DialogContent>
                </Dialog>
            </div>

            <div v-if="lists.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card v-for="list in lists" :key="list.id" class="flex flex-col h-[380px] shadow-sm hover:shadow-md transition-shadow border-t-4 bg-card dark:bg-slate-900 dark:border-slate-800" :style="{ borderTopColor: list.color || '#6366f1' }">
                    <CardHeader class="pb-3 shrink-0">
                        <CardTitle class="text-lg truncate text-card-foreground">{{ list.name }}</CardTitle>
                    </CardHeader>

                    <CardContent class="flex-1 flex flex-col min-h-0 pt-0">
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-2 shrink-0">Required Files:</p>

                        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                            <ul v-if="list.requirements?.length" class="space-y-2">
                                <li v-for="req in list.requirements" :key="req" class="text-sm flex items-start gap-2 text-slate-600 dark:text-slate-300">
                                    <CheckCircle2 class="h-3.5 w-3.5 mt-0.5 shrink-0" :style="{ color: list.color || '#10b981' }" />
                                    <span class="leading-snug">{{ req }}</span>
                                </li>
                            </ul>
                            <p v-else class="text-sm text-muted-foreground italic py-4 border border-dashed rounded-md text-center bg-muted/30">
                                No requirements set.
                            </p>
                        </div>

                        <div class="flex gap-2 mt-4 pt-4 border-t dark:border-slate-800 shrink-0">
                            <Link :href="`/files?list_id=${list.id}`" class="flex-1">
                                <Button variant="outline" size="sm" class="w-full dark:border-slate-700">View Files</Button>
                            </Link>
                            <Button variant="outline" size="sm" @click="openEditDialog(list)" class="shrink-0 hover:bg-muted dark:border-slate-700">
                                <Pencil class="h-4 w-4" />
                            </Button>
                            <Button variant="destructive" size="sm" @click="openDeleteDialog(list)" class="shrink-0">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-20 border-2 border-dashed rounded-xl bg-muted/20 dark:border-slate-800">
                 <p class="text-muted-foreground">No employment types found. Create one to get started.</p>
            </div>

            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent class="sm:max-w-[450px] max-h-[90vh] flex flex-col p-0 overflow-hidden dark:bg-slate-900">
                    <DialogHeader class="p-6 pb-2">
                        <DialogTitle>Edit {{ editingList?.name }}</DialogTitle>
                    </DialogHeader>

                    <div class="flex-1 overflow-y-auto p-6 pt-2 custom-scrollbar">
                        <form @submit.prevent="updateList" id="editForm" class="space-y-4">
                            <div class="grid grid-cols-4 gap-4">
                                <div class="col-span-3 space-y-2">
                                    <Label>Name</Label>
                                    <Input v-model="editForm.name" required class="dark:bg-slate-950" />
                                </div>
                                <div class="col-span-1 space-y-2">
                                    <Label>Color</Label>
                                    <Input type="color" v-model="editForm.color" class="h-10 p-1 cursor-pointer dark:bg-slate-950" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label>File Checklist</Label>
                                <div class="space-y-2 border rounded-lg p-3 bg-muted/30 dark:bg-slate-950/50">
                                    <div v-for="(_, index) in editForm.requirements" :key="index" class="flex gap-2">
                                        <Input v-model="editForm.requirements[index]" class="bg-background" />
                                        <Button type="button" variant="ghost" size="icon" @click="removeRequirement(editForm, index)" class="shrink-0 text-muted-foreground hover:text-destructive">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <Button type="button" variant="outline" size="sm" class="w-full mt-2 bg-background" @click="addRequirement(editForm)">
                                        <Plus class="h-4 w-4 mr-2" /> Add Requirement
                                    </Button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="p-6 border-t bg-muted/20 dark:bg-slate-900/50">
                        <Button type="submit" form="editForm" class="w-full" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Updating...' : 'Update Checklist' }}
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>

            <Dialog v-model:open="isDeleteDialogOpen">
                <DialogContent class="sm:max-w-[400px] dark:bg-slate-900">
                    <DialogHeader>
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                            <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                        <DialogTitle class="text-center">Delete Employment Type</DialogTitle>
                        <DialogDescription class="text-center pt-2">
                            Are you sure you want to delete <span class="font-bold text-foreground">"{{ listToDelete?.name }}"</span>?
                            This action cannot be undone and may affect associated employee records.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter class="sm:justify-center gap-2 pt-4">
                        <Button variant="outline" @click="isDeleteDialogOpen = false" class="flex-1 dark:border-slate-700">
                            Cancel
                        </Button>
                        <Button variant="destructive" @click="confirmDelete" :disabled="!!deletingListId" class="flex-1">
                            {{ deletingListId ? 'Deleting...' : 'Yes, Delete' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
/* Use CSS variables for scrollbar to match system theme */
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: hsl(var(--muted-foreground) / 0.2);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--muted-foreground) / 0.4);
}
</style>
