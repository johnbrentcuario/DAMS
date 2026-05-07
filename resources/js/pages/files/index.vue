<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
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
  DialogTrigger,
  DialogFooter
} from '@/components/ui/dialog'
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger
} from '@/components/ui/tooltip'
import InputError from '@/components/InputError.vue'

import {
  Plus, Loader2, Trash2, CheckCircle2, Circle, Edit2, Eye,
  FilterX, FileUp, FileText, XCircle, RotateCcw, MapPin,
  Archive, RefreshCw, AlertTriangle, FileSearch, Layers,
  FileSpreadsheet, MoveRight, X, SquareCheck
} from 'lucide-vue-next'

/* =======================
    Constants & Types
======================= */
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
  physical_location_id?: number
  physical_path?: string
  attachments: Record<string, string>
  list: {
    id: number
    name: string
    color?: string
    requirements?: string[]
  }
  physical_location?: PhysicalLocation
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
  current_page: number
  per_page: number
  links: { url: string | null; label: string; active: boolean }[]
}

const props = defineProps<{
  files: PaginationFiles
  lists: FileList[]
  physical_locations: PhysicalLocation[]
  filters: { search?: string; list_id?: string; sort?: string; missing_requirement?: string }
}>()

/* =======================
    Logic & Filters
======================= */
const search = ref(props.filters.search || '')
const listId = ref(props.filters.list_id || '')
const missingRequirement = ref(props.filters.missing_requirement || '')
const sort = ref(props.filters.sort || 'asc')

const allPossibleRequirements = computed(() => {
  const reqs = new Set<string>()
  props.lists.forEach(list => {
    list.requirements?.forEach(r => reqs.add(r))
  })
  return Array.from(reqs).sort()
})

watchDebounced(
  [search, listId, sort, missingRequirement],
  () => {
    router.get('/files', {
      search: search.value || undefined,
      list_id: listId.value || undefined,
      missing_requirement: missingRequirement.value || undefined,
      sort: sort.value || undefined
    }, { preserveState: true, preserveScroll: true, replace: true })
  },
  { debounce: 300 }
)

const clearFilters = () => {
  search.value = ''
  listId.value = ''
  missingRequirement.value = ''
  sort.value = 'asc'
}

/* =======================
    Bulk Selection
======================= */
const selectedIds = ref<number[]>([])

const allSelected = computed(() =>
  props.files.data.length > 0 &&
  props.files.data.every(f => selectedIds.value.includes(f.id))
)

const someSelected = computed(() =>
  selectedIds.value.length > 0 && !allSelected.value
)

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = props.files.data.map(f => f.id)
  }
}

const toggleSelect = (id: number) => {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter(i => i !== id)
  } else {
    selectedIds.value = [...selectedIds.value, id]
  }
}

const clearSelection = () => {
  selectedIds.value = []
}

/* =======================
    Bulk Action Modals
======================= */
const isBulkDeleteOpen    = ref(false)
const isBulkMoveOpen      = ref(false)
const isBulkChangeTypeOpen = ref(false)

const bulkMoveForm = useForm({
  ids: [] as number[],
  physical_location_id: '' as number | '',
  physical_path: '',
})

const bulkChangeTypeForm = useForm({
  ids: [] as number[],
  list_id: '' as number | '',
})

const bulkAvailablePaths = computed(() => {
  const loc = props.physical_locations.find(l => l.id === bulkMoveForm.physical_location_id)
  return loc ? loc.storage_paths : []
})

const bulkDelete = () => {
  router.post('/files/bulk-delete', { ids: selectedIds.value }, {
    preserveScroll: true,
    onSuccess: () => {
      isBulkDeleteOpen.value = false
      selectedIds.value = []
    }
  })
}

const bulkMove = () => {
  bulkMoveForm.ids = selectedIds.value
  bulkMoveForm.post('/files/bulk-move', {
    preserveScroll: true,
    onSuccess: () => {
      isBulkMoveOpen.value = false
      bulkMoveForm.reset()
      selectedIds.value = []
    }
  })
}

const bulkChangeType = () => {
  bulkChangeTypeForm.ids = selectedIds.value
  bulkChangeTypeForm.post('/files/bulk-change-type', {
    preserveScroll: true,
    onSuccess: () => {
      isBulkChangeTypeOpen.value = false
      bulkChangeTypeForm.reset()
      selectedIds.value = []
    }
  })
}

