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
  Edit2
} from 'lucide-vue-next'

/* =======================
   Types
======================= */
interface File {
  id: number
  fullname: string
  description?: string
  priority: 'low' | 'normal' | 'high'
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

interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

interface PaginationFiles {
  data: File[]
  total: number
  links: PaginationLink[]
}

/* =======================
   Props
======================= */
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
   Filters
======================= */
const search = ref(props.filters.search || '')
const priority = ref(props.filters.priority || '')
const listId = ref(props.filters.list_id || '')

watchDebounced(
  [search, priority, listId],
  () => {
    router.get(
      '/files',
      {
        search: search.value || undefined,
        priority: priority.value || undefined,
        list_id: listId.value || undefined
      },
      {
        preserveState: true,
        preserveScroll: true
      }
    )
  },
  { debounce: 300 }
)

const clearFilters = () => {
  search.value = ''
  priority.value = ''
  listId.value = ''
}

/* =======================
   Dialog state
======================= */
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const editingFile = ref<File | null>(null)
const deletingFileId = ref<number | null>(null)

/* =======================
   Forms
======================= */
const createForm = useForm({
  fullname: '',
  description: '',
  list_id: '',
  priority: 'normal'
})

const editForm = useForm({
  fullname: '',
  description: '',
  priority: 'normal'
})

/* =======================
   Actions
======================= */
const createFile = () => {
  createForm.post('/files', {
    onSuccess: () => {
      isCreateDialogOpen.value = false
      createForm.reset()
    }
  })
}

const openEditDialog = (file: File) => {
  editingFile.value = file
  editForm.fullname = file.fullname
  editForm.description = file.description || ''
  editForm.priority = file.priority
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
  if (!confirm('Are you sure?')) return

  deletingFileId.value = id
  router.delete(`/files/${id}`, {
    onFinish: () => {
      deletingFileId.value = null
    }
  })
}

const toggleFileCompletion = (file: File) => {
  router.put(`/files/${file.id}`, {
    fullname: file.fullname,
    description: file.description,
    priority: file.priority,
    completed: !file.completed
  })
}

const getPriorityVariant = (priority: string): 'default' | 'secondary' | 'destructive' => {
  switch (priority) {
    case 'high':
      return 'destructive'
    case 'low':
      return 'secondary'
    default:
      return 'default'
  }
}
</script>

<template>
  <Head title="All Files" />

  <AppLayout>
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold">All Files</h1>
          <p class="text-muted-foreground">{{ files.total }} total files</p>
        </div>

        <!-- Create Dialog -->
        <Dialog v-model:open="isCreateDialogOpen">
          <DialogTrigger as-child>
            <Button>
              <Plus class="h-4 w-4 mr-2" />
              Add File
            </Button>
          </DialogTrigger>

          <DialogContent>
            <DialogHeader>
              <DialogTitle>Add File</DialogTitle>
              <DialogDescription>Create a new file</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="createFile" class="space-y-4">
              <div>
                <Label>Full Name</Label>
                <Input v-model="createForm.fullname" required />
                <InputError :message="createForm.errors.fullname" />
              </div>

              <div>
                <Label>List</Label>
                <select v-model="createForm.list_id" class="w-full border rounded px-3 py-2" required>
                  <option value="">Select list</option>
                  <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
                </select>
                <InputError :message="createForm.errors.list_id" />
              </div>

              <div>
                <Label>Description</Label>
                <textarea v-model="createForm.description" class="w-full border rounded px-3 py-2" />
                <InputError :message="createForm.errors.description" />
              </div>

              <div>
                <Label>Priority</Label>
                <select v-model="createForm.priority" class="w-full border rounded px-3 py-2">
                  <option value="low">Low</option>
                  <option value="normal">Normal</option>
                  <option value="high">High</option>
                </select>
              </div>

