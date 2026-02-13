<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { watchDebounced } from '@vueuse/core'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger
} from '@/components/ui/dialog'
import InputError from '@/components/InputError.vue'
import { Badge } from '@/components/ui/badge'

import {
  Plus,
  Loader2,
  Trash2,
  CheckCircle2,
  Circle,
  Edit2,
  Eye,
  FilterX
} from 'lucide-vue-next'

/* =======================
   Constants & Types
======================= */
const SEPARATION_METHODS = [
  'Resignation',      // 11
  'Retirement',       // 10
  'End of Contract',  // 15
  'Termination',      // 11
  'Retrenchment',     // 12
  'Redundancy',       // 10
  'Layoff',           // 6
  'Probation Fail',   // 14
  'Mutual Agreement', // 16
  'Death'             // 5
] as const;

type SeparationMethod = typeof SEPARATION_METHODS[number];

interface File {
  id: number
  fullname: string
  description?: string
  priority: SeparationMethod
  completed: boolean
  list_id: number
  list: {
    id: number
    name: string
    color?: string
  }
}

interface FileList {
  id: number
  name: string
  color?: string
}

interface PaginationFiles {
  data: File[]
  total: number
  links: { url: string | null; label: string; active: boolean }[]
}

const props = defineProps<{
  files: PaginationFiles
  lists: FileList[]
  filters: {
    search?: string
    priority?: string
    list_id?: string
  }
}>()

/* =======================
   Logic & Filters
======================= */
const search = ref(props.filters.search || '')
const priority = ref(props.filters.priority || '')
const listId = ref(props.filters.list_id || '')

watchDebounced(
  [search, priority, listId],
  () => {
    router.get('/files', {
      search: search.value || undefined,
      priority: priority.value || undefined,
      list_id: listId.value || undefined
    }, { preserveState: true, preserveScroll: true, replace: true })
  },
  { debounce: 300 }
)

const clearFilters = () => {
  search.value = ''
  priority.value = ''
  listId.value = ''
}

/* =======================
   Forms
======================= */
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const editingFile = ref<File | null>(null)
const deletingFileId = ref<number | null>(null)
const isViewDialogOpen = ref(false)
const viewingFile = ref<File | null>(null)

const openViewDialog = (file: File) => {
  viewingFile.value = file
  isViewDialogOpen.value = true
}

const createForm = useForm({
  fullname: '',
  description: '',
  list_id: '',
  priority: '' as SeparationMethod | ''
})

const editForm = useForm({
  fullname: '',
  description: '',
  priority: '' as SeparationMethod | '',
  list_id: '' as number | ''
})

const createFile = () => {
  createForm.post('/files', {
    onSuccess: () => {
      isCreateDialogOpen.value = false
      createForm.reset()
    }
  })
}

const openEditDialog = (file: File) => {
  editingFile.value = { ...file }
  editForm.fullname = file.fullname
  editForm.description = file.description || ''
  editForm.priority = file.priority
  editForm.list_id = file.list_id
  isEditDialogOpen.value = true
}

const updateFile = () => {
  if (!editingFile.value) return
  editForm.put(`/files/${editingFile.value.id}`, {
    onSuccess: () => {
      isEditDialogOpen.value = false
      editForm.reset()
    }
  })
}

const deleteFile = (id: number) => {
  if (!confirm('Delete this record?')) return
  deletingFileId.value = id
  router.delete(`/files/${id}`, { onFinish: () => deletingFileId.value = null })
}

const toggleFileCompletion = (file: File) => {
  router.put(`/files/${file.id}`, { ...file, completed: !file.completed }, { preserveScroll: true })
}

const getPriorityVariant = (method: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
  const highRisk = ['Termination', 'Death', 'Layoff', 'Redundancy', 'Probation Fail'];
  return highRisk.includes(method) ? 'destructive' : 'secondary';
}

const selectStyle = "w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none";
</script>

