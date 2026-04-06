<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue' // Added computed for path logic
import { watchDebounced } from '@vueuse/core'

import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
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

import {
  Plus,
  Loader2,
  Trash2,
  CheckCircle2,
  Circle,
  Edit2,
  Eye,
  FilterX,
  FileUp,
  FileText,
  XCircle,
  RotateCcw,
  MapPin, // NEW
  Archive // NEW
} from 'lucide-vue-next'

/* =======================
    Constants & Types
======================= */
// NEW: Location Interface
interface PhysicalLocation {
  id: number
  name: string
  color: string
  storage_paths: string[]
}

interface FileRecord {
  id: number
  fullname: string
  description?: string
  list_id: number
  physical_location_id?: number // NEW
  physical_path?: string        // NEW
  attachments: Record<string, string>
  list: {
    id: number
    name: string
    color?: string
    requirements?: string[]
  }
  physical_location?: PhysicalLocation // NEW
}

interface FileList {
  id: number
  name: string
  color?: string
  requirements?: string[]
}

interface PaginationFiles {
  data: FileRecord[]
  total: number
  links: { url: string | null; label: string; active: boolean }[]
}

const props = defineProps<{
  files: PaginationFiles
  lists: FileList[]
  physical_locations: PhysicalLocation[] // NEW: Expected from Controller
  filters: { search?: string; list_id?: string }
}>()

/* =======================
    Logic & Filters
======================= */
const search = ref(props.filters.search || '')
const listId = ref(props.filters.list_id || '')

watchDebounced(
  [search, listId],
  () => {
    router.get('/files', {
      search: search.value || undefined,
      list_id: listId.value || undefined
    }, { preserveState: true, preserveScroll: true, replace: true })
  },
  { debounce: 300 }
)

const clearFilters = () => {
  search.value = ''
  listId.value = ''
}

/* =======================
    Forms & State Management
======================= */
const isCreateDialogOpen = ref(false)
const isEditDialogOpen = ref(false)
const isViewDialogOpen = ref(false)

const editingFile = ref<FileRecord | null>(null)
const viewingFile = ref<FileRecord | null>(null)
const deletingFileId = ref<number | null>(null)

const pendingUploads = ref<Record<string, File>>({})
const pendingDeletions = ref<string[]>([])

const createForm = useForm({
  fullname: '',
  description: '',
  list_id: '',
  physical_location_id: '' as number | '', // NEW
  physical_path: ''                         // NEW
})

const editForm = useForm({
  _method: 'put',
  fullname: '',
  description: '',
  list_id: '' as number | '',
  physical_location_id: '' as number | '', // NEW
  physical_path: '',                        // NEW
  new_attachments: {} as Record<string, File>,
  delete_attachments: [] as string[]
})

/* =======================
    Location Logic (Computed)
======================= */
// Filters the available paths based on which Office is selected in Create Form
const availableCreatePaths = computed(() => {
    const loc = props.physical_locations?.find(l => l.id === createForm.physical_location_id);
    return loc ? loc.storage_paths : [];
});

// Filters the available paths based on which Office is selected in Edit Form
const availableEditPaths = computed(() => {
    const loc = props.physical_locations?.find(l => l.id === editForm.physical_location_id);
    return loc ? loc.storage_paths : [];
});

/* =======================
    Dialog Actions
======================= */
const openViewDialog = (file: FileRecord) => {
  viewingFile.value = file
  isViewDialogOpen.value = true
}

const openEditDialog = (file: FileRecord) => {
  editingFile.value = { ...file }
  editForm.fullname = file.fullname
  editForm.description = file.description || ''
  editForm.list_id = file.list_id

  // NEW: Populate physical location fields
  editForm.physical_location_id = file.physical_location_id || ''
  editForm.physical_path = file.physical_path || ''

  pendingUploads.value = {}
  pendingDeletions.value = []
  isEditDialogOpen.value = true
}

/* =======================
    File Logic (Existing)
======================= */
const handleFileUploadLocal = (event: Event, requirement: string) => {
  const target = event.target as HTMLInputElement;
  if (!target.files?.length) return;
  const file = target.files[0];
  pendingUploads.value[requirement] = file;
  pendingDeletions.value = pendingDeletions.value.filter(req => req !== requirement);
  target.value = '';
};

const removeFileLocal = (requirement: string) => {
  if (pendingUploads.value[requirement]) {
    delete pendingUploads.value[requirement];
  } else {
    if (!pendingDeletions.value.includes(requirement)) {
      pendingDeletions.value.push(requirement);
    }
  }
};

const restoreFileLocal = (requirement: string) => {
  pendingDeletions.value = pendingDeletions.value.filter(req => req !== requirement);
};

