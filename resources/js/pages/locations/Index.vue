<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
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
    DialogFooter,
    DialogDescription
} from '@/components/ui/dialog'
import { Plus, Trash2, MapPin, X, Archive, Pencil, AlertTriangle, Eye } from 'lucide-vue-next'

const props = defineProps<{ locations: any[] }>()

// --- State for Dialogs ---
const isCreateOpen = ref(false)
const isEditOpen = ref(false)
const isDeleteOpen = ref(false)
const isViewOpen = ref(false) // New View State
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
const addPath = (form: any) => form.storage_paths.push('')
const removePath = (form: any, index: number) => form.storage_paths.splice(index, 1)

// --- Actions ---
const submitCreate = () => {
    createForm.post('/physical-locations', {
        onSuccess: () => {
            isCreateOpen.value = false
            createForm.reset()
        }
    })
}

const openView = (loc: any) => {
    selectedLocation.value = loc
    isViewOpen.value = true
}

const openEdit = (loc: any) => {
    selectedLocation.value = loc
    editForm.name = loc.name
    editForm.color = loc.color || '#6366f1'
    editForm.storage_paths = loc.storage_paths ? [...loc.storage_paths] : []
    isEditOpen.value = true
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
    isDeleteOpen.value = true
}

const confirmDelete = () => {
    if (!selectedLocation.value) return
    router.delete(`/physical-locations/${selectedLocation.value.id}`, {
        onSuccess: () => {
            isDeleteOpen.value = false
            selectedLocation.value = null
        }
    })
}
</script>