<template>
  <Head title="Folders Management" />

  <AppLayout>
    <div class="p-6 space-y-6 w-full">

      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">All Folders</h1>
          <p class="text-muted-foreground">{{ files.total }} folders</p>
        </div>

        <Dialog v-model:open="isCreateDialogOpen">
          <DialogTrigger as-child>
            <Button size="lg"><Plus class="h-5 w-5 mr-2" /> Add Folder</Button>
          </DialogTrigger>

          <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
              <DialogTitle>New Folder</DialogTitle>
              <DialogDescription>Enter employee separation details.</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="createFile" class="space-y-4 pt-4">
              <div class="space-y-2">
                <Label>Full Name</Label>
                <Input v-model="createForm.fullname" placeholder="e.g. John Smith" required />
                <InputError :message="createForm.errors.fullname" />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>Employment Type</Label>
                  <select v-model="createForm.list_id" :class="selectStyle" required>
                    <option value="">Select Type</option>
                    <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
                  </select>
                </div>
                <div class="space-y-2">
                  <Label>Mode of Separation</Label>
                  <select v-model="createForm.priority" :class="selectStyle" required>
                    <option value="">Select Mode</option>
                    <option v-for="method in SEPARATION_METHODS" :key="method" :value="method">{{ method }}</option>
                  </select>
                </div>
              </div>

              <div class="space-y-2">
                <Label>Description / Remarks</Label>
                <textarea v-model="createForm.description" :class="selectStyle" class="min-h-[100px]" placeholder="Optional details..." />
              </div>

              <Button type="submit" class="w-full" :disabled="createForm.processing">
                <Loader2 v-if="createForm.processing" class="h-4 w-4 mr-2 animate-spin" />
                Create Record
              </Button>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      <Card>
        <CardContent class="grid md:grid-cols-3 lg:grid-cols-4 gap-4 pt-6">
          <Input v-model="search" placeholder="Search by name..." class="w-full" />

          <select v-model="priority" :class="selectStyle">
            <option value="">All Mode of Separation</option>
            <option v-for="method in SEPARATION_METHODS" :key="method" :value="method">{{ method }}</option>
          </select>

          <select v-model="listId" :class="selectStyle">
            <option value="">All Employment Types</option>
            <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
          </select>

          <div v-if="search || priority || listId" class="flex items-center">
            <Button variant="ghost" @click="clearFilters" class="text-muted-foreground">
              <FilterX class="h-4 w-4 mr-2" /> Reset
            </Button>
          </div>
        </CardContent>
      </Card>

      <Card class="border-none shadow-sm">
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-muted/40 border-b">
                <tr>
                  <th class="p-4 text-left font-semibold ">Status</th>
                  <th class="p-4 text-left font-semibold ">Full Name</th>
                  <th class="p-4 text-left font-semibold">Description / Remarks</th>
                  <th class="p-4 text-left font-semibold">Mode of Separation</th>
                  <th class="p-4 text-left font-semibold">Employment Type</th>
                  <th class="p-4 text font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr v-for="file in files.data" :key="file.id" class="hover:bg-muted/20 transition-colors">
                  <td class="p-4 text-center">
                    <button @click="toggleFileCompletion(file)">
                      <CheckCircle2 v-if="file.completed" class="text-green-600 h-5 w-5" />
                      <Circle v-else class="text-muted-foreground h-5 w-5" />
                    </button>
                  </td>
                  <td class="p-4 font-medium">{{ file.fullname }}</td>

                  <td class="p-4 text-muted-foreground">
                    <span v-if="file.description" class="line-clamp-1">{{ file.description }}</span>
                    <span v-else class="italic opacity-50 text-xs">No remarks</span>
                  </td>

                  <td class="p-4">
                    <Badge :variant="getPriorityVariant(file.priority)" class="truncate px-2.5 py-0.5">
                      {{ file.priority }}
                    </Badge>
                  </td>
                  <td class="p-4">
                    <div class="flex items-center gap-2">
                      <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: file.list?.color || '#cbd5e1' }"></div>
                      {{ file.list?.name }}
                    </div>
                  </td>
                  <td class="p-4 text-right">
                    <div class="flex justify-end gap-1">
                        <Button variant="ghost" size="icon" @click="openViewDialog(file)" class="h-8 w-8 text-blue-600 hover:bg-blue-50">
                            <Eye class="h-4 w-4" />
                        </Button>
                      <Button variant="ghost" size="icon" @click="openEditDialog(file)" class="h-8 w-8">
                        <Edit2 class="h-4 w-4" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 text-destructive hover:bg-destructive/10"
                        @click="deleteFile(file.id)"
                        :disabled="deletingFileId === file.id"
                      >
                        <Loader2 v-if="deletingFileId === file.id" class="h-4 w-4 animate-spin" />
                        <Trash2 v-else class="h-4 w-4" />
                      </Button>
                    </div>
                  </td>

                </tr>
                <tr v-if="files.data.length === 0">
                   <td colspan="6" class="p-10 text-center text-muted-foreground">No folders found matching your search.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="p-4 border-t flex items-center justify-between bg-muted/10">
            <div class="flex gap-1">
              <Link
                v-for="link in files.links"
                :key="link.label"
                :href="link.url || '#'"
                v-html="link.label"
                class="px-3 py-1.5 border rounded-md text-xs font-medium transition-all"
                :class="{ 'bg-primary text-white border-primary shadow-sm': link.active, 'bg-background text-muted-foreground pointer-events-none opacity-50': !link.url }"
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <Dialog v-model:open="isEditDialogOpen">
        <DialogContent class="sm:max-w-[500px]">
          <DialogHeader><DialogTitle>Edit Record</DialogTitle></DialogHeader>
          <form @submit.prevent="updateFile" class="space-y-4 pt-4">
            <div>
              <Label>Full Name</Label>
              <Input v-model="editForm.fullname" required />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <Label>Employment Type</Label>
                <select v-model="editForm.list_id" :class="selectStyle" required>
                  <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
                </select>
              </div>
              <div>
                <Label>Mode of Separation</Label>
                <select v-model="editForm.priority" :class="selectStyle" required>
                  <option v-for="method in SEPARATION_METHODS" :key="method" :value="method">{{ method }}</option>
                </select>
              </div>
            </div>
            <div>
              <Label>Description / Remarks</Label>
              <textarea v-model="editForm.description" :class="selectStyle" class="min-h-[100px]" />
            </div>
            <Button type="submit" class="w-full" :disabled="editForm.processing">Update Folder</Button>
          </form>
        </DialogContent>
      </Dialog>
      <Dialog v-model:open="isViewDialogOpen">
  <DialogContent class="sm:max-w-[500px]">
    <DialogHeader>
      <DialogTitle>Folder Details</DialogTitle>
      <DialogDescription>Full record for employee separation.</DialogDescription>
    </DialogHeader>

    <div v-if="viewingFile" class="space-y-6 py-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <Label class="text-xs text-muted-foreground">Full Name</Label>
          <p class="text-sm font-semibold">{{ viewingFile.fullname }}</p>
        </div>
        <div>
          <Label class="text-xs text-muted-foreground">Status</Label>
          <div class="flex items-center gap-1.5">
            <Badge :variant="viewingFile.completed ? 'default' : 'outline'">
              {{ viewingFile.completed ? 'Completed' : 'Pending' }}
            </Badge>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <Label class="text-xs text-muted-foreground">Employment Type</Label>
          <div class="flex items-center gap-2 mt-1">
             <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: viewingFile.list?.color || '#cbd5e1' }"></div>
             <p class="text-sm">{{ viewingFile.list?.name }}</p>
          </div>
        </div>
        <div>
          <Label class="text-xs text-muted-foreground">Mode of Separation</Label>
          <div class="mt-1">
            <Badge :variant="getPriorityVariant(viewingFile.priority)">
              {{ viewingFile.priority }}
            </Badge>
          </div>
        </div>
      </div>

      <div class="border-t pt-4">
        <Label class="text-xs text-muted-foreground">Description / Remarks</Label>
        <p class="text-sm mt-1 whitespace-pre-wrap bg-muted/30 p-3 rounded-md min-h-[100px]">
          {{ viewingFile.description || 'No remarks provided.' }}
        </p>
      </div>
    </div>

    <div class="flex justify-end">
      <Button variant="outline" @click="isViewDialogOpen = false">Close</Button>
    </div>
  </DialogContent>
</Dialog>
    </div>
  </AppLayout>
</template>
