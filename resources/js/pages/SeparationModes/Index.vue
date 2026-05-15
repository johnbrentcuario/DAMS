<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2,} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
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

// ── Dialog state ──────────────────────────────────────────────────────────────
const showFormDialog = ref(false);
const showDeleteDialog = ref(false);
const editingMode = ref<SeparationMode | null>(null);
const deletingMode = ref<SeparationMode | null>(null);

// ── Form ──────────────────────────────────────────────────────────────────────
const form = useForm({
    name: '',
    description: '',
});

function openCreate() {
    editingMode.value = null;
    form.reset();
    showFormDialog.value = true;
}

function openEdit(mode: SeparationMode) {
    editingMode.value = mode;
    form.name = mode.name;
    form.description = mode.description ?? '';
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
            },
        });
    }
}

// ── Delete ────────────────────────────────────────────────────────────────────
function confirmDelete(mode: SeparationMode) {
    deletingMode.value = mode;
    showDeleteDialog.value = true;
}

function deleteMode() {
    if (!deletingMode.value) return;
    router.delete(`/separation-modes/${deletingMode.value.id}`, {
        onSuccess: () => { showDeleteDialog.value = false; },
    });
}

// ── Pagination ────────────────────────────────────────────────────────────────
const breadcrumbs = [
    { title: 'Mode of Separation', href: '/separation-modes' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">Mode of Separation</h1>
                    <p class="text-muted-foreground text-sm mt-1">
                        Manage the types of separation (e.g. Resigned, Terminated, Retired).
                    </p>
                </div>
                <Button @click="openCreate" class="gap-2">
                    <Plus class="size-4" />
                    Add Mode
                </Button>
            </div>

            <!-- Table -->
            <div class="rounded-lg border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead>Created</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="mode in separationModes.data"
                            :key="mode.id"
                        >
                            <TableCell class="font-medium">{{ mode.name }}</TableCell>
                            <TableCell class="text-muted-foreground max-w-xs truncate">
                                {{ mode.description || '—' }}
                            </TableCell>
                            <TableCell class="text-muted-foreground text-sm">
                                {{ new Date(mode.created_at).toLocaleDateString() }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openEdit(mode)"
                                    >
                                        <Pencil class="size-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        @click="confirmDelete(mode)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="separationModes.data.length === 0">
                            <TableCell colspan="5" class="text-center text-muted-foreground py-10">
                                No modes of separation found. Add one to get started.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination info -->
            <p v-if="separationModes.total > 0" class="text-sm text-muted-foreground text-right">
                Showing {{ separationModes.data.length }} of {{ separationModes.total }} entries
            </p>
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
                        <Label for="name">Name <span class="text-destructive">*</span></Label>
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
                </div>

                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="showFormDialog = false">Cancel</Button>
                    <Button @click="submitForm" :disabled="form.processing">
                        {{ editingMode ? 'Save Changes' : 'Create' }}
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
