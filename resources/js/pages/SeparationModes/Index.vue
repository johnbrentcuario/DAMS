<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Scissors, FolderOpen, Calendar } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    DialogDescription,
} from '@/components/ui/dialog';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Label } from '@/components/ui/label';

interface SeparationMode {
    id: number;
    name: string;
    description: string | null;
    color: string | null;
    files_count: number;
    created_at: string;
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    separationModes: Paginated<SeparationMode>;
}>();

// ── Preset colours ────────────────────────────────────────────────────────────
const presetColors = [
    '#f97316', // orange
    '#ef4444', // red
    '#eab308', // yellow
    '#22c55e', // green
    '#3b82f6', // blue
    '#8b5cf6', // violet
    '#ec4899', // pink
    '#14b8a6', // teal
    '#64748b', // slate
    '#f59e0b', // amber
]

// ── Dialog state ──────────────────────────────────────────────────────────────
const showFormDialog   = ref(false);
const showDeleteDialog = ref(false);
const editingMode      = ref<SeparationMode | null>(null);
const deletingMode     = ref<SeparationMode | null>(null);

// ── Form ──────────────────────────────────────────────────────────────────────
const form = useForm({
    name: '',
    description: '',
    color: '#f97316',
});

function openCreate() {
    editingMode.value = null;
    form.reset();
    form.color = '#f97316';
    showFormDialog.value = true;
}

function openEdit(mode: SeparationMode) {
    editingMode.value    = mode;
    form.name            = mode.name;
    form.description     = mode.description ?? '';
    form.color           = mode.color ?? '#f97316';
    showFormDialog.value = true;
}

function submitForm() {
    if (editingMode.value) {
        form.put(`/separation-modes/${editingMode.value.id}`, {
            onSuccess: () => { showFormDialog.value = false; },
        });
    } else {
        form.post('/separation-modes', {
            onSuccess: () => {
                showFormDialog.value = false;
                form.reset();
                form.color = '#f97316';
            },
        });
    }
}

// ── Delete ────────────────────────────────────────────────────────────────────
function confirmDelete(mode: SeparationMode) {
    deletingMode.value     = mode;
    showDeleteDialog.value = true;
}

function deleteMode() {
    if (!deletingMode.value) return;
    router.delete(`/separation-modes/${deletingMode.value.id}`, {
        onSuccess: () => { showDeleteDialog.value = false; },
    });
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
    });
}
</script>

