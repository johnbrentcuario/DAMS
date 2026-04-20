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
import { Plus, Trash2, MapPin, X, Archive, Pencil, AlertTriangle } from 'lucide-vue-next'

const props = defineProps<{ locations: any[] }>()

// --- State for Dialogs ---
const isCreateOpen = ref(false)
const isEditOpen = ref(false)
const isDeleteOpen = ref(false)
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
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-foreground tracking-tight">Physical Archive Map</h1>
                    <p class="text-muted-foreground">Define physical storage rooms and their internal filing structures.</p>
                </div>

                <Dialog v-model:open="isCreateOpen">
                    <DialogTrigger as-child>
                        <Button><Plus class="mr-2 h-4 w-4" /> Add Location</Button>
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
                                <div class="max-h-[250px] overflow-y-auto pr-2 space-y-2 custom-scrollbar">
                                    <div v-for="(_, i) in createForm.storage_paths" :key="i" class="flex gap-2">
                                        <Input v-model="createForm.storage_paths[i]" placeholder="Drawer Name > Drawer #" class="dark:bg-slate-950" />
                                        <Button type="button" variant="ghost" size="icon" @click="removePath(createForm, i)" class="text-destructive shrink-0">
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <div v-if="createForm.storage_paths.length === 0" class="text-xs text-center text-muted-foreground py-4 border-2 border-dashed rounded-md bg-muted/20">
                                        No paths added yet.
                                    </div>
                                </div>
                                <Button type="button" variant="outline" class="w-full text-xs mt-2 dark:border-slate-700" @click="addPath(createForm)">
                                    <Plus class="h-3 w-3 mr-1" /> Add Path
                                </Button>
                            </div>
                            <DialogFooter class="pt-4">
                                <Button type="submit" class="w-full" :disabled="createForm.processing">Create Map</Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="loc in locations" :key="loc.id"
                    class="bg-card dark:bg-slate-900 border-t-4 transition-all hover:shadow-lg dark:border-slate-800"
                    :style="{ borderTopColor: loc.color }">
                    <CardHeader class="pb-3">
                        <CardTitle class="flex items-center justify-between text-xl italic text-foreground">
                            <div class="flex items-center gap-2">
                                <MapPin class="h-5 w-5" :style="{ color: loc.color }" /> {{ loc.name }}
                            </div>
                            <div class="flex gap-1">
                                <Button variant="ghost" size="icon" @click="openEdit(loc)" class="h-8 w-8 text-muted-foreground hover:text-indigo-600 dark:hover:text-indigo-400">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="ghost" size="icon" @click="openDelete(loc)" class="h-8 w-8 text-muted-foreground hover:text-destructive">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div v-for="path in loc.storage_paths" :key="path"
                            class="text-sm bg-slate-50 dark:bg-slate-800/50 p-2 rounded-md border border-slate-100 dark:border-slate-700 flex items-center gap-2 text-slate-600 dark:text-slate-300">
                            <Archive class="h-3.5 w-3.5 text-muted-foreground" /> {{ path }}
                        </div>
                        <div v-if="!loc.storage_paths?.length" class="text-xs italic text-center text-muted-foreground py-6 border-2 border-dashed rounded-lg bg-muted/10">
                            No storage units defined.
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Dialog v-model:open="isEditOpen">
                <DialogContent class="sm:max-w-[450px] dark:bg-slate-900">
                    <DialogHeader>
                        <DialogTitle>Edit Location: {{ selectedLocation?.name }}</DialogTitle>
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
                                    <Input v-model="editForm.storage_paths[i]" placeholder="Drawer Name > Drawer #" class="dark:bg-slate-950" />
                                    <Button type="button" variant="ghost" size="icon" @click="removePath(editForm, i)" class="text-destructive shrink-0">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                            <Button type="button" variant="outline" class="w-full text-xs mt-2 dark:border-slate-700" @click="addPath(editForm)">
                                <Plus class="h-3 w-3 mr-1" /> Add Path
                            </Button>
                        </div>
                        <DialogFooter class="pt-4">
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
    width: 5px;
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
