<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Scissors, FolderOpen } from 'lucide-vue-next';
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

            <div class="relative z-10 flex flex-col gap-6 p-6">

                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white drop-shadow-md">Mode of Separation</h1>
                        <p class="mt-1 text-sm text-gray-200">
                            {{ separationModes.total }} mode{{ separationModes.total !== 1 ? 's' : '' }} ·
                            Manage types of separation (e.g. Resigned, Terminated, Retired)
                        </p>
                    </div>
                    <button
                        @click="openCreate"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white shadow-lg backdrop-blur-md transition hover:bg-white/20 active:scale-95"
                    >
                        <Plus class="h-4 w-4" /> Add Mode
                    </button>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-2xl border border-white/20 bg-white/10 shadow-2xl backdrop-blur-xl">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/10 bg-white/10">
                                <th class="px-4 py-3 text-left font-medium text-gray-200">Mode</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-200">Description</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-200">201 Inactive Files</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-200 hidden sm:table-cell">Created</th>
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
                                <td class="px-4 py-3 text-xs text-gray-300 hidden sm:table-cell">
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

                <!-- Pagination info -->
                <p v-if="separationModes.total > 0" class="text-sm text-gray-300 text-right">
                    Showing {{ separationModes.data.length }} of {{ separationModes.total }} entries
                </p>

            </div>
        </div>

        <!-- Create / Edit Dialog -->
        <Dialog v-model:open="showFormDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingMode ? 'Edit Mode of Separation' : 'Add Mode of Separation' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ editingMode ? 'Update the details below.' : 'Fill in the details for the new mode.' }}
                    </DialogDescription>
                </DialogHeader>

                <div class="flex flex-col gap-4 py-2">
                    <!-- Name -->
                    <div class="flex flex-col gap-1.5">
                        <Label for="name">Mode<span class="text-destructive">*</span></Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="e.g. Resigned, Terminated, Retired"
                            :class="{ 'border-destructive': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="text-destructive text-xs">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Description -->
                    <div class="flex flex-col gap-1.5">
                        <Label for="description">Description</Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Optional description..."
                            rows="3"
                        />
                        <p v-if="form.errors.description" class="text-destructive text-xs">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Color -->
                    <div class="flex flex-col gap-2">
                        <!-- Custom colour input -->
                        <div class="flex items-center gap-3 mt-1">
                            <input
                                type="color"
                                v-model="form.color"
                                class="h-8 w-10 cursor-pointer rounded border border-input bg-transparent p-0.5"
                            />
                            <span class="text-xs text-muted-foreground font-mono">{{ form.color }}</span>
                            <!-- Preview badge -->
                            <span
                                class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold border"
                                :style="{ backgroundColor: form.color + '20', borderColor: form.color + '40', color: form.color }"
                            >
                                <Scissors class="h-3 w-3" />
                                Preview
                            </span>
                        </div>
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="showFormDialog = false">Cancel</Button>
                    <Button @click="submitForm" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : (editingMode ? 'Save Changes' : 'Create') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete Mode of Separation</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to delete
                        <span class="font-semibold">{{ deletingMode?.name }}</span>?
                        <template v-if="deletingMode && deletingMode.files_count > 0">
                            <br /><br />
                            <span class="text-destructive font-medium">
                                Warning: {{ deletingMode.files_count }} Record{{ deletingMode.files_count !== 1 ? 's are' : ' is' }} currently using this mode.
                            </span>
                        </template>
                        This action cannot be undone.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                        @click="deleteMode"
                    >
                        Delete
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </AppLayout>
</template>