const bulkExport = (format: 'pdf' | 'excel') => {
  const ids = selectedIds.value.join(',')
  window.location.href = `/files/bulk-export?ids=${ids}&format=${format}`
}

/* =======================
    Forms & State Management
======================= */
const isCreateDialogOpen    = ref(false)
const isEditDialogOpen      = ref(false)
const isViewDialogOpen      = ref(false)
const isDeleteDialogOpen    = ref(false)
const isOverwriteWarningOpen = ref(false)

const editingFile        = ref<FileRecord | null>(null)
const viewingFile        = ref<FileRecord | null>(null)
const deletingFileRecord = ref<FileRecord | null>(null)

const overwriteData   = ref<{ file: File, requirement: string } | null>(null)
const pendingUploads  = ref<Record<string, File>>({})
const pendingDeletions = ref<string[]>([])

const createForm = useForm({
  fullname: '',
  description: '',
  list_id: '' as number | '',
  physical_location_id: '' as number | '',
  physical_path: ''
})

const editForm = useForm({
  _method: 'put',
  fullname: '',
  description: '',
  list_id: '' as number | '',
  physical_location_id: '' as number | '',
  physical_path: '',
  new_attachments: {} as Record<string, File>,
  delete_attachments: [] as string[]
})

const availableCreatePaths = computed(() => {
  const loc = props.physical_locations?.find(l => l.id === createForm.physical_location_id)
  return loc ? loc.storage_paths : []
})

const availableEditPaths = computed(() => {
  const loc = props.physical_locations?.find(l => l.id === editForm.physical_location_id)
  return loc ? loc.storage_paths : []
})

const openViewDialog = (file: FileRecord) => {
  viewingFile.value = file
  isViewDialogOpen.value = true
}

const openEditDialog = (file: FileRecord) => {
  editingFile.value = { ...file }
  editForm.fullname = file.fullname
  editForm.description = file.description || ''
  editForm.list_id = file.list_id
  editForm.physical_location_id = file.physical_location_id || ''
  editForm.physical_path = file.physical_path || ''
  pendingUploads.value = {}
  pendingDeletions.value = []
  isEditDialogOpen.value = true
}

const openDeleteModal = (file: FileRecord) => {
  deletingFileRecord.value = file
  isDeleteDialogOpen.value = true
}

const handleFileUploadLocal = (event: Event, requirement: string) => {
  const target = event.target as HTMLInputElement
  if (!target.files?.length) return
  const file = target.files[0]
  const hasExistingFile = editingFile.value?.attachments?.[requirement]
  const hasPendingFile  = pendingUploads.value[requirement]
  if (hasExistingFile || hasPendingFile) {
    overwriteData.value = { file, requirement }
    isOverwriteWarningOpen.value = true
    target.value = ''
    return
  }
  processFileSelection(file, requirement)
  target.value = ''
}

const confirmOverwrite = () => {
  if (overwriteData.value) {
    processFileSelection(overwriteData.value.file, overwriteData.value.requirement)
    isOverwriteWarningOpen.value = false
    overwriteData.value = null
  }
}

const processFileSelection = (file: File, requirement: string) => {
  const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png']
  if (!allowedTypes.includes(file.type)) {
    alert('Invalid file format. Only PDF, JPEG, and PNG are allowed.')
    return
  }
  pendingUploads.value[requirement] = file
  pendingDeletions.value = pendingDeletions.value.filter(req => req !== requirement)
}

const removeFileLocal = (requirement: string) => {
  if (pendingUploads.value[requirement]) {
    delete pendingUploads.value[requirement]
  } else {
    if (!pendingDeletions.value.includes(requirement)) {
      pendingDeletions.value.push(requirement)
    }
  }
}

const restoreFileLocal = (requirement: string) => {
  pendingDeletions.value = pendingDeletions.value.filter(req => req !== requirement)
}

const getStatus = (req: string) => {
  if (pendingDeletions.value.includes(req)) return 'deleted'
  if (pendingUploads.value[req]) return 'new'
  if (editingFile.value?.attachments?.[req]) return 'exists'
  return 'empty'
}

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
  editForm.new_attachments    = pendingUploads.value
  editForm.delete_attachments = pendingDeletions.value
  editForm.post(`/files/${editingFile.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      isEditDialogOpen.value = false
      editForm.reset()
      pendingUploads.value  = {}
      pendingDeletions.value = []
    }
  })
}

const confirmDeletion = () => {
  if (!deletingFileRecord.value) return
  router.delete(`/files/${deletingFileRecord.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      isDeleteDialogOpen.value  = false
      deletingFileRecord.value  = null
    }
  })
}

