<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger, DialogFooter } from '@/components/ui/dialog'
import { Plus, Trash2, MapPin, X, Archive, Pencil } from 'lucide-vue-next'

const props = defineProps<{ locations: any[] }>()

// --- State for Dialogs ---
const isCreateOpen = ref(false)
const isEditOpen = ref(false)
const selectedLocationId = ref<number | null>(null)

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
    selectedLocationId.value = loc.id
    editForm.name = loc.name
    editForm.color = loc.color || '#6366f1'
    // Deep copy the array to avoid reference issues
    editForm.storage_paths = loc.storage_paths ? [...loc.storage_paths] : []
    isEditOpen.value = true
}

const submitUpdate = () => {
    editForm.put(`/physical-locations/${selectedLocationId.value}`, {
        onSuccess: () => {
            isEditOpen.value = false
        }
    })
}

const deleteLocation = (id: number) => {
    if (confirm('Are you sure? This will remove this location mapping.')) {
        router.delete(`/physical-locations/${id}`)
    }
}
</script>

<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Physical Archive Map</h1>

                <Dialog v-model:open="isCreateOpen">
                    <DialogTrigger as-child>
                        <Button><Plus class="mr-2 h-4 w-4" /> Add Location</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[450px]">
                        <DialogHeader><DialogTitle>New Physical Location</DialogTitle></DialogHeader>
                        <form @submit.prevent="submitCreate" class="space-y-4">
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
                                <div v-for="(_, i) in createForm.storage_paths" :key="i" class="flex gap-2">
                                    <Input v-model="createForm.storage_paths[i]" placeholder="Drawer Name > Drawer #" />
                                    <Button type="button" variant="ghost" size="icon" @click="removePath(createForm, i)" class="text-destructive">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                                <Button type="button" variant="outline" class="w-full text-xs" @click="addPath(createForm)">
                                    <Plus class="h-3 w-3 mr-1" /> Add Path
                                </Button>
                            </div>
                            <Button type="submit" class="w-full" :disabled="createForm.processing">Create Map</Button>
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
                                <Button variant="ghost" size="icon" @click="deleteLocation(loc.id)" class="h-8 w-8 text-slate-400 hover:text-destructive">
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
                    <DialogHeader><DialogTitle>Edit Location: {{ editForm.name }}</DialogTitle></DialogHeader>
                    <form @submit.prevent="submitUpdate" class="space-y-4">
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
                            <div v-for="(_, i) in editForm.storage_paths" :key="i" class="flex gap-2">
                                <Input v-model="editForm.storage_paths[i]" placeholder="Drawer Name > Drawer #" />
                                <Button type="button" variant="ghost" size="icon" @click="removePath(editForm, i)" class="text-destructive">
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                            <Button type="button" variant="outline" class="w-full text-xs" @click="addPath(editForm)">
                                <Plus class="h-3 w-3 mr-1" /> Add Path
                            </Button>
                        </div>
                        <DialogFooter>
                             <Button type="submit" class="w-full" :disabled="editForm.processing">Save Changes</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
