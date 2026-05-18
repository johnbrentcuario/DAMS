<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { watchDebounced } from '@vueuse/core'

import { Button } from '@/components/ui/button'
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
  FileSpreadsheet, MoveRight, X, SquareCheck, Search, MinusCircle,
  Scissors, CalendarDays
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

interface SeparationMode {
  id: number
  name: string
}

interface FileRecord {
  id: number
  fullname: string
  description?: string
  list_id: number
  physical_location_id?: number
  physical_path?: string
  separation_mode_id?: number
  effectivity_date?: string
  attachments: Record<string, string>
  list: {
    id: number
    name: string
    color?: string
    requirements?: string[]
  }
  physical_location?: PhysicalLocation
  separation_mode?: SeparationMode
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
  separation_modes: SeparationMode[]
  filters: {
    search?: string
    list_id?: string
    sort?: string
    missing_requirement?: string
    separation_mode_id?: string
    location_id?: string
  }
}>()

/* =======================
    Logic & Filters
======================= */
const search = ref(props.filters.search || '')
const listId = ref(props.filters.list_id || '')
const missingRequirement = ref(props.filters.missing_requirement || '')
const separationModeId = ref(props.filters.separation_mode_id || '')
const locationId = ref(props.filters.location_id || '')
const sort = ref(props.filters.sort || 'asc')

const allPossibleRequirements = computed(() => {
  const reqs = new Set<string>()
  props.lists.forEach(list => {
    list.requirements?.forEach(r => reqs.add(r))
  })
  return Array.from(reqs).sort()
})

watchDebounced(
  [search, listId, sort, missingRequirement, separationModeId, locationId],
  () => {
    router.get('/files', {
      search: search.value || undefined,
      list_id: listId.value || undefined,
      missing_requirement: missingRequirement.value || undefined,
      separation_mode_id: separationModeId.value || undefined,
      location_id: locationId.value || undefined,
      sort: sort.value || undefined
    }, { preserveState: true, preserveScroll: true, replace: true })
  },
  { debounce: 300 }
)

const clearFilters = () => {
  search.value = ''
  listId.value = ''
  missingRequirement.value = ''
  separationModeId.value = ''
  locationId.value = ''
  sort.value = 'asc'
}