const getStatus = (req: string) => {
  if (pendingDeletions.value.includes(req)) return 'deleted';
  if (pendingUploads.value[req]) return 'new';
  if (editingFile.value?.attachments?.[req]) return 'exists';
  return 'empty';
};

/* =======================
    Server Submissions
======================= */
const createFile = () => {
  createForm.post('/files', {
    onSuccess: () => {
      isCreateDialogOpen.value = false
      createForm.reset()
    }
  })
}

const updateFile = () => {
  if (!editingFile.value) return
  editForm.new_attachments = pendingUploads.value;
  editForm.delete_attachments = pendingDeletions.value;

  editForm.post(`/files/${editingFile.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      isEditDialogOpen.value = false
      editForm.reset()
      pendingUploads.value = {}
      pendingDeletions.value = []
    }
  })
}

const deleteFileRecord = (id: number) => {
  if (!confirm('Permanently delete this personnel record?')) return
  deletingFileId.value = id
  router.delete(`/files/${id}`, { onFinish: () => deletingFileId.value = null })
}

const selectStyle = "w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none transition-shadow";
</script>

<template>
  <Head title="Folders Management" />

  <AppLayout>
    <div class="p-6 space-y-6 w-full max-w-7xl mx-auto">

      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-slate-900">Personnel Files</h1>
          <p class="text-muted-foreground">{{ files.total }} Records Found</p>
        </div>

        <Dialog v-model:open="isCreateDialogOpen">
          <DialogTrigger as-child>
            <Button size="lg" class="shadow-sm transition-all active:scale-95">
              <Plus class="h-5 w-5 mr-2" /> New Folder
            </Button>
          </DialogTrigger>
          <DialogContent class="sm:max-w-[500px]">
            <DialogHeader><DialogTitle>Create New Record</DialogTitle></DialogHeader>
            <form @submit.prevent="createFile" class="space-y-4 pt-4">
              <div class="space-y-2">
                <Label>Full Name</Label>
                <Input v-model="createForm.fullname" placeholder="Enter employee name" required />
                <InputError :message="createForm.errors.fullname" />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label>Category</Label>
                    <select v-model="createForm.list_id" :class="selectStyle" required>
                    <option value="">Select Type</option>
                    <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <Label>Physical Office</Label>
                    <select v-model="createForm.physical_location_id" :class="selectStyle">
                    <option value="">None / External</option>
                    <option v-for="loc in physical_locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                    </select>
                </div>
              </div>

              <div v-if="createForm.physical_location_id" class="space-y-2 animate-in fade-in slide-in-from-top-1">
                <Label>Storage Unit / Drawer</Label>
                <select v-model="createForm.physical_path" :class="selectStyle" required>
                    <option value="">Select Location</option>
                    <option v-for="path in availableCreatePaths" :key="path" :value="path">{{ path }}</option>
                </select>
              </div>

              <div class="space-y-2">
                <Label>Remarks</Label>
                <textarea v-model="createForm.description" :class="selectStyle" class="min-h-[100px] resize-none" />
              </div>
              <Button type="submit" class="w-full" :disabled="createForm.processing">
                <Loader2 v-if="createForm.processing" class="h-4 w-4 mr-2 animate-spin" />
                Create Record
              </Button>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      <Card class="shadow-sm border-muted">
        <CardContent class="grid md:grid-cols-3 gap-4 pt-6">
          <Input v-model="search" placeholder="Search by name..." />
          <select v-model="listId" :class="selectStyle">
            <option value="">All Categories</option>
            <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
          </select>
          <div class="flex items-center">
            <Button v-if="search || listId" variant="ghost" @click="clearFilters" class="text-muted-foreground hover:text-destructive">
              <FilterX class="h-4 w-4 mr-2" /> Reset Filters
            </Button>
          </div>
        </CardContent>
      </Card>

      <Card class="border-none shadow-sm overflow-hidden">
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-muted/40 border-b">
                <tr>
                  <th class="p-4 text-left font-semibold pl-6">Full Name</th>
                  <th class="p-4 text-left font-semibold">Category</th>
                  <th class="p-4 text-left font-semibold">Location</th> <th class="p-4 text-left font-semibold hidden lg:table-cell">Remarks</th>
                  <th class="p-4 text-center font-semibold w-40">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr v-for="file in files.data" :key="file.id" class="hover:bg-muted/10 transition-colors group">
                  <td class="p-4 font-medium pl-6">{{ file.fullname }}</td>
                  <td class="p-4">
                    <div class="flex items-center gap-2">
                      <div class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: file.list?.color || '#94a3b8' }"></div>
                      <span>{{ file.list?.name }}</span>
                    </div>
                  </td>
                  <td class="p-4">
                    <div v-if="file.physical_location" class="flex flex-col">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                            <MapPin class="h-3 w-3" :style="{ color: file.physical_location.color }" />
                            {{ file.physical_location.name }}
                        </div>
                        <div class="text-[10px] text-muted-foreground pl-4 flex items-center gap-1">
                            <Archive class="h-2.5 w-2.5" /> {{ file.physical_path || 'Main Room' }}
                        </div>
                    </div>
                    <span v-else class="text-xs text-muted-foreground italic">No mapping</span>
                  </td>
                  <td class="p-4 text-muted-foreground hidden lg:table-cell italic text-xs">
                    {{ file.description || 'N/A' }}
                  </td>
                  <td class="p-4">
                    <div class="flex justify-center gap-1 opacity-90 group-hover:opacity-100">
                      <Button variant="ghost" size="icon" @click="openViewDialog(file)" class="h-8 w-8 text-blue-600 hover:bg-blue-50">
                        <Eye class="h-4 w-4" />
                      </Button>
                      <Button variant="ghost" size="icon" @click="openEditDialog(file)" class="h-8 w-8 hover:bg-muted">
                        <Edit2 class="h-4 w-4" />
                      </Button>
                      <Button variant="ghost" size="icon" @click="deleteFileRecord(file.id)" class="h-8 w-8 text-destructive hover:bg-destructive/10">
                        <Loader2 v-if="deletingFileId === file.id" class="h-4 w-4 animate-spin" />
                        <Trash2 v-else class="h-4 w-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="p-4 border-t flex items-center justify-between bg-muted/5">
            <div class="flex gap-1">
              <Link v-for="link in files.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
                class="px-3 py-1.5 border rounded-md text-xs font-medium"
                :class="{ 'bg-primary text-white border-primary': link.active, 'opacity-40 pointer-events-none': !link.url }"
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <Dialog v-model:open="isEditDialogOpen">
        <DialogContent class="sm:max-w-[600px] max-h-[90vh] flex flex-col p-0 overflow-hidden">
          <DialogHeader class="p-6 pb-2 border-b">
            <DialogTitle>Edit Record & Files</DialogTitle>
          </DialogHeader>

          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <form @submit.prevent="updateFile" id="edit-form" class="space-y-4">
              <div class="space-y-2"><Label>Full Name</Label><Input v-model="editForm.fullname" required /></div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label>Category</Label>
                    <select v-model="editForm.list_id" :class="selectStyle" required>
                    <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <Label>Physical Office</Label>
                    <select v-model="editForm.physical_location_id" :class="selectStyle">
                        <option value="">None / External</option>
                        <option v-for="loc in physical_locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                    </select>
                </div>
              </div>

              <div v-if="editForm.physical_location_id" class="space-y-2">
                <Label>Specific Storage Path</Label>
                <select v-model="editForm.physical_path" :class="selectStyle" required>
                    <option value="">Select Location</option>
                    <option v-for="path in availableEditPaths" :key="path" :value="path">{{ path }}</option>
                </select>
              </div>

              <div class="space-y-2"><Label>Remarks</Label><textarea v-model="editForm.description" :class="selectStyle" class="min-h-[80px] resize-none" /></div>
            </form>

            <div v-if="editingFile" class="pt-6 border-t space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold flex items-center gap-2 text-primary">
                  <FileText class="h-4 w-4" /> Documents Checklist
                </h3>
                <span class="text-[10px] text-muted-foreground uppercase font-bold">Changes will apply on save</span>
              </div>

              <div v-if="editingFile.list?.requirements?.length" class="grid gap-2">
                <div v-for="req in editingFile.list.requirements" :key="req"
                  class="flex items-center justify-between p-3 rounded-lg border bg-card transition-all"
                  :class="{ 'opacity-60 border-destructive bg-destructive/5': getStatus(req) === 'deleted' }"
                >
                  <div class="flex items-center gap-3 overflow-hidden">
                    <CheckCircle2 v-if="getStatus(req) === 'exists'" class="h-5 w-5 text-green-500 shrink-0" />
                    <FileUp v-else-if="getStatus(req) === 'new'" class="h-5 w-5 text-blue-500 shrink-0 animate-pulse" />
                    <Trash2 v-else-if="getStatus(req) === 'deleted'" class="h-5 w-5 text-destructive shrink-0" />
                    <Circle v-else class="h-5 w-5 text-muted-foreground/30 shrink-0" />

                    <div class="flex flex-col overflow-hidden">
                      <span class="text-sm font-medium truncate">{{ req }}</span>
                      <span v-if="getStatus(req) === 'new'" class="text-[10px] text-blue-600 font-bold uppercase">Pending Upload</span>
                      <span v-if="getStatus(req) === 'deleted'" class="text-[10px] text-destructive font-bold uppercase">Marked for removal</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-1">
                    <Button v-if="getStatus(req) === 'exists'" variant="ghost" size="icon" as-child class="h-8 w-8 text-blue-600">
                      <a :href="`/storage/${editingFile.attachments[req]}`" target="_blank">
                        <Eye class="h-4 w-4" />
                      </a>
                    </Button>

                    <Button v-if="getStatus(req) === 'exists' || getStatus(req) === 'new'"
                      variant="ghost" size="icon" @click="removeFileLocal(req)" class="h-8 w-8 text-destructive hover:bg-destructive/10"
                    >
                      <Trash2 class="h-4 w-4" />
                    </Button>

                    <Button v-if="getStatus(req) === 'deleted'"
                      variant="ghost" size="icon" @click="restoreFileLocal(req)" class="h-8 w-8 text-primary"
                    >
                      <RotateCcw class="h-4 w-4" />
                    </Button>

                    <div class="relative" v-if="getStatus(req) !== 'deleted'">
                      <input :id="`edit-input-${req}`" type="file" class="hidden" @change="handleFileUploadLocal($event, req)" />
                      <Button variant="ghost" size="icon" as-child class="h-8 w-8 text-muted-foreground hover:text-primary">
                        <label :for="`edit-input-${req}`" class="cursor-pointer flex items-center justify-center">
                          <FileUp class="h-4 w-4" />
                        </label>
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="p-4 border-t flex justify-end gap-3 bg-muted/10">
            <Button variant="outline" @click="isEditDialogOpen = false">Cancel</Button>
            <Button type="submit" form="edit-form" :disabled="editForm.processing">
              <Loader2 v-if="editForm.processing" class="h-4 w-4 mr-2 animate-spin" />
              Save Changes
            </Button>
          </div>
        </DialogContent>
      </Dialog>

      <Dialog v-model:open="isViewDialogOpen">
        <DialogContent class="sm:max-w-[550px] max-h-[85vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>Personnel Details</DialogTitle>
            <DialogDescription>Full record view and document status</DialogDescription>
          </DialogHeader>

          <div v-if="viewingFile" class="space-y-6 pt-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground uppercase tracking-wider">Full Name</Label>
                <p class="font-semibold text-lg">{{ viewingFile.fullname }}</p>
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground uppercase tracking-wider">Category</Label>
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: viewingFile.list?.color }"></div>
                  <p class="font-medium">{{ viewingFile.list?.name }}</p>
                </div>
              </div>
            </div>

            <div class="space-y-1">
                <Label class="text-xs text-muted-foreground uppercase tracking-wider">Physical Home</Label>
                <div v-if="viewingFile.physical_location" class="flex items-center gap-2 p-3 rounded-md border bg-slate-50/50">
                  <MapPin class="h-4 w-4" :style="{ color: viewingFile.physical_location.color }" />
                  <div>
                      <p class="font-medium text-slate-800">{{ viewingFile.physical_location.name }}</p>
                      <p class="text-xs text-muted-foreground">{{ viewingFile.physical_path }}</p>
                  </div>
                </div>
                <p v-else class="text-sm italic text-muted-foreground">Not assigned to a physical office.</p>
            </div>

            <div class="space-y-1">
              <Label class="text-xs text-muted-foreground uppercase tracking-wider">Remarks</Label>
              <p class="text-sm bg-muted/30 p-3 rounded-md italic border">
                {{ viewingFile.description || 'No additional remarks provided.' }}
              </p>
            </div>

            <div class="space-y-3">
              <Label class="text-xs text-muted-foreground uppercase tracking-wider">Document Checklist</Label>
              <div class="grid gap-2">
                <div v-for="req in viewingFile.list?.requirements" :key="req"
                     class="flex items-center justify-between p-3 border rounded-lg bg-background">
                  <div class="flex items-center gap-3">
                    <CheckCircle2 v-if="viewingFile.attachments?.[req]" class="h-5 w-5 text-green-500" />
                    <XCircle v-else class="h-5 w-5 text-destructive/40" />
                    <span class="text-sm font-medium">{{ req }}</span>
                  </div>
                  <div v-if="viewingFile.attachments?.[req]">
                    <Button variant="outline" size="sm" as-child class="h-8 gap-2">
                      <a :href="`/storage/${viewingFile.attachments[req]}`" target="_blank">
                        <Eye class="h-3.5 w-3.5" /> View
                      </a>
                    </Button>
                  </div>
                  <span v-else class="text-xs font-bold text-destructive uppercase">Missing</span>
                </div>
              </div>
            </div>
          </div>
        </DialogContent>
      </Dialog>

    </div>
  </AppLayout>
</template>
