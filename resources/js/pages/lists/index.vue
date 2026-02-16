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
    DialogTrigger
} from '@/components/ui/dialog'
import { type BreadcrumbItem } from '@/types'
import { dashboard } from '@/routes'
import {
    Plus,
    Pencil,
    Trash2,
    CheckCircle2,
    X
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

const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const editingList = ref<any>(null)
const deletingListId = ref<number | null>(null)

/* Create Form */
const createForm = useForm({
    name: '',
    color: '#6366f1', // Default Indigo
    requirements: [] as string[]
})

/* Edit Form */
const editForm = useForm({
    id: 0,
    name: '',
    color: '#6366f1',
    requirements: [] as string[]
})

const addRequirement = (form: any) => {
    form.requirements.push('')
}

const removeRequirement = (form: any, index: number) => {
    form.requirements.splice(index, 1)
}

const openEditDialog = (list: any) => {
    editingList.value = list
    editForm.id = list.id
    editForm.name = list.name
    editForm.color = list.color || '#6366f1'
    editForm.requirements = list.requirements ? [...list.requirements] : []
    isEditDialogOpen.value = true
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

const deleteList = (listId: number) => {
    if (!confirm('Are you sure you want to delete the list?')) return
    deletingListId.value = listId
    router.delete(`/lists/${listId}`, {
        onFinish: () => deletingListId.value = null
    })
}
</script>

<template>
    <Head title="Employment Types" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Employment Types</h1>
                    <p class="text-muted-foreground">Manage types and their required document checklists.</p>
                </div>

                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button>
                            <Plus class="h-4 w-4 mr-2" />
                            Create New
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[450px] max-h-[90vh] flex flex-col p-0 overflow-hidden">
                        <DialogHeader class="p-6 pb-2">
                            <DialogTitle>Create Employment Type</DialogTitle>
                        </DialogHeader>

                        <div class="flex-1 overflow-y-auto p-6 pt-2 custom-scrollbar">
                            <form @submit.prevent="createList" id="createForm" class="space-y-4">
                                <div class="grid grid-cols-4 gap-4">
                                    <div class="col-span-3 space-y-2">
                                        <Label>Name</Label>
                                        <Input v-model="createForm.name" placeholder="e.g. Regular" required />
                                    </div>
                                    <div class="col-span-1 space-y-2">
                                        <Label>Color</Label>
                                        <div class="flex items-center gap-2">
                                            <Input type="color" v-model="createForm.color" class="h-10 p-1 cursor-pointer" />
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label>File Checklist (Requirements)</Label>
                                    <div class="space-y-2 border rounded-lg p-3 bg-slate-50/50">
                                        <div v-for="(_, index) in createForm.requirements" :key="index" class="flex gap-2">
                                            <Input v-model="createForm.requirements[index]" placeholder="e.g. ID Copy" class="bg-white" />
                                            <Button type="button" variant="ghost" size="icon" @click="removeRequirement(createForm, index)" class="shrink-0 text-muted-foreground hover:text-destructive">
                                                <X class="h-4 w-4" />
                                            </Button>
                                        </div>
                                        <Button type="button" variant="outline" size="sm" class="w-full mt-2" @click="addRequirement(createForm)">
                                            <Plus class="h-3 w-3 mr-2" /> Add Item
                                        </Button>
                                        <p v-if="createForm.requirements.length === 0" class="text-center text-xs text-muted-foreground py-2 italic">
                                            No requirements added yet.
                                        </p>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="p-6 border-t bg-slate-50/50">
                            <Button type="submit" form="createForm" class="w-full" :disabled="createForm.processing">
                                {{ createForm.processing ? 'Creating...' : 'Save Employment Type' }}
                            </Button>
                        </div>
                    </DialogContent>
                </Dialog>
            </div>

            <div v-if="lists.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card v-for="list in lists" :key="list.id" class="flex flex-col h-[380px] shadow-sm hover:shadow-md transition-shadow border-t-4" :style="{ borderTopColor: list.color || '#6366f1' }">
                    <CardHeader class="pb-3 shrink-0">
                        <div class="flex items-center gap-2">
                            <CardTitle class="text-lg truncate">{{ list.name }}</CardTitle>
                        </div>
                    </CardHeader>

                    <CardContent class="flex-1 flex flex-col min-h-0 pt-0">
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-2 shrink-0">Required Files:</p>

                        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                            <ul v-if="list.requirements?.length" class="space-y-2">
                                <li v-for="req in list.requirements" :key="req" class="text-sm flex items-start gap-2 text-slate-600">
                                    <CheckCircle2 class="h-3.5 w-3.5 mt-0.5 shrink-0" :style="{ color: list.color || '#10b981' }" />
                                    <span class="leading-snug">{{ req }}</span>
                                </li>
                            </ul>
                            <p v-else class="text-sm text-muted-foreground italic py-4 border border-dashed rounded-md text-center bg-slate-50/50">
                                No requirements set.
                            </p>
                        </div>

                        <div class="flex gap-2 mt-4 pt-4 border-t shrink-0">
                            <Link :href="`/files?list_id=${list.id}`" class="flex-1">
                                <Button variant="outline" size="sm" class="w-full">View Files</Button>
                            </Link>
                            <Button variant="outline" size="sm" @click="openEditDialog(list)" class="shrink-0 hover:bg-slate-100">
                                <Pencil class="h-4 w-4" />
                            </Button>
                            <Button variant="destructive" size="sm" @click="deleteList(list.id)" :disabled="deletingListId === list.id" class="shrink-0">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-20 border-2 border-dashed rounded-xl bg-slate-50/50">
                 <p class="text-muted-foreground">No employment types found. Create one to get started.</p>
            </div>

            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent class="sm:max-w-[450px] max-h-[90vh] flex flex-col p-0 overflow-hidden">
                    <DialogHeader class="p-6 pb-2">
                        <DialogTitle>Edit {{ editingList?.name }}</DialogTitle>
                    </DialogHeader>

                    <div class="flex-1 overflow-y-auto p-6 pt-2 custom-scrollbar">
                        <form @submit.prevent="updateList" id="editForm" class="space-y-4">
                            <div class="grid grid-cols-4 gap-4">
                                <div class="col-span-3 space-y-2">
                                    <Label>Name</Label>
                                    <Input v-model="editForm.name" required />
                                </div>
                                <div class="col-span-1 space-y-2">
                                    <Label>Color</Label>
                                    <Input type="color" v-model="editForm.color" class="h-10 p-1 cursor-pointer" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label>File Checklist</Label>
                                <div class="space-y-2 border rounded-lg p-3 bg-slate-50/50">
                                    <div v-for="(_, index) in editForm.requirements" :key="index" class="flex gap-2">
                                        <Input v-model="editForm.requirements[index]" class="bg-white" />
                                        <Button type="button" variant="ghost" size="icon" @click="removeRequirement(editForm, index)" class="shrink-0 text-muted-foreground hover:text-destructive">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <Button type="button" variant="outline" size="sm" class="w-full mt-2" @click="addRequirement(editForm)">
                                        <Plus class="h-4 w-4 mr-2" /> Add Requirement
                                    </Button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="p-6 border-t bg-slate-50/50">
                        <Button type="submit" form="editForm" class="w-full" :disabled="editForm.processing">
                            Update Type
                        </Button>
                    </div>
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
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