/* =======================
    Date Formatter
======================= */
const formatDate = (dateStr?: string) => {
  if (!dateStr) return null
  const date = new Date(dateStr + 'T00:00:00')
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const formatDateLong = (dateStr?: string) => {
  if (!dateStr) return null
  const date = new Date(dateStr + 'T00:00:00')
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}

/* =======================
    Extra Documents Helpers
======================= */
const getExtraAttachments = (file: FileRecord) => {
  const requirements = file.list?.requirements ?? []
  return Object.entries(file.attachments ?? {}).filter(
    ([key, val]) => !requirements.includes(key) && val !== '__NA__'
  )
}

const getEditingExtraAttachments = computed(() => {
  if (!editingFile.value) return []
  const requirements = editingFile.value.list?.requirements ?? []
  return Object.entries(editingFile.value.attachments ?? {}).filter(
    ([key]) => !requirements.includes(key) && !pendingDeletions.value.includes(key)
  )
})

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
const isBulkDeleteOpen     = ref(false)
const isBulkMoveOpen       = ref(false)
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
const isCreateDialogOpen     = ref(false)
const isEditDialogOpen       = ref(false)
const isViewDialogOpen       = ref(false)
const isDeleteDialogOpen     = ref(false)
const isOverwriteWarningOpen = ref(false)

const editingFile        = ref<FileRecord | null>(null)
const viewingFile        = ref<FileRecord | null>(null)
const deletingFileRecord = ref<FileRecord | null>(null)

const overwriteData    = ref<{ file: File, requirement: string } | null>(null)
const pendingUploads   = ref<Record<string, File>>({})
const pendingDeletions = ref<string[]>([])
const pendingNa        = ref<string[]>([])

const createForm = useForm({
  fullname: '',
  description: '',
  list_id: '' as number | '',
  physical_location_id: '' as number | '',
  physical_path: '',
  separation_mode_id: '' as number | '',
  effectivity_date: '',
})

const editForm = useForm({
  _method: 'put',
  fullname: '',
  description: '',
  list_id: '' as number | '',
  physical_location_id: '' as number | '',
  physical_path: '',
  separation_mode_id: '' as number | '',
  effectivity_date: '',
  new_attachments: {} as Record<string, File>,
  delete_attachments: [] as string[],
  na_attachments: [] as string[]
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
  viewingFile.value      = file
  isViewDialogOpen.value = true
}

const openEditDialog = (file: FileRecord) => {
  editingFile.value              = { ...file }
  editForm.fullname              = file.fullname
  editForm.description           = file.description || ''
  editForm.list_id               = file.list_id
  editForm.physical_location_id  = file.physical_location_id || ''
  editForm.physical_path         = file.physical_path || ''
  editForm.separation_mode_id    = file.separation_mode_id || ''
  editForm.effectivity_date      = file.effectivity_date || ''
  pendingUploads.value           = {}
  pendingDeletions.value         = []
  pendingNa.value = Object.entries(file.attachments ?? {})
    .filter(([, v]) => v === '__NA__')
    .map(([k]) => k)
  isEditDialogOpen.value         = true
}

const openDeleteModal = (file: FileRecord) => {
  deletingFileRecord.value  = file
  isDeleteDialogOpen.value  = true
}

const handleFileUploadLocal = (event: Event, requirement: string) => {
  const target = event.target as HTMLInputElement
  if (!target.files?.length) return
  const file = target.files[0]
  const hasExistingFile = editingFile.value?.attachments?.[requirement]
  const hasPendingFile  = pendingUploads.value[requirement]
  if (hasExistingFile || hasPendingFile) {
    overwriteData.value          = { file, requirement }
    isOverwriteWarningOpen.value = true
    target.value                 = ''
    return
  }
  processFileSelection(file, requirement)
  target.value = ''
}

const confirmOverwrite = () => {
  if (overwriteData.value) {
    processFileSelection(overwriteData.value.file, overwriteData.value.requirement)
    isOverwriteWarningOpen.value = false
    overwriteData.value          = null
  }
}

const processFileSelection = (file: File, requirement: string) => {
  const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png']
  if (!allowedTypes.includes(file.type)) {
    alert('Invalid file format. Only PDF, JPEG, and PNG are allowed.')
    return
  }
  pendingUploads.value[requirement]  = file
  pendingDeletions.value             = pendingDeletions.value.filter(req => req !== requirement)
  // If it was marked N/A, uploading a file un-marks it
  pendingNa.value = pendingNa.value.filter(r => r !== requirement)
}

const removeFileLocal = (requirement: string) => {
  pendingNa.value = pendingNa.value.filter(r => r !== requirement)
  if (pendingUploads.value[requirement]) {
    delete pendingUploads.value[requirement]
  } else {
    if (!pendingDeletions.value.includes(requirement)) {
      pendingDeletions.value.push(requirement)
    }
  }
}

/* =======================
    N/A Toggle
    - empty   → click N/A  → na
    - na      → click N/A  → empty  (reverts to "No Record")
    - deleted → click N/A  → na

    Special case: if the record was ORIGINALLY saved as __NA__ in the
    database, removing the N/A mark must also queue it for deletion so
    the backend clears the __NA__ sentinel on save.
======================= */
const toggleNa = (requirement: string) => {
  if (pendingNa.value.includes(requirement)) {
    // Was added to N/A in this session → just remove from pending
    pendingNa.value = pendingNa.value.filter(r => r !== requirement)
  } else if (editingFile.value?.attachments?.[requirement] === '__NA__') {
    // Was saved as __NA__ in the database → remove from pendingNa AND
    // queue for deletion so the backend clears it
    pendingNa.value = pendingNa.value.filter(r => r !== requirement)
    if (!pendingDeletions.value.includes(requirement)) {
      pendingDeletions.value = [...pendingDeletions.value, requirement]
    }
  } else {
    // Currently No Record or deleted → mark as N/A
    delete pendingUploads.value[requirement]
    pendingDeletions.value = pendingDeletions.value.filter(r => r !== requirement)
    pendingNa.value = [...pendingNa.value, requirement]
  }
}

const restoreFileLocal = (requirement: string) => {
  pendingDeletions.value = pendingDeletions.value.filter(req => req !== requirement)
}

const getStatus = (req: string) => {
  if (pendingNa.value.includes(req)) return 'na'
  if (pendingDeletions.value.includes(req)) {
    // If the original was __NA__, reverting shows as empty (No Record), not deleted
    if (editingFile.value?.attachments?.[req] === '__NA__') return 'empty'
    return 'deleted'
  }
  if (pendingUploads.value[req]) return 'new'
  if (editingFile.value?.attachments?.[req] === '__NA__') return 'na'
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
  editForm.na_attachments     = pendingNa.value
  editForm.post(`/files/${editingFile.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      isEditDialogOpen.value  = false
      editForm.reset()
      pendingUploads.value    = {}
      pendingDeletions.value  = []
      pendingNa.value         = []
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

const glassSelect = "w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white shadow-lg backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
const glassInput  = "w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm text-white placeholder-gray-300 shadow-lg backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
</script>

<template>
  <TooltipProvider>
    <AppLayout>

      <div
        class="relative min-h-screen bg-cover bg-center bg-fixed"
        style="background-image: url('/images/landingbg.png')"
      >
        <div class="absolute inset-0 bg-black/40"></div>

        <div class="relative z-10 flex flex-col gap-6 p-6 w-full max-w-7xl mx-auto">

          <!-- Header -->
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-white drop-shadow-md">Separated Personnel & Employees</h1>
              <p class="mt-1 text-sm text-gray-200">{{ files.total }} Records Found</p>
            </div>

            <Dialog v-model:open="isCreateDialogOpen">
              <DialogTrigger as-child>
                <button class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white shadow-lg backdrop-blur-md transition hover:bg-white/20 active:scale-95">
                  <Plus class="h-5 w-5" /> Add Record
                </button>
              </DialogTrigger>
              <DialogContent class="sm:max-w-[500px]">
                <DialogHeader><DialogTitle>Create New Record</DialogTitle></DialogHeader>
                <form @submit.prevent="createFile" class="space-y-4 pt-4">
                  <div class="space-y-2">
                    <Label>Full Name</Label>
                    <input v-model="createForm.fullname" placeholder="Enter employee name" required class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none" />
                    <InputError :message="createForm.errors.fullname" />
                  </div>
                  <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                      <Label>Employment Type</Label>
                      <select v-model="createForm.list_id" class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none" required>
                        <option value="">Select Type</option>
                        <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
                      </select>
                    </div>
                    <div class="space-y-2">
                      <Label>Location</Label>
                      <select v-model="createForm.physical_location_id" class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none">
                        <option value="">None / External</option>
                        <option v-for="loc in physical_locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                      </select>
                    </div>
                  </div>
                  <div v-if="createForm.physical_location_id" class="space-y-2 animate-in fade-in slide-in-from-top-1">
                    <Label>Storage Unit / Drawer</Label>
                    <select v-model="createForm.physical_path" class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none" required>
                      <option value="">Select Location</option>
                      <option v-for="path in availableCreatePaths" :key="path" :value="path">{{ path }}</option>
                    </select>
                  </div>
                  <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                      <Label>Mode of Separation</Label>
                      <select v-model="createForm.separation_mode_id" class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none">
                        <option value="">— None —</option>
                        <option v-for="mode in separation_modes" :key="mode.id" :value="mode.id">{{ mode.name }}</option>
                      </select>
                      <InputError :message="createForm.errors.separation_mode_id" />
                    </div>
                    <div class="space-y-2">
                      <Label>Effectivity Date</Label>
                      <input
                        type="date"
                        v-model="createForm.effectivity_date"
                        class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none"
                      />
                      <InputError :message="createForm.errors.effectivity_date" />
                    </div>
                  </div>
                  <div class="space-y-2">
                    <Label>Remarks</Label>
                    <textarea v-model="createForm.description" class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none min-h-[100px] resize-none" />
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
          <div class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[180px]">
              <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-300" />
              <input
                v-model="search"
                type="text"
                placeholder="Search by name..."
                :class="glassInput + ' pl-9 pr-4'"
              />
            </div>
            <select v-model="listId" :class="glassSelect" style="width: 170px; flex-shrink: 0;">
              <option value="" class="text-black">All Employment Types</option>
              <option v-for="list in lists" :key="list.id" :value="list.id" class="text-black">{{ list.name }}</option>
            </select>
            <select v-model="locationId" :class="glassSelect" style="width: 160px; flex-shrink: 0;">
              <option value="" class="text-black">All Locations</option>
              <option v-for="loc in physical_locations" :key="loc.id" :value="loc.id" class="text-black">{{ loc.name }}</option>
            </select>
            <select v-model="separationModeId" :class="glassSelect" style="width: 185px; flex-shrink: 0;">
              <option value="" class="text-black">All Separation Modes</option>
              <option v-for="mode in separation_modes" :key="mode.id" :value="mode.id" class="text-black">{{ mode.name }}</option>
            </select>
            <select v-model="missingRequirement" :class="glassSelect" style="width: 190px; flex-shrink: 0;">
              <option value="" class="text-black">All Documents Present</option>
              <optgroup label="Show Records Missing:">
                <option v-for="req in allPossibleRequirements" :key="req" :value="req" class="text-black">
                  No Record: {{ req }}
                </option>
              </optgroup>
            </select>
            <select v-model="sort" :class="glassSelect" style="width: 130px; flex-shrink: 0;">
              <option value="asc" class="text-black">Name (A–Z)</option>
              <option value="desc" class="text-black">Name (Z–A)</option>
            </select>
            <button
              v-if="search || listId || missingRequirement || separationModeId || locationId || sort !== 'asc'"
              @click="clearFilters"
              class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white shadow-lg backdrop-blur-md transition hover:bg-white/20"
            >
              <X class="h-4 w-4" /> Clear
            </button>
          </div>

          <!-- Missing filter warning -->
          <div
            v-if="missingRequirement"
            class="flex items-center gap-2 rounded-xl border border-yellow-400/30 bg-yellow-400/10 px-4 py-2 text-sm font-medium text-yellow-200 backdrop-blur-md animate-in fade-in slide-in-from-left-2"
          >
            <FileSearch class="h-4 w-4" />
            Personnel Without Records: <span class="underline decoration-2">{{ missingRequirement }}</span>
          </div>

          <!-- Bulk Action Toolbar -->
          <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            leave-active-class="transition-all duration-150 ease-in"
            leave-to-class="opacity-0 -translate-y-2"
          >
            <div
              v-if="selectedIds.length > 0"
              class="flex flex-wrap items-center gap-2 px-4 py-3 rounded-2xl border border-white/20 bg-white/10 shadow-lg backdrop-blur-md"
            >
              <div class="flex items-center gap-2 mr-2">
                <SquareCheck class="h-4 w-4 text-blue-300" />
                <span class="text-sm font-semibold text-white">{{ selectedIds.length }} selected</span>
              </div>
              <button @click="isBulkChangeTypeOpen = true" class="inline-flex items-center gap-1.5 rounded-lg border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-white/20">
                <Layers class="h-3.5 w-3.5" /> Change Type
              </button>
              <button @click="isBulkMoveOpen = true" class="inline-flex items-center gap-1.5 rounded-lg border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-white/20">
                <MoveRight class="h-3.5 w-3.5" /> Move Location
              </button>
              <button @click="bulkExport('excel')" class="inline-flex items-center gap-1.5 rounded-lg border border-green-400/30 bg-green-400/10 px-3 py-1.5 text-xs font-medium text-green-300 transition hover:bg-green-400/20">
                <FileSpreadsheet class="h-3.5 w-3.5" /> Export Excel
              </button>
              <button @click="bulkExport('pdf')" class="inline-flex items-center gap-1.5 rounded-lg border border-blue-400/30 bg-blue-400/10 px-3 py-1.5 text-xs font-medium text-blue-300 transition hover:bg-blue-400/20">
                <FileText class="h-3.5 w-3.5" /> Export PDF
              </button>
              <button @click="isBulkDeleteOpen = true" class="inline-flex items-center gap-1.5 rounded-lg border border-red-400/30 bg-red-400/10 px-3 py-1.5 text-xs font-medium text-red-300 transition hover:bg-red-400/20">
                <Trash2 class="h-3.5 w-3.5" /> Delete
              </button>
              <button @click="clearSelection" class="ml-auto inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-transparent px-3 py-1.5 text-xs font-medium text-gray-300 transition hover:bg-white/10">
                <X class="h-3.5 w-3.5" /> Clear
              </button>
            </div>
          </Transition>

          <!-- Table -->
          <div class="overflow-hidden rounded-2xl border border-white/20 bg-white/10 shadow-2xl backdrop-blur-xl">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-white/10 bg-white/10">
                  <th class="p-4 pl-6 w-10">
                    <input
                      type="checkbox"
                      :checked="allSelected"
                      :indeterminate="someSelected"
                      @change="toggleSelectAll"
                      class="rounded border-gray-300 cursor-pointer"
                    />
                  </th>
                  <th class="px-4 py-3 text-left font-medium text-gray-200 w-12">No.</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-200">Full Name</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-200">Employment Type</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-200">Location</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-200 hidden xl:table-cell">Mode of Separation</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-200 hidden xl:table-cell">Effectivity Date</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-200 hidden lg:table-cell">Remarks</th>
                  <th class="px-4 py-3 text-center font-medium text-gray-200 w-40">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/10">
                <tr
                  v-for="(file, index) in files.data"
                  :key="file.id"
                  class="transition hover:bg-white/10"
                  :class="{ 'bg-blue-500/10': selectedIds.includes(file.id) }"
                >
                  <td class="p-4 pl-6">
                    <input
                      type="checkbox"
                      :checked="selectedIds.includes(file.id)"
                      @change="toggleSelect(file.id)"
                      class="rounded border-gray-300 cursor-pointer"
                    />
                  </td>
                  <td class="px-4 py-3 font-mono text-xs text-gray-300">
                    {{ (props.files.current_page - 1) * props.files.per_page + index + 1 }}
                  </td>
                  <td class="px-4 py-3 font-medium text-white">{{ file.fullname }}</td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: file.list?.color || '#94a3b8' }"></div>
                      <span class="inline-block rounded-md bg-white/10 px-2 py-0.5 text-xs font-medium text-white">
                        {{ file.list?.name }}
                      </span>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <div v-if="file.physical_location" class="flex flex-col">
                      <div class="flex items-center gap-1.5 text-xs font-bold text-white">
                        <MapPin class="h-3 w-3 shrink-0" :style="{ color: file.physical_location.color }" />
                        {{ file.physical_location.name }}
                      </div>
                      <div class="text-[10px] text-gray-300 pl-4 flex items-center gap-1">
                        <Archive class="h-2.5 w-2.5" /> {{ file.physical_path || 'Main Room' }}
                      </div>
                    </div>
                    <span v-else class="text-xs text-gray-400 italic">No mapping</span>
                  </td>
                  <td class="px-4 py-3 hidden xl:table-cell">
                    <div v-if="file.separation_mode" class="flex items-center gap-1.5">
                      <Scissors class="h-3 w-3 text-orange-300 shrink-0" />
                      <span class="inline-block rounded-md bg-orange-400/10 border border-orange-400/20 px-2 py-0.5 text-xs font-medium text-orange-200">
                        {{ file.separation_mode.name }}
                      </span>
                    </div>
                    <span v-else class="text-xs text-gray-400 italic">—</span>
                  </td>
                  <td class="px-4 py-3 hidden xl:table-cell">
                    <div v-if="file.effectivity_date" class="flex items-center gap-1.5">
                      <CalendarDays class="h-3 w-3 text-sky-300 shrink-0" />
                      <span class="text-xs text-sky-200 font-mono">
                        {{ formatDate(file.effectivity_date) }}
                      </span>
                    </div>
                    <span v-else class="text-xs text-gray-400 italic">—</span>
                  </td>
                  <td class="px-4 py-3 text-gray-300 hidden lg:table-cell italic text-xs">
                    {{ file.description || 'N/A' }}
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex justify-center gap-1">
                      <Tooltip>
                        <TooltipTrigger as-child>
                          <button @click="openViewDialog(file)" class="rounded-lg p-1.5 text-blue-300 transition hover:bg-white/10 hover:text-blue-200">
                            <Eye class="h-4 w-4" />
                          </button>
                        </TooltipTrigger>
                        <TooltipContent>View Folder Details</TooltipContent>
                      </Tooltip>
                      <Tooltip>
                        <TooltipTrigger as-child>
                          <button @click="openEditDialog(file)" class="rounded-lg p-1.5 text-gray-300 transition hover:bg-white/10 hover:text-white">
                            <Edit2 class="h-4 w-4" />
                          </button>
                        </TooltipTrigger>
                        <TooltipContent>Edit Record & Files</TooltipContent>
                      </Tooltip>
                      <Tooltip>
                        <TooltipTrigger as-child>
                          <button @click="openDeleteModal(file)" class="rounded-lg p-1.5 text-red-300 transition hover:bg-red-400/10 hover:text-red-200">
                            <Trash2 class="h-4 w-4" />
                          </button>
                        </TooltipTrigger>
                        <TooltipContent>Delete Folder</TooltipContent>
                      </Tooltip>
                    </div>
                  </td>
                </tr>
                <tr v-if="files.data.length === 0">
                  <td colspan="9" class="px-4 py-10 text-center text-gray-300">
                    No records found matching these filters.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div
            v-if="files.links && files.links.length > 3"
            class="flex items-center justify-between text-sm text-gray-200"
          >
            <span>Page {{ files.current_page }}</span>
            <div class="flex gap-1">
              <Link
                v-for="link in files.links"
                :key="link.label"
                :href="link.url ?? '#'"
                :class="[
                  'rounded-lg px-3 py-1.5 transition',
                  link.active
                    ? 'bg-blue-600 text-white'
                    : 'bg-white/10 text-white hover:bg-white/20',
                  !link.url ? 'pointer-events-none opacity-40' : '',
                ]"
                v-html="link.label"
                preserve-scroll
              />
            </div>
          </div>

        </div>
      </div>

      <!-- ══════════════════════════════════════════════
           DIALOGS
      ══════════════════════════════════════════════ -->

      <!-- BULK DELETE -->
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

      <!-- BULK MOVE -->
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
              <select v-model="bulkMoveForm.physical_location_id" class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none">
                <option value="">None / External</option>
                <option v-for="loc in physical_locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
              </select>
            </div>
            <div v-if="bulkMoveForm.physical_location_id" class="space-y-2">
              <Label>Storage Path</Label>
              <select v-model="bulkMoveForm.physical_path" class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none">
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
              <select v-model="bulkChangeTypeForm.list_id" class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none" required>
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
              <div class="space-y-2">
                <Label>Full Name</Label>
                <input v-model="editForm.fullname" required class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none" />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>Employment Type</Label>
                  <select v-model="editForm.list_id" class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none" required>
                    <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
                  </select>
                </div>
                <div class="space-y-2">
                  <Label>Location</Label>
                  <select v-model="editForm.physical_location_id" class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none">
                    <option value="">None / External</option>
                    <option v-for="loc in physical_locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                  </select>
                </div>
              </div>
              <div v-if="editForm.physical_location_id" class="space-y-2">
                <Label>Specific Storage Location</Label>
                <select v-model="editForm.physical_path" class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none" required>
                  <option value="">Select Location</option>
                  <option v-for="path in availableEditPaths" :key="path" :value="path">{{ path }}</option>
                </select>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>Mode of Separation</Label>
                  <select v-model="editForm.separation_mode_id" class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none">
                    <option value="">— None —</option>
                    <option v-for="mode in separation_modes" :key="mode.id" :value="mode.id">{{ mode.name }}</option>
                  </select>
                </div>
                <div class="space-y-2">
                  <Label>Effectivity Date</Label>
                  <input
                    type="date"
                    v-model="editForm.effectivity_date"
                    class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none"
                  />
                </div>
              </div>
              <div class="space-y-2">
                <Label>Remarks</Label>
                <textarea v-model="editForm.description" class="w-full border rounded-md px-3 py-2 text-sm bg-background focus:ring-2 focus:ring-ring outline-none min-h-[80px] resize-none" />
              </div>
            </form>

            <!-- Documents Checklist -->
            <div v-if="editingFile" class="pt-6 border-t space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold flex items-center gap-2 text-primary">
                  <FileText class="h-4 w-4" /> Documents Checklist
                </h3>
                <span class="text-[10px] text-muted-foreground uppercase font-bold">Changes will apply on save</span>
              </div>
              <div v-if="editingFile.list?.requirements?.length" class="grid gap-2">
                <div
                  v-for="req in editingFile.list.requirements"
                  :key="req"
                  class="flex items-center justify-between p-3 rounded-lg border bg-card transition-all hover:border-muted-foreground/30"
                  :class="[
                    getStatus(req) === 'deleted' ? 'opacity-60 border-destructive bg-destructive/5' : '',
                    getStatus(req) === 'exists'  ? 'bg-slate-50/50' : '',
                    getStatus(req) === 'na'      ? 'border-gray-300 bg-gray-50/50 dark:bg-gray-800/30 dark:border-gray-600' : ''
                  ]"
                >
                  <div class="flex items-center gap-3 overflow-hidden">
                    <CheckCircle2 v-if="getStatus(req) === 'exists'"      class="h-5 w-5 text-green-500 shrink-0" />
                    <FileUp       v-else-if="getStatus(req) === 'new'"     class="h-5 w-5 text-blue-500 shrink-0 animate-pulse" />
                    <Trash2       v-else-if="getStatus(req) === 'deleted'" class="h-5 w-5 text-destructive shrink-0" />
                    <MinusCircle  v-else-if="getStatus(req) === 'na'"      class="h-5 w-5 text-gray-400 shrink-0" />
                    <XCircle      v-else                                    class="h-5 w-5 text-destructive/30 shrink-0" />
                    <div class="flex flex-col overflow-hidden">
                      <span class="text-sm font-medium truncate">{{ req }}</span>
                      <span v-if="getStatus(req) === 'new'"     class="text-[10px] text-blue-600 font-bold uppercase">Pending Upload</span>
                      <span v-if="getStatus(req) === 'deleted'" class="text-[10px] text-destructive font-bold uppercase">Marked for removal</span>
                      <span v-if="getStatus(req) === 'na'"      class="text-[10px] text-gray-400 font-bold uppercase">Not Applicable</span>
                      <span v-if="getStatus(req) === 'empty'"   class="text-[10px] text-destructive/60 font-bold uppercase">No Record</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-1">
                    <!-- View existing file -->
                    <Tooltip v-if="getStatus(req) === 'exists'">
                      <TooltipTrigger as-child>
                        <Button variant="ghost" size="icon" as-child class="h-8 w-8 text-blue-600">
                          <a :href="`/storage/${editingFile.attachments[req]}`" target="_blank"><Eye class="h-4 w-4" /></a>
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>Open Document</TooltipContent>
                    </Tooltip>

                    <!-- Remove existing or pending file -->
                    <Tooltip v-if="getStatus(req) === 'exists' || getStatus(req) === 'new'">
                      <TooltipTrigger as-child>
                        <Button variant="ghost" size="icon" @click="removeFileLocal(req)" class="h-8 w-8 text-destructive hover:bg-destructive/10">
                          <Trash2 class="h-4 w-4" />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>Remove attachment</TooltipContent>
                    </Tooltip>

                    <!-- Restore deleted file -->
                    <Tooltip v-if="getStatus(req) === 'deleted'">
                      <TooltipTrigger as-child>
                        <Button variant="ghost" size="icon" @click="restoreFileLocal(req)" class="h-8 w-8 text-primary">
                          <RotateCcw class="h-4 w-4" />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>Restore original file</TooltipContent>
                    </Tooltip>

                    <!-- N/A toggle — visible for empty, na, deleted states -->
                    <Tooltip v-if="getStatus(req) === 'empty' || getStatus(req) === 'na' || getStatus(req) === 'deleted'">
                      <TooltipTrigger as-child>
                        <Button
                          variant="ghost"
                          size="icon"
                          @click="toggleNa(req)"
                          :class="[
                            'h-8 w-8 transition-all',
                            getStatus(req) === 'na'
                              ? 'text-gray-500 bg-gray-100 dark:bg-gray-700 ring-1 ring-gray-400'
                              : 'text-muted-foreground hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700'
                          ]"
                        >
                          <MinusCircle class="h-4 w-4" />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>
                        {{ getStatus(req) === 'na' ? 'Remove N/A — revert to No Record' : 'Mark as N/A' }}
                      </TooltipContent>
                    </Tooltip>

                    <!-- Upload / Replace file -->
                    <div class="relative" v-if="getStatus(req) !== 'deleted' && getStatus(req) !== 'na'">
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

              <template v-if="getEditingExtraAttachments.length > 0">
                <div class="pt-2 space-y-3">
                  <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold flex items-center gap-2 text-muted-foreground">
                      <FileText class="h-4 w-4" /> Extra Documents
                    </h3>
                    <span class="text-[10px] text-muted-foreground bg-muted px-2 py-0.5 rounded">Not required by current type</span>
                  </div>
                  <div class="grid gap-2">
                    <div v-for="[key, path] in getEditingExtraAttachments" :key="key"
                      class="flex items-center justify-between p-3 rounded-lg border border-dashed bg-muted/10 hover:bg-muted/20 transition-colors"
                    >
                      <div class="flex items-center gap-3 overflow-hidden">
                        <FileText class="h-5 w-5 text-muted-foreground shrink-0" />
                        <div class="flex flex-col overflow-hidden">
                          <span class="text-sm font-medium truncate">{{ key }}</span>
                          <span class="text-[10px] text-muted-foreground">From previous employment type</span>
                        </div>
                      </div>
                      <div class="flex items-center gap-1">
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" as-child class="h-8 w-8 text-blue-600">
                              <a :href="`/storage/${path}`" target="_blank"><Eye class="h-4 w-4" /></a>
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>View Document</TooltipContent>
                        </Tooltip>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" @click="removeFileLocal(key)" class="h-8 w-8 text-destructive hover:bg-destructive/10">
                              <Trash2 class="h-4 w-4" />
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>Remove Document</TooltipContent>
                        </Tooltip>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
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
            <div class="grid grid-cols-2 gap-4">
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
                <Label class="text-xs text-muted-foreground uppercase tracking-wider">Mode of Separation</Label>
                <div v-if="viewingFile.separation_mode" class="flex items-center gap-2 p-3 rounded-md border bg-orange-50/50">
                  <Scissors class="h-4 w-4 text-orange-500" />
                  <p class="font-medium text-slate-800">{{ viewingFile.separation_mode.name }}</p>
                </div>
                <p v-else class="text-sm italic text-muted-foreground">Not specified.</p>
              </div>
            </div>
            <div class="space-y-1">
              <Label class="text-xs text-muted-foreground uppercase tracking-wider">Effectivity Date</Label>
              <div v-if="viewingFile.effectivity_date" class="flex items-center gap-2 p-3 rounded-md border bg-sky-50/50">
                <CalendarDays class="h-4 w-4 text-sky-500" />
                <p class="font-medium text-slate-800">{{ formatDateLong(viewingFile.effectivity_date) }}</p>
              </div>
              <p v-else class="text-sm italic text-muted-foreground">Not specified.</p>
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
                  class="flex items-center justify-between p-3 border rounded-lg bg-background"
                  :class="viewingFile.attachments?.[req] === '__NA__' ? 'border-gray-200 bg-gray-50/50 dark:bg-gray-800/30' : ''"
                >
                  <div class="flex items-center gap-3">
                    <CheckCircle2 v-if="viewingFile.attachments?.[req] && viewingFile.attachments[req] !== '__NA__'" class="h-5 w-5 text-green-500" />
                    <MinusCircle  v-else-if="viewingFile.attachments?.[req] === '__NA__'"                           class="h-5 w-5 text-gray-400" />
                    <XCircle      v-else                                                                            class="h-5 w-5 text-destructive/40" />
                    <span class="text-sm font-medium">{{ req }}</span>
                  </div>
                  <div v-if="viewingFile.attachments?.[req] && viewingFile.attachments[req] !== '__NA__'">
                    <Button variant="outline" size="sm" as-child class="h-8 gap-2">
                      <a :href="`/storage/${viewingFile.attachments[req]}`" target="_blank">
                        <Eye class="h-3.5 w-3.5" /> View
                      </a>
                    </Button>
                  </div>
                  <span v-else-if="viewingFile.attachments?.[req] === '__NA__'" class="text-xs font-bold text-gray-400 uppercase">N/A</span>
                  <span v-else class="text-xs font-bold text-destructive uppercase">No Record</span>
                </div>
                <div v-if="!viewingFile.list?.requirements?.length" class="p-4 text-center text-xs text-muted-foreground italic border border-dashed rounded-lg">
                  No requirements defined for this employment type.
                </div>
              </div>
            </div>
            <template v-if="getExtraAttachments(viewingFile).length > 0">
              <div class="space-y-3">
                <div class="flex items-center gap-2">
                  <Label class="text-xs text-muted-foreground uppercase tracking-wider">Extra Documents</Label>
                  <span class="text-[10px] text-muted-foreground bg-muted px-2 py-0.5 rounded">Not required by current type</span>
                </div>
                <div class="grid gap-2">
                  <div v-for="[key, path] in getExtraAttachments(viewingFile)" :key="key"
                    class="flex items-center justify-between p-3 border border-dashed rounded-lg bg-muted/10"
                  >
                    <div class="flex items-center gap-3">
                      <FileText class="h-5 w-5 text-muted-foreground" />
                      <div>
                        <span class="text-sm font-medium">{{ key }}</span>
                        <p class="text-[10px] text-muted-foreground">From previous employment type</p>
                      </div>
                    </div>
                    <Button variant="outline" size="sm" as-child class="h-8 gap-2">
                      <a :href="`/storage/${path}`" target="_blank">
                        <Eye class="h-3.5 w-3.5" /> View
                      </a>
                    </Button>
                  </div>
                </div>
              </div>
            </template>
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

    </AppLayout>
  </TooltipProvider>
</template>
