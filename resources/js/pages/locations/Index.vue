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
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Physical Archive Map</h1>

                <Dialog v-model:open="isCreateOpen">
                    <DialogTrigger as-child>
                        <Button><Plus class="mr-2 h-4 w-4" /> Add Location</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[450px]">
                        <DialogHeader>
                            <DialogTitle>New Location</DialogTitle>
                        </DialogHeader>
                        <form @submit.prevent="submitCreate" class="space-y-4 pt-4">
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <Label>Room Name</Label>
                                    <Input v-model="createForm.name" placeholder="e.g., Storage Room" required />
                                </div>
                                <div class="w-20">
                                    <Label>Color</Label>
                                    <Input type="color" v-model="createForm.color" class="h-10 p-1 cursor-pointer" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label>Internal Storage Units</Label>
                                <div class="max-h-[250px] overflow-y-auto pr-2 space-y-2 custom-scrollbar">
                                    <div v-for="(_, i) in createForm.storage_paths" :key="i" class="flex gap-2">
                                        <Input v-model="createForm.storage_paths[i]" placeholder="Drawer Name > Drawer #" />
                                        <Button type="button" variant="ghost" size="icon" @click="removePath(createForm, i)" class="text-destructive shrink-0">
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <div v-if="createForm.storage_paths.length === 0" class="text-xs text-center text-slate-400 py-4 border-2 border-dashed rounded-md">
                                        No paths added yet.
                                    </div>
                                </div>
                                <Button type="button" variant="outline" class="w-full text-xs mt-2" @click="addPath(createForm)">
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
                <Card v-for="loc in locations" :key="loc.id" class="border-t-4 transition-all hover:shadow-lg" :style="{ borderTopColor: loc.color }">
                    <CardHeader class="pb-3">
                        <CardTitle class="flex items-center justify-between text-xl italic">
                            <div class="flex items-center gap-2">
                                <MapPin class="h-5 w-5" :style="{ color: loc.color }" /> {{ loc.name }}
                            </div>
                            <div class="flex gap-1">
                                <Button variant="ghost" size="icon" @click="openEdit(loc)" class="h-8 w-8 text-slate-400 hover:text-indigo-600">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="ghost" size="icon" @click="openDelete(loc)" class="h-8 w-8 text-slate-400 hover:text-destructive">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div v-for="path in loc.storage_paths" :key="path" class="text-sm bg-slate-50 p-2 rounded-md border flex items-center gap-2">
                            <Archive class="h-3.5 w-3.5 text-slate-400" /> {{ path }}
                        </div>
                        <div v-if="!loc.storage_paths?.length" class="text-xs italic text-center text-slate-400 py-6 border-2 border-dashed rounded-lg">
                            No storage units defined.
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Dialog v-model:open="isEditOpen">
                <DialogContent class="sm:max-w-[450px]">
                    <DialogHeader>
                        <DialogTitle>Edit Location: {{ selectedLocation?.name }}</DialogTitle>
                    </DialogHeader>
                    <form @submit.prevent="submitUpdate" class="space-y-4 pt-4">
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <Label>Room Name</Label>
                                <Input v-model="editForm.name" required />
                            </div>
                            <div class="w-20">
                                <Label>Color</Label>
                                <Input type="color" v-model="editForm.color" class="h-10 p-1 cursor-pointer" />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label>Internal Storage Units</Label>
                            <div class="max-h-[250px] overflow-y-auto pr-2 space-y-2 custom-scrollbar">
                                <div v-for="(_, i) in editForm.storage_paths" :key="i" class="flex gap-2">
                                    <Input v-model="editForm.storage_paths[i]" placeholder="Drawer Name > Drawer #" />
                                    <Button type="button" variant="ghost" size="icon" @click="removePath(editForm, i)" class="text-destructive shrink-0">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                            <Button type="button" variant="outline" class="w-full text-xs mt-2" @click="addPath(editForm)">
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
                <DialogContent class="sm:max-w-[400px]">
                    <DialogHeader>
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 mb-4">
                            <AlertTriangle class="h-6 w-6 text-red-600" />
                        </div>
                        <DialogTitle class="text-center text-xl">Confirm Deletion</DialogTitle>
                        <DialogDescription class="text-center pt-2">
                            Are you sure you want to delete <span class="font-bold text-foreground">"{{ selectedLocation?.name }}"</span>?
                            This action will remove the location mapping and cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="flex sm:justify-center gap-2 pt-4">
                        <Button variant="outline" @click="isDeleteOpen = false" class="flex-1">Cancel</Button>
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
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