<template>
    <Head title="Physical Archive Map" />

    <AppLayout>
        <div class="p-6 space-y-6">
            <div class="flex justify-between items-end">
                <div>
                    <h1 class="text-2xl font-bold text-foreground tracking-tight">Physical Archive Map</h1>
                    <p class="text-sm text-muted-foreground">Manage storage rooms and filing structures.</p>
                </div>

                <Dialog v-model:open="isCreateOpen">
                    <DialogTrigger as-child>
                        <Button size="sm"><Plus class="mr-2 h-4 w-4" /> Add Location</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[450px] dark:bg-slate-900">
                        <DialogHeader>
                            <DialogTitle>New Location</DialogTitle>
                        </DialogHeader>
                        <form @submit.prevent="submitCreate" class="space-y-4 pt-4">
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <Label>Room Name</Label>
                                    <Input v-model="createForm.name" placeholder="e.g., Storage Room" required class="dark:bg-slate-950" />
                                </div>
                                <div class="w-20">
                                    <Label>Color</Label>
                                    <Input type="color" v-model="createForm.color" class="h-10 p-1 cursor-pointer dark:bg-slate-950" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label>Internal Storage Units</Label>
                                <div class="max-h-[200px] overflow-y-auto pr-2 space-y-2 custom-scrollbar">
                                    <div v-for="(_, i) in createForm.storage_paths" :key="i" class="flex gap-2">
                                        <Input v-model="createForm.storage_paths[i]" placeholder="Drawer Name > Drawer #" class="dark:bg-slate-950" />
                                        <Button type="button" variant="ghost" size="icon" @click="removePath(createForm, i)" class="text-destructive shrink-0">
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                                <Button type="button" variant="outline" class="w-full text-xs mt-2 dark:border-slate-700" @click="addPath(createForm)">
                                    <Plus class="h-3 w-3 mr-1" /> Add Path
                                </Button>
                            </div>
                            <DialogFooter>
                                <Button type="submit" class="w-full" :disabled="createForm.processing">Create Map</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card v-for="loc in locations" :key="loc.id"
                    class="bg-card dark:bg-slate-900 border-t-4 transition-all hover:ring-1 hover:ring-primary/20"
                    :style="{ borderTopColor: loc.color }">
                    <CardHeader class="p-4 pb-2">
                        <CardTitle class="flex items-start justify-between text-base font-semibold">
                            <div class="flex items-center gap-2 truncate pr-2">
                                <MapPin class="h-4 w-4 shrink-0" :style="{ color: loc.color }" />
                                <span class="truncate">{{ loc.name }}</span>
                            </div>
                            <div class="flex shrink-0">
                                <Button variant="ghost" size="icon" @click="openView(loc)" class="h-7 w-7 text-muted-foreground">
                                    <Eye class="h-3.5 w-3.5" />
                                </Button>
                                <Button variant="ghost" size="icon" @click="openEdit(loc)" class="h-7 w-7 text-muted-foreground hover:text-indigo-500">
                                    <Pencil class="h-3.5 w-3.5" />
                                </Button>
                                <Button variant="ghost" size="icon" @click="openDelete(loc)" class="h-7 w-7 text-muted-foreground hover:text-destructive">
                                    <Trash2 class="h-3.5 w-3.5" />
                                </Button>
                            </div>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-4 pt-0 space-y-1.5">
                        <div v-for="path in loc.storage_paths.slice(0, 3)" :key="path"
                            class="text-[11px] bg-slate-50 dark:bg-slate-800/50 px-2 py-1 rounded border border-slate-100 dark:border-slate-800 truncate text-slate-500">
                            {{ path }}
                        </div>
                        <div v-if="loc.storage_paths?.length > 3" class="text-[10px] text-center text-muted-foreground pt-1">
                            + {{ loc.storage_paths.length - 3 }} more units
                        </div>
                        <div v-if="!loc.storage_paths?.length" class="text-[11px] italic text-center text-muted-foreground py-2 border border-dashed rounded bg-muted/5">
                            Empty
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Dialog v-model:open="isViewOpen">
                <DialogContent class="sm:max-w-[500px] dark:bg-slate-900">
                    <DialogHeader>
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :style="{ backgroundColor: selectedLocation?.color + '20' }">
                                <MapPin class="h-5 w-5" :style="{ color: selectedLocation?.color }" />
                            </div>
                            <div>
                                <DialogTitle>{{ selectedLocation?.name }}</DialogTitle>
                                <DialogDescription>Full structural map of this location</DialogDescription>
                            </div>
                        </div>
                    </DialogHeader>
                    <div class="mt-4 space-y-2 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                        <div v-for="(path, idx) in selectedLocation?.storage_paths" :key="idx"
                            class="flex items-center gap-3 p-3 rounded-md border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                            <Archive class="h-4 w-4 text-muted-foreground" />
                            <span class="text-sm font-medium">{{ path }}</span>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="secondary" @click="isViewOpen = false">Close</Button>
                        <Button @click="() => { isViewOpen = false; openEdit(selectedLocation) }">Edit Details</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog v-model:open="isEditOpen">
                <DialogContent class="sm:max-w-[450px] dark:bg-slate-900">
                    <DialogHeader>
                        <DialogTitle>Edit Location</DialogTitle>
                    </DialogHeader>
                    <form @submit.prevent="submitUpdate" class="space-y-4 pt-4">
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <Label>Room Name</Label>
                                <Input v-model="editForm.name" required class="dark:bg-slate-950" />
                            </div>
                            <div class="w-20">
                                <Label>Color</Label>
                                <Input type="color" v-model="editForm.color" class="h-10 p-1 cursor-pointer dark:bg-slate-950" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label>Internal Storage Units</Label>
                            <div class="max-h-[250px] overflow-y-auto pr-2 space-y-2 custom-scrollbar">
                                <div v-for="(_, i) in editForm.storage_paths" :key="i" class="flex gap-2">
                                    <Input v-model="editForm.storage_paths[i]" class="dark:bg-slate-950" />
                                    <Button type="button" variant="ghost" size="icon" @click="removePath(editForm, i)" class="text-destructive shrink-0">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                            <Button type="button" variant="outline" class="w-full text-xs mt-2 dark:border-slate-700" @click="addPath(editForm)">
                                <Plus class="h-3 w-3 mr-1" /> Add Path
                            </Button>
                        </div>
                        <DialogFooter>
                             <Button type="submit" class="w-full" :disabled="editForm.processing">Save Changes</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog v-model:open="isDeleteOpen">
                <DialogContent class="sm:max-w-[400px] dark:bg-slate-900">
                    <DialogHeader>
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                            <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                        <DialogTitle class="text-center text-xl">Confirm Deletion</DialogTitle>
                        <DialogDescription class="text-center pt-2">
                            Are you sure you want to delete <span class="font-bold text-foreground">"{{ selectedLocation?.name }}"</span>?
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="flex sm:justify-center gap-2 pt-4">
                        <Button variant="outline" @click="isDeleteOpen = false" class="flex-1 dark:border-slate-700">Cancel</Button>
                        <Button variant="destructive" @click="confirmDelete" class="flex-1">Yes, Delete</Button>
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
</style>