const selectStyle = "w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none transition-shadow"
</script>

<template>
  <Head title="Folders Management" />
  <TooltipProvider>
    <AppLayout>
      <div class="p-6 space-y-6 w-full max-w-7xl mx-auto">

        <!-- Header -->
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Personnel Folder</h1>
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
                    <Label>Employment Type</Label>
                    <select v-model="createForm.list_id" :class="selectStyle" required>
                      <option value="">Select Type</option>
                      <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
                    </select>
                  </div>
                  <div class="space-y-2">
                    <Label>Location</Label>
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

        <!-- Filters -->
        <Card class="shadow-sm border-muted">
          <CardContent class="grid md:grid-cols-5 gap-4 pt-2 pb-2">
            <Input v-model="search" placeholder="Search by name..." />
            <select v-model="listId" :class="selectStyle">
              <option value="">All Employment Types</option>
              <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
            </select>
            <div class="relative">
              <select v-model="missingRequirement" :class="selectStyle + ' text-destructive font-medium border-destructive/20 bg-destructive/[0.02]'">
                <option value="" class="text-foreground">All Documents Present</option>
                <optgroup label="Show Records Missing:">
                  <option v-for="req in allPossibleRequirements" :key="req" :value="req">
                    Missing: {{ req }}
                  </option>
                </optgroup>
              </select>
            </div>
            <select v-model="sort" :class="selectStyle">
              <option value="asc">Name (A-Z)</option>
              <option value="desc">Name (Z-A)</option>
            </select>
            <div class="flex items-center">
              <Tooltip v-if="search || listId || missingRequirement || sort !== 'asc'">
                <TooltipTrigger as-child>
                  <Button variant="ghost" @click="clearFilters" class="text-muted-foreground hover:text-destructive w-full md:w-auto">
                    <FilterX class="h-4 w-4 mr-2" /> Reset
                  </Button>
                </TooltipTrigger>
                <TooltipContent>Clear all search filters</TooltipContent>
              </Tooltip>
            </div>
          </CardContent>
        </Card>

        <!-- Missing filter warning -->
        <div v-if="missingRequirement" class="bg-destructive/10 text-destructive px-4 py-2 rounded-lg flex items-center gap-2 text-sm font-medium animate-in fade-in slide-in-from-left-2">
          <FileSearch class="h-4 w-4" />
          Filtering personnel missing: <span class="underline decoration-2">{{ missingRequirement }}</span>
        </div>

        <!-- Bulk Action Toolbar -->
        <Transition
          enter-active-class="transition-all duration-200 ease-out"
          enter-from-class="opacity-0 -translate-y-2"
          leave-active-class="transition-all duration-150 ease-in"
          leave-to-class="opacity-0 -translate-y-2"
        >
          <div v-if="selectedIds.length > 0"
            class="flex flex-wrap items-center gap-2 px-4 py-3 rounded-xl border border-primary/20 bg-primary/5 dark:bg-primary/10"
          >
            <div class="flex items-center gap-2 mr-2">
              <SquareCheck class="h-4 w-4 text-primary" />
              <span class="text-sm font-semibold text-primary">{{ selectedIds.length }} selected</span>
            </div>

            <!-- Change Employment Type -->
            <Button variant="outline" size="sm" class="h-8 gap-1.5 text-xs" @click="isBulkChangeTypeOpen = true">
              <Layers class="h-3.5 w-3.5" />
              Change Type
            </Button>

            <!-- Move Location -->
            <Button variant="outline" size="sm" class="h-8 gap-1.5 text-xs" @click="isBulkMoveOpen = true">
              <MoveRight class="h-3.5 w-3.5" />
              Move Location
            </Button>

            <!-- Export Excel -->
            <Button variant="outline" size="sm" class="h-8 gap-1.5 text-xs text-emerald-600 border-emerald-200 hover:bg-emerald-50" @click="bulkExport('excel')">
              <FileSpreadsheet class="h-3.5 w-3.5" />
              Export Excel
            </Button>

            <!-- Export PDF -->
            <Button variant="outline" size="sm" class="h-8 gap-1.5 text-xs text-red-600 border-red-200 hover:bg-red-50" @click="bulkExport('pdf')">
              <FileText class="h-3.5 w-3.5" />
              Export PDF
            </Button>

            <!-- Delete -->
            <Button variant="outline" size="sm" class="h-8 gap-1.5 text-xs text-destructive border-destructive/20 hover:bg-destructive/10" @click="isBulkDeleteOpen = true">
              <Trash2 class="h-3.5 w-3.5" />
              Delete
            </Button>

            <!-- Clear selection -->
            <Button variant="ghost" size="sm" class="h-8 gap-1.5 text-xs text-muted-foreground ml-auto" @click="clearSelection">
              <X class="h-3.5 w-3.5" />
              Clear
            </Button>
          </div>
        </Transition>

        <!-- Table -->
        <Card class="border-none shadow-sm overflow-hidden">
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-muted/40 border-b">
                  <tr>
                    <th class="p-4 pl-6 w-10">
                      <input
                        type="checkbox"
                        :checked="allSelected"
                        :indeterminate="someSelected"
                        @change="toggleSelectAll"
                        class="rounded border-gray-300 cursor-pointer"
                      />
                    </th>
                    <th class="p-4 text-left font-semibold w-12">No.</th>
                    <th class="p-4 text-left font-semibold">Full Name</th>
                    <th class="p-4 text-left font-semibold">Employment Type</th>
                    <th class="p-4 text-left font-semibold">Location</th>
                    <th class="p-4 text-left font-semibold hidden lg:table-cell">Remarks</th>
                    <th class="p-4 text-center font-semibold w-40">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y">
                  <tr v-for="(file, index) in files.data" :key="file.id"
                    class="hover:bg-muted/10 transition-colors group"
                    :class="{ 'bg-primary/5': selectedIds.includes(file.id) }"
                  >
                    <td class="p-4 pl-6">
                      <input
                        type="checkbox"
                        :checked="selectedIds.includes(file.id)"
                        @change="toggleSelect(file.id)"
                        class="rounded border-gray-300 cursor-pointer"
                      />
                    </td>
                    <td class="p-4 text-muted-foreground font-mono text-xs">
                      {{ (props.files.current_page - 1) * props.files.per_page + index + 1 }}
                    </td>
                    <td class="p-4 font-medium">{{ file.fullname }}</td>
                    <td class="p-4">
                      <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: file.list?.color || '#94a3b8' }"></div>
                        <span>{{ file.list?.name }}</span>
                      </div>
                    </td>
                    <td class="p-4">
                      <div v-if="file.physical_location" class="flex flex-col">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
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
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" @click="openViewDialog(file)" class="h-8 w-8 text-blue-600 hover:bg-blue-50">
                              <Eye class="h-4 w-4" />
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>View Folder Details</TooltipContent>
                        </Tooltip>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" @click="openEditDialog(file)" class="h-8 w-8 hover:bg-muted">
                              <Edit2 class="h-4 w-4" />
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>Edit Record & Files</TooltipContent>
                        </Tooltip>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" @click="openDeleteModal(file)" class="h-8 w-8 text-destructive hover:bg-destructive/10">
                              <Trash2 class="h-4 w-4" />
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>Delete Folder</TooltipContent>
                        </Tooltip>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="files.data.length === 0">
                    <td colspan="7" class="p-12 text-center text-muted-foreground italic">
                      No records found matching these filters.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="p-4 border-t dark:border-slate-800 flex items-center justify-between bg-muted/5">
              <div class="flex gap-1">
                <Link
                  v-for="link in files.links"
                  :key="link.label"
                  :href="link.url || '#'"
                  v-html="link.label"
                  class="px-3 py-1.5 border dark:border-slate-700 rounded-md text-xs font-medium transition-colors"
                  :class="{
                    'bg-primary text-primary-foreground border-primary': link.active,
                    'text-foreground hover:bg-muted': !link.active && link.url,
                    'opacity-40 pointer-events-none': !link.url
                  }"
                />
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- BULK DELETE CONFIRM -->
        <Dialog v-model:open="isBulkDeleteOpen">
          <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle class="flex items-center gap-2">
                <Trash2 class="h-5 w-5 text-destructive" />
                Delete {{ selectedIds.length }} Folder(s)?
              </DialogTitle>
              <DialogDescription class="py-3">
                This will permanently delete <span class="font-bold text-slate-900 dark:text-white">{{ selectedIds.length }}</span> folder(s) and all their attachments. This action cannot be undone.
              </DialogDescription>
            </DialogHeader>
            <div class="flex justify-end gap-3 mt-2">
              <Button variant="outline" @click="isBulkDeleteOpen = false">Cancel</Button>
              <Button variant="destructive" @click="bulkDelete">
                <Trash2 class="h-4 w-4 mr-2" /> Delete All
              </Button>
            </div>
          </DialogContent>
        </Dialog>

        <!-- BULK MOVE LOCATION -->
        <Dialog v-model:open="isBulkMoveOpen">
          <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle class="flex items-center gap-2">
                <MoveRight class="h-5 w-5" />
                Move {{ selectedIds.length }} Folder(s)
              </DialogTitle>
              <DialogDescription>Select a new physical location for the selected folders.</DialogDescription>
            </DialogHeader>
            <div class="space-y-4 pt-4">
              <div class="space-y-2">
                <Label>Location</Label>
                <select v-model="bulkMoveForm.physical_location_id" :class="selectStyle">
                  <option value="">None / External</option>
                  <option v-for="loc in physical_locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                </select>
              </div>
              <div v-if="bulkMoveForm.physical_location_id" class="space-y-2">
                <Label>Storage Path</Label>
                <select v-model="bulkMoveForm.physical_path" :class="selectStyle">
                  <option value="">Select Path</option>
                  <option v-for="path in bulkAvailablePaths" :key="path" :value="path">{{ path }}</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
              <Button variant="outline" @click="isBulkMoveOpen = false">Cancel</Button>
              <Button @click="bulkMove" :disabled="bulkMoveForm.processing">
                <Loader2 v-if="bulkMoveForm.processing" class="h-4 w-4 mr-2 animate-spin" />
                Move Folders
              </Button>
            </div>
          </DialogContent>
        </Dialog>

        <!-- BULK CHANGE TYPE -->
        <Dialog v-model:open="isBulkChangeTypeOpen">
          <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle class="flex items-center gap-2">
                <Layers class="h-5 w-5" />
                Change Type for {{ selectedIds.length }} Folder(s)
              </DialogTitle>
              <DialogDescription>Select a new employment type for the selected folders.</DialogDescription>
            </DialogHeader>
            <div class="space-y-4 pt-4">
              <div class="space-y-2">
                <Label>Employment Type</Label>
                <select v-model="bulkChangeTypeForm.list_id" :class="selectStyle" required>
                  <option value="">Select Type</option>
                  <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
              <Button variant="outline" @click="isBulkChangeTypeOpen = false">Cancel</Button>
              <Button @click="bulkChangeType" :disabled="bulkChangeTypeForm.processing || !bulkChangeTypeForm.list_id">
                <Loader2 v-if="bulkChangeTypeForm.processing" class="h-4 w-4 mr-2 animate-spin" />
                Update Type
              </Button>
            </div>
          </DialogContent>
        </Dialog>

        <!-- EDIT DIALOG -->
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
                    <Label>Employment Type</Label>
                    <select v-model="editForm.list_id" :class="selectStyle" required>
                      <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
                    </select>
                  </div>
                  <div class="space-y-2">
                    <Label>Location</Label>
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
                    class="flex items-center justify-between p-3 rounded-lg border bg-card transition-all hover:border-muted-foreground/30"
                    :class="[
                      getStatus(req) === 'deleted' ? 'opacity-60 border-destructive bg-destructive/5' : '',
                      getStatus(req) === 'exists' ? 'bg-slate-50/50' : ''
                    ]"
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
                      <Tooltip v-if="getStatus(req) === 'exists'">
                        <TooltipTrigger as-child>
                          <Button variant="ghost" size="icon" as-child class="h-8 w-8 text-blue-600">
                            <a :href="`/storage/${editingFile.attachments[req]}`" target="_blank">
                              <Eye class="h-4 w-4" />
                            </a>
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Open Document</TooltipContent>
                      </Tooltip>
                      <Tooltip v-if="getStatus(req) === 'exists' || getStatus(req) === 'new'">
                        <TooltipTrigger as-child>
                          <Button variant="ghost" size="icon" @click="removeFileLocal(req)" class="h-8 w-8 text-destructive hover:bg-destructive/10">
                            <Trash2 class="h-4 w-4" />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Remove attachment</TooltipContent>
                      </Tooltip>
                      <Tooltip v-if="getStatus(req) === 'deleted'">
                        <TooltipTrigger as-child>
                          <Button variant="ghost" size="icon" @click="restoreFileLocal(req)" class="h-8 w-8 text-primary">
                            <RotateCcw class="h-4 w-4" />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Restore original file</TooltipContent>
                      </Tooltip>
                      <div class="relative" v-if="getStatus(req) !== 'deleted'">
                        <input :id="`edit-input-${req}`" type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileUploadLocal($event, req)" />
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" as-child
                              :class="['h-8 w-8 transition-all', (getStatus(req) === 'exists' || getStatus(req) === 'new') ? 'text-amber-500 hover:text-amber-600 hover:bg-amber-50' : 'text-muted-foreground hover:text-primary']"
                            >
                              <label :for="`edit-input-${req}`" class="cursor-pointer flex items-center justify-center">
                                <RefreshCw v-if="getStatus(req) === 'exists' || getStatus(req) === 'new'" class="h-4 w-4" />
                                <FileUp v-else class="h-4 w-4" />
                              </label>
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>{{ (getStatus(req) === 'exists' || getStatus(req) === 'new') ? 'Replace File' : 'Upload File' }}</TooltipContent>
                        </Tooltip>
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

        <!-- VIEW DIALOG -->
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
                  <Label class="text-xs text-muted-foreground uppercase tracking-wider">Employment Type</Label>
                  <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: viewingFile.list?.color }"></div>
                    <p class="font-medium">{{ viewingFile.list?.name }}</p>
                  </div>
                </div>
              </div>
              <div class="space-y-1">
                <Label class="text-xs text-muted-foreground uppercase tracking-wider">Location</Label>
                <div v-if="viewingFile.physical_location" class="flex items-center gap-2 p-3 rounded-md border bg-slate-50/50">
                  <MapPin class="h-4 w-4" :style="{ color: viewingFile.physical_location.color }" />
                  <div>
                    <p class="font-medium text-slate-800">{{ viewingFile.physical_location.name }}</p>
                    <p class="text-xs text-muted-foreground">{{ viewingFile.physical_path }}</p>
                  </div>
                </div>
                <p v-else class="text-sm italic text-muted-foreground">Not assigned to a location.</p>
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

        <!-- DELETE CONFIRMATION -->
        <Dialog v-model:open="isDeleteDialogOpen">
          <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle class="flex items-center gap-2">
                <XCircle class="h-5 w-5 text-destructive" />
                Confirm Deletion
              </DialogTitle>
              <DialogDescription class="py-3">
                Are you sure you want to delete the record for
                <span class="font-bold text-slate-900 dark:text-white">{{ deletingFileRecord?.fullname }}</span>?
                This action cannot be undone and all associated attachments will be removed.
              </DialogDescription>
            </DialogHeader>
            <div class="flex justify-end gap-3 mt-4">
              <Button variant="outline" @click="isDeleteDialogOpen = false">Cancel</Button>
              <Button variant="destructive" @click="confirmDeletion">
                <Trash2 class="h-4 w-4 mr-2" /> Delete Record
              </Button>
            </div>
          </DialogContent>
        </Dialog>

        <!-- OVERWRITE WARNING -->
        <Dialog v-model:open="isOverwriteWarningOpen">
          <DialogContent class="sm:max-w-[400px]">
            <DialogHeader>
              <div class="mx-auto bg-amber-100 p-3 rounded-full w-fit mb-4">
                <AlertTriangle class="h-6 w-6 text-amber-600" />
              </div>
              <DialogTitle class="text-center">Overwrite File?</DialogTitle>
              <DialogDescription class="text-center pt-2">
                A document is already assigned to <span class="font-bold text-foreground">"{{ overwriteData?.requirement }}"</span>.
                Uploading a new one will replace the existing file.
              </DialogDescription>
            </DialogHeader>
            <DialogFooter class="sm:justify-center gap-2 mt-4">
              <Button variant="outline" @click="isOverwriteWarningOpen = false" class="flex-1">Keep Current</Button>
              <Button variant="default" @click="confirmOverwrite" class="flex-1 bg-amber-600 hover:bg-amber-700">
                Yes, Overwrite
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

      </div>
    </AppLayout>
  </TooltipProvider>
</template>