<template>
    <AppLayout>

        <div
            class="relative min-h-screen bg-cover bg-center bg-fixed"
            style="background-image: url('/images/landingbg.png')"
        >
            <div class="absolute inset-0 bg-black/40"></div>

            <div class="relative z-10 flex flex-col gap-6 p-4 sm:p-6">

                <!-- Header -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-white drop-shadow-md">Mode of Separation</h1>
                        <p class="mt-1 text-xs sm:text-sm text-gray-200 max-w-xl">
                            {{ separationModes.total }} mode{{ separationModes.total !== 1 ? 's' : '' }} ·
                            Manage types of separation (e.g. Resigned, Terminated, Retired)
                        </p>
                    </div>
                    <button
                        @click="openCreate"
                        class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-medium text-white shadow-lg backdrop-blur-md transition hover:bg-white/20 active:scale-95"
                    >
                        <Plus class="h-4 w-4" /> Add Mode
                    </button>
                </div>

                <!-- Desktop Table View (Hidden on Mobile/Tablet) -->
                <div class="hidden md:block overflow-hidden rounded-2xl border border-white/20 bg-white/10 shadow-2xl backdrop-blur-xl">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/10 bg-white/10">
                                <th class="px-4 py-3 text-left font-medium text-gray-200">Mode</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-200">Description</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-200">201 Inactive Files</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-200">Created</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-200">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr
                                v-for="mode in separationModes.data"
                                :key="mode.id"
                                class="transition hover:bg-white/10"
                            >
                                <td class="px-4 py-3 font-medium text-white">
                                    <div class="flex items-center gap-2">
                                        <Scissors class="h-3.5 w-3.5 shrink-0" :style="{ color: mode.color ?? '#f97316' }" />
                                        {{ mode.name }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-300 max-w-xs truncate italic">
                                    {{ mode.description || '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <FolderOpen class="h-3.5 w-3.5 text-gray-400 shrink-0" />
                                        <span
                                            class="inline-block rounded-md px-2 py-0.5 text-xs font-semibold border"
                                            :style="mode.files_count > 0
                                                ? { backgroundColor: (mode.color ?? '#f97316') + '20', borderColor: (mode.color ?? '#f97316') + '40', color: mode.color ?? '#f97316' }
                                                : {}"
                                            :class="mode.files_count === 0 ? 'bg-white/5 border-white/10 text-gray-400' : ''"
                                        >
                                            {{ mode.files_count }} {{ mode.files_count === 1 ? 'Record' : 'Records' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-300">
                                    {{ formatDate(mode.created_at) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-1">
                                        <button
                                            @click="openEdit(mode)"
                                            class="rounded-lg p-1.5 text-gray-300 transition hover:bg-white/10 hover:text-white"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="confirmDelete(mode)"
                                            class="rounded-lg p-1.5 text-red-300 transition hover:bg-red-400/10 hover:text-red-200"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="separationModes.data.length === 0">
                                <td colspan="5" class="px-4 py-10 text-center text-gray-300 italic">
                                    No modes of separation found. Add one to get started.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Responsive Card View (Hidden on Desktop) -->
                <div class="grid grid-cols-1 gap-4 md:hidden">
                    <div
                        v-for="mode in separationModes.data"
                        :key="mode.id"
                        class="flex flex-col justify-between rounded-2xl border border-white/20 bg-white/10 p-4 shadow-lg backdrop-blur-xl border-l-4"
                        :style="{ borderLeftColor: mode.color ?? '#f97316' }"
                    >
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2 font-semibold text-white">
                                    <Scissors class="h-4 w-4 shrink-0" :style="{ color: mode.color ?? '#f97316' }" />
                                    {{ mode.name }}
                                </div>
                                <div class="flex gap-1 shrink-0">
                                    <button
                                        @click="openEdit(mode)"
                                        class="rounded-lg p-2 text-gray-300 bg-white/5 active:bg-white/15 transition"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        @click="confirmDelete(mode)"
                                        class="rounded-lg p-2 text-red-300 bg-red-400/10 active:bg-red-400/20 transition"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-300 italic line-clamp-2">
                                {{ mode.description || 'No description provided.' }}
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-1.5 text-xs">
                                <FolderOpen class="h-3.5 w-3.5 text-gray-400 shrink-0" />
                                <span
                                    class="inline-block rounded-md px-2 py-0.5 text-xs font-semibold border"
                                    :style="mode.files_count > 0
                                        ? { backgroundColor: (mode.color ?? '#f97316') + '20', borderColor: (mode.color ?? '#f97316') + '40', color: mode.color ?? '#f97316' }
                                        : {}"
                                    :class="mode.files_count === 0 ? 'bg-white/5 border-white/10 text-gray-400' : ''"
                                >
                                    {{ mode.files_count }} {{ mode.files_count === 1 ? 'Record' : 'Records' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1 text-[11px] text-gray-400">
                                <Calendar class="h-3 w-3" />
                                {{ formatDate(mode.created_at) }}
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="separationModes.data.length === 0"
                        class="rounded-2xl border border-white/20 bg-white/10 p-8 text-center text-gray-300 backdrop-blur-xl italic text-sm"
                    >
                        No modes of separation found. Add one to get started.
                    </div>
                </div>

                <!-- Pagination info -->
                <p v-if="separationModes.total > 0" class="text-xs sm:text-sm text-gray-300 text-center sm:text-right">
                    Showing {{ separationModes.data.length }} of {{ separationModes.total }} entries
                </p>

            </div>
        </div>

        <!-- Create / Edit Dialog -->
        <Dialog v-model:open="showFormDialog">
            <DialogContent class="w-[95vw] sm:max-w-md rounded-xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle class="text-base sm:text-lg">
                        {{ editingMode ? 'Edit Mode of Separation' : 'Add Mode of Separation' }}
                    </DialogTitle>
                    <DialogDescription class="text-xs sm:text-sm">
                        {{ editingMode ? 'Update the details below.' : 'Fill in the details for the new mode.' }}
                    </DialogDescription>
                </DialogHeader>

                <div class="flex flex-col gap-4 py-2">
                    <!-- Name -->
                    <div class="flex flex-col gap-1.5">
                        <Label for="name" class="text-xs sm:text-sm">Mode<span class="text-destructive">*</span></Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="e.g. Resigned, Terminated, Retired"
                            :class="{ 'border-destructive': form.errors.name }"
                            class="text-sm"
                        />
                        <p v-if="form.errors.name" class="text-destructive text-xs">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div class="flex flex-col gap-1.5">
                        <Label for="description" class="text-xs sm:text-sm">Description</Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Optional description..."
                            rows="3"
                            class="text-sm"
                        />
                        <p v-if="form.errors.description" class="text-destructive text-xs">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Color -->
                    <div class="flex flex-col gap-2">
                        <Label class="text-xs sm:text-sm">Label Theme Color</Label>
                        <div class="flex flex-wrap items-center gap-3 mt-0.5">
                            <input
                                type="color"
                                v-model="form.color"
                                class="h-8 w-12 cursor-pointer rounded border border-input bg-transparent p-0.5 shrink-0"
                            />
                            <span class="text-xs text-muted-foreground font-mono bg-muted px-2 py-1 rounded">{{ form.color }}</span>

                            <!-- Preview badge -->
                            <span
                                class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold border ml-auto sm:ml-0"
                                :style="{ backgroundColor: form.color + '20', borderColor: form.color + '40', color: form.color }"
                            >
                                <Scissors class="h-3 w-3" />
                                Preview
                            </span>
                        </div>
                    </div>
                </div>

                <DialogFooter class="flex flex-col-reverse gap-2 sm:flex-row">
                    <Button variant="outline" @click="showFormDialog = false" class="w-full sm:w-auto">Cancel</Button>
                    <Button @click="submitForm" :disabled="form.processing" class="w-full sm:w-auto">
                        {{ form.processing ? 'Saving...' : (editingMode ? 'Save Changes' : 'Create') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent class="w-[95vw] sm:max-w-md rounded-xl">
                <AlertDialogHeader>
                    <AlertDialogTitle class="text-base sm:text-lg">Delete Mode of Separation</AlertDialogTitle>
                    <AlertDialogDescription class="text-xs sm:text-sm text-left">
                        Are you sure you want to delete
                        <span class="font-semibold text-foreground">"{{ deletingMode?.name }}"</span>?
                        <template v-if="deletingMode && deletingMode.files_count > 0">
                            <br /><br />
                            <span class="text-destructive font-medium block border border-destructive/20 bg-destructive/10 p-2.5 rounded-lg text-xs">
                                Warning: {{ deletingMode.files_count }} Record{{ deletingMode.files_count !== 1 ? 's are' : ' is' }} currently using this mode.
                            </span>
                        </template>
                        <span class="block mt-2">This action cannot be undone.</span>
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter class="flex flex-col gap-2 sm:flex-row">
                    <AlertDialogCancel class="mt-0 w-full sm:w-auto">Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-destructive text-destructive-foreground hover:bg-destructive/90 w-full sm:w-auto"
                        @click="deleteMode"
                    >
                        Delete
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </AppLayout>
</template>