              <Button type="submit" class="w-full" :disabled="createForm.processing">
                <Loader2 v-if="createForm.processing" class="h-4 w-4 mr-2 animate-spin" />
                Create
              </Button>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      <!-- Filters -->
      <Card>
        <CardHeader>
          <CardTitle>Filters</CardTitle>
        </CardHeader>

        <CardContent class="grid md:grid-cols-3 gap-4">
          <Input v-model="search" placeholder="Search..." />
          <select v-model="listId" class="border rounded px-3 py-2">
            <option value="">All Lists</option>
            <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
          </select>
          <select v-model="priority" class="border rounded px-3 py-2">
            <option value="">All Priority</option>
            <option value="low">Low</option>
            <option value="normal">Normal</option>
            <option value="high">High</option>
          </select>
        </CardContent>
      </Card>

      <!-- Table -->
      <Card>
        <CardContent>
          <table class="w-full border">
            <thead>
              <tr class="border-b">
                <th class="p-2 text-left">Done</th>
                <th class="p-2 text-left">Full Name</th>
                <th class="p-2 text-left">Description</th>
                <th class="p-2 text-left">List</th>
                <th class="p-2 text-left">Priority</th>
                <th class="p-2 text-left">Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="file in files.data" :key="file.id" class="border-b">
                <td class="p-2">
                  <button @click="toggleFileCompletion(file)">
                    <CheckCircle2 v-if="file.completed" class="text-green-600" />
                    <Circle v-else />
                  </button>
                </td>
                <td class="p-2">{{ file.fullname }}</td>
                <td class="p-2">{{ file.description }}</td>
                <td class="p-2 flex items-center gap-2">
                <!-- Color badge -->
                    <span
                    class="w-3 h-3 rounded-full"
                    :style="{ backgroundColor: file.list?.color || '#ccc' }"
                    ></span>
                    <!-- List name -->
                    {{ file.list?.name }}
                </td>

                <td class="p-2">
                  <Badge :variant="getPriorityVariant(file.priority)">{{ file.priority }}</Badge>
                </td>
                <td class="p-2 flex gap-2">
                  <!-- Edit Button -->
                  <Button variant="ghost" size="sm" @click="openEditDialog(file)">
                    <Edit2 />
                  </Button>
                  <!-- Delete Button -->
                  <Button
                    variant="ghost"
                    size="sm"
                    @click="deleteFile(file.id)"
                    :disabled="deletingFileId === file.id"
                  >
                    <Loader2 v-if="deletingFileId === file.id" class="h-4 w-4 animate-spin" />
                    <Trash2 v-else />
                  </Button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="flex gap-2 mt-4">
            <Link
              v-for="link in files.links"
              :key="link.label"
              :href="link.url || '#'"
              v-html="link.label"
              class="px-3 py-1 border rounded"
              :class="{ 'bg-primary text-white': link.active }"
            />
          </div>
        </CardContent>
      </Card>

      <!-- Edit Dialog -->
      <Dialog v-model:open="isEditDialogOpen">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Edit File</DialogTitle>
            <DialogDescription>Update the file info</DialogDescription>
          </DialogHeader>

          <form @submit.prevent="updateFile" class="space-y-4">
            <div>
              <Label>Full Name</Label>
              <Input v-model="editForm.fullname" required />
              <InputError :message="editForm.errors.fullname" />
            </div>

            <div>
              <Label>Description</Label>
              <textarea v-model="editForm.description" class="w-full border rounded px-3 py-2" />
              <InputError :message="editForm.errors.description" />
            </div>

            <div>
              <Label>Priority</Label>
              <select v-model="editForm.priority" class="w-full border rounded px-3 py-2">
                <option value="low">Low</option>
                <option value="normal">Normal</option>
                <option value="high">High</option>
              </select>
            </div>

            <Button type="submit" class="w-full" :disabled="editForm.processing">
              <Loader2 v-if="editForm.processing" class="h-4 w-4 mr-2 animate-spin" />
              Update
            </Button>
          </form>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
