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
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger
} from '@/components/ui/dialog'
import InputError from '@/components/InputError.vue'
import { type BreadcrumbItem } from '@/types'
import { dashboard } from '@/routes'
import {
  Plus,
  Pencil,
  Trash2,
  ExternalLink,
  Loader2,
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
    requirements?: string[] // Added requirements array
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
  color: '#6366f1',
  requirements: [] as string[] // Initialize requirements
})

/* Edit Form */
const editForm = useForm({
  id: 0,
  name: '',
  color: '#6366f1',
  requirements: [] as string[] // Initialize requirements
})

/* Helper methods for checklist */
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
  // Clone the array to avoid direct mutation
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
  if (!confirm('Are you sure?')) return
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
          <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle>Create Employment Type</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="createList" class="space-y-4 mt-4">
              <div class="space-y-2">
                <Label>Name</Label>
                <Input v-model="createForm.name" placeholder="e.g. Full Time" required />
              </div>

              <div class="space-y-2">
                <Label>File Checklist (Requirements)</Label>
                <div v-for="(_, index) in createForm.requirements" :key="index" class="flex gap-2">
                  <Input v-model="createForm.requirements[index]" placeholder="e.g. ID Copy" />
                  <Button type="button" variant="ghost" size="icon" @click="removeRequirement(createForm, index)">
                    <X class="h-4 w-4" />
                  </Button>
                </div>
                <Button type="button" variant="outline" size="sm" class="w-full" @click="addRequirement(createForm)">
                  <Plus class="h-3 w-3 mr-2" /> Add Item
                </Button>
              </div>

              <Button type="submit" class="w-full" :disabled="createForm.processing">
                {{ createForm.processing ? 'Creating...' : 'Save Employment Type' }}
              </Button>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      <div v-if="lists.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Card v-for="list in lists" :key="list.id" class="flex flex-col">
          <CardHeader class="pb-2">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="h-3 w-3 rounded-full" :style="{ backgroundColor: list.color || '#6366f1' }" />
                <CardTitle class="text-lg">{{ list.name }}</CardTitle>
              </div>
            </div>
          </CardHeader>
          <CardContent class="flex-1">
            <div class="mb-4">
              <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2">Required Files:</p>
              <ul v-if="list.requirements?.length" class="space-y-1">
                <li v-for="req in list.requirements" :key="req" class="text-sm flex items-center gap-2 text-slate-600">
                  <CheckCircle2 class="h-3.5 w-3.5 text-emerald-500" />
                  {{ req }}
                </li>
              </ul>
              <p v-else class="text-sm text-muted-foreground italic">No requirements set.</p>
            </div>

            <div class="flex gap-2 mt-auto pt-4 border-t">
              <Link :href="`/files?list_id=${list.id}`" class="flex-1">
                <Button variant="outline" size="sm" class="w-full">View Files</Button>
              </Link>
              <Button variant="outline" size="sm" @click="openEditDialog(list)">
                <Pencil class="h-4 w-4" />
              </Button>
              <Button variant="destructive" size="sm" @click="deleteList(list.id)" :disabled="deletingListId === list.id">
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <Dialog v-model:open="isEditDialogOpen">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Edit {{ editingList?.name }}</DialogTitle>
          </DialogHeader>
          <form @submit.prevent="updateList" class="space-y-4">
            <div class="space-y-2">
              <Label>Name</Label>
              <Input v-model="editForm.name" required />
            </div>

            <div class="space-y-2">
              <Label>File Checklist</Label>
              <div v-for="(_, index) in editForm.requirements" :key="index" class="flex gap-2 mb-2">
                <Input v-model="editForm.requirements[index]" />
                <Button type="button" variant="ghost" size="icon" @click="removeRequirement(editForm, index)">
                  <Trash2 class="h-4 w-4 text-destructive" />
                </Button>
              </div>
              <Button type="button" variant="outline" size="sm" @click="addRequirement(editForm)">
                <Plus class="h-4 w-4 mr-2" /> Add Requirement
              </Button>
            </div>

            <Button type="submit" class="w-full" :disabled="editForm.processing">
              Update Type
            </Button>
          </form>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
