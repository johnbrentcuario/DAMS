<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Trash2, Plus, X, Search, Eye, EyeOff } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
    id_number: string;
    role: 'admin' | 'user';
    created_at: string;
}

interface PaginatedUsers {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{ users: PaginatedUsers }>();
const showModal         = ref(false);
const editingUser       = ref<User | null>(null);
const showDeleteConfirm = ref(false);
const deletingUser      = ref<User | null>(null);
const searchQuery       = ref('');

const filteredUsers = computed(() =>
    props.users.data.filter(u =>
        u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        u.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        u.id_number.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
);

const form = useForm({
    name: '',
    email: '',
    id_number: '',
    role: 'user' as 'admin' | 'user',
    password: '',
    password_confirmation: '',
});

const showPassword            = ref(false);
const showPasswordConfirm     = ref(false);

function openCreate() {
    editingUser.value = null;
    form.reset();
    form.role = 'user';
    form.clearErrors();
    showModal.value = true;
}

function openEdit(user: User) {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.id_number = user.id_number;
    form.role = user.role;
    form.password = '';
    form.password_confirmation = '';
    form.clearErrors();
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    showPassword.value = false;
    showPasswordConfirm.value = false;
}

// Blocks non-numeric key strokes on keypress
function handleIdKeypress(event: KeyboardEvent) {
    if (!/[0-9]/.test(event.key)) {
        event.preventDefault();
    }
}

// Restricts typed and pasted input to exactly numbers and trims to 6 digits maximum
function handleIdInput(event: Event) {
    const input = event.target as HTMLInputElement;
    // Strip out all non-numeric characters if pasted
    let value = input.value.replace(/\D/g, '');

    // Slice down to a maximum length of 6
    if (value.length > 6) {
        value = value.slice(0, 6);
    }

    form.id_number = value;
}

function submitForm() {
    // Client side guard to verify it's exactly 6 digits before hitting the backend endpoint
    if (form.id_number.length !== 6) {
        form.setError('id_number', 'The ID number must be exactly 6 digits.');
        return;
    }

    if (editingUser.value) {
        form.put(`/users/${editingUser.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/users', {
            onSuccess: () => closeModal(),
        });
    }
}

function confirmDelete(user: User) {
    deletingUser.value = user;
    showDeleteConfirm.value = true;
}

function deleteUser() {
    if (!deletingUser.value) return;
    router.delete(`/users/${deletingUser.value.id}`, {
        onSuccess: () => {
            showDeleteConfirm.value = false;
            deletingUser.value = null;
        },
    });
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
    });
}

const roleStyles: Record<string, string> = {
    admin: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    user: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
};
</script>

<template>
    <AppLayout>

        <!-- Background Wrapper -->
        <div
            class="relative min-h-screen bg-cover bg-center bg-fixed"
            style="background-image: url('/images/landingbg.png')"
        >
            <div class="absolute inset-0 bg-black/40"></div>

            <!-- Main Content -->
            <div class="relative z-10 flex flex-col gap-6 p-4 sm:p-6 lg:p-8">

                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-white drop-shadow-md">User Management</h1>
                        <p class="mt-1 text-sm text-gray-200">{{ users.total }} total users</p>
                    </div>
                    <button
                        @click="openCreate"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-medium text-white shadow-lg backdrop-blur-md transition hover:bg-white/20 active:scale-95"
                    >
                        <Plus class="h-4 w-4" />
                        Add User
                    </button>
                </div>

                <!-- Flash messages -->
                <TransitionGroup
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform translate-y-[-10px] opacity-0"
                    enter-to-class="transform translate-y-0 opacity-100"
                >
                    <div v-if="$page.props.flash?.success" key="success" class="rounded-xl border border-green-400/30 bg-green-400/10 px-4 py-3 text-sm text-green-300 backdrop-blur-md">
                        {{ $page.props.flash.success }}
                    </div>
                    <div v-if="$page.props.flash?.error" key="error" class="rounded-xl border border-red-400/30 bg-red-400/10 px-4 py-3 text-sm text-red-300 backdrop-blur-md">
                        {{ $page.props.flash.error }}
                    </div>
                </TransitionGroup>

                <!-- Search -->
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-300" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search users..."
                        class="w-full rounded-xl border border-white/20 bg-white/10 py-2.5 pr-4 pl-10 text-sm text-white placeholder-gray-300 shadow-lg backdrop-blur-md focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all"
                    />
                </div>

                <!-- Table Container -->
                <div class="overflow-x-auto rounded-2xl border border-white/20 bg-white/10 shadow-2xl backdrop-blur-xl">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/10 bg-white/10">
                                <th class="px-4 py-4 text-left font-semibold text-gray-200">Name</th>
                                <th class="hidden md:table-cell px-4 py-4 text-left font-semibold text-gray-200">Email</th>
                                <th class="hidden sm:table-cell px-4 py-4 text-left font-semibold text-gray-200">ID Number</th>
                                <th class="px-4 py-4 text-left font-semibold text-gray-200">Role</th>
                                <th class="hidden lg:table-cell px-4 py-4 text-left font-semibold text-gray-200">Joined</th>
                                <th class="px-4 py-4 text-right font-semibold text-gray-200">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr v-if="filteredUsers.length === 0">
                                <td colspan="6" class="px-4 py-12 text-center text-gray-300 italic">
                                    No users found matching your search.
                                </td>
                            </tr>
                            <tr
                                v-for="user in filteredUsers"
                                :key="user.id"
                                class="transition hover:bg-white/5"
                            >
                                <td class="px-4 py-4 font-medium text-white">
                                    {{ user.name }}
                                    <div class="md:hidden text-xs text-gray-400 font-normal mt-0.5">{{ user.email }}</div>
                                </td>
                                <td class="hidden md:table-cell px-4 py-4 text-gray-300">{{ user.email }}</td>
                                <td class="hidden sm:table-cell px-4 py-4">
                                    <span class="inline-block rounded-md bg-white/5 px-2 py-1 text-xs font-mono text-gray-200">
                                        {{ user.id_number }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span
                                        :class="[
                                            'inline-block rounded-full px-2.5 py-0.5 text-xs font-medium uppercase tracking-wider',
                                            roleStyles[user.role]
                                        ]"
                                    >
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="hidden lg:table-cell px-4 py-4 text-xs text-gray-300">{{ formatDate(user.created_at) }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="openEdit(user)"
                                            class="rounded-lg p-2 text-gray-300 transition hover:bg-white/10 hover:text-blue-300"
                                            title="Edit User"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="confirmDelete(user)"
                                            class="rounded-lg p-2 text-gray-300 transition hover:bg-red-400/10 hover:text-red-300"
                                            title="Delete User"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="users.last_page > 1"
                    class="flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-200 pb-8"
                >
                    <span>Showing Page {{ users.current_page }} of {{ users.last_page }}</span>
                    <div class="flex flex-wrap justify-center gap-1">
                        <Link
                            v-for="link in users.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            :class="[
                                'rounded-lg px-3 py-2 transition text-xs sm:text-sm',
                                link.active
                                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30'
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

        <!-- Create / Edit Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal" />

                    <!-- Modal Card -->
                    <div class="relative flex flex-col w-full max-w-md max-h-[90vh] rounded-2xl bg-white dark:bg-gray-800 shadow-2xl overflow-hidden">

                        <!-- Header -->
                        <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ editingUser ? 'Edit User Profile' : 'Register New User' }}
                            </h2>
                            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition p-1">
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Scrollable Body -->
                        <div class="flex-1 overflow-y-auto p-5 space-y-4">
                            <!-- Name -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Full Name</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all"
                                    :class="{ 'border-red-400 ring-red-100': form.errors.name }"
                                    placeholder="Juan Dela Cruz"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Email Address</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all"
                                    :class="{ 'border-red-400 ring-red-100': form.errors.email }"
                                    placeholder="juan@example.com"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                            </div>

                            <!-- ID & Role Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">ID Number (6 Digits)</label>
                                    <input
                                        :value="form.id_number"
                                        @keypress="handleIdKeypress"
                                        @input="handleIdInput"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        maxlength="6"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        :class="{ 'border-red-400 ring-red-100': form.errors.id_number }"
                                        placeholder="123456"
                                    />
                                    <p v-if="form.errors.id_number" class="mt-1 text-xs text-red-500">{{ form.errors.id_number }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Role</label>
                                    <select
                                        v-model="form.role"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    >
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Password Fields -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Password <span v-if="editingUser" class="lowercase font-normal opacity-70">(Leave blank to keep current)</span>
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all"
                                        :class="{ 'border-red-400 ring-red-100': form.errors.password }"
                                        placeholder="••••••••"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                                    >
                                        <EyeOff v-if="showPassword" class="h-4 w-4" />
                                        <Eye v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Confirm Password</label>
                                <div class="relative">
                                    <input
                                        v-model="form.password_confirmation"
                                        :type="showPasswordConfirm ? 'text' : 'password'"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="••••••••"
                                    />
                                    <button
                                        type="button"
                                        @click="showPasswordConfirm = !showPasswordConfirm"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                                    >
                                        <EyeOff v-if="showPasswordConfirm" class="h-4 w-4" />
                                        <Eye v-else class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex flex-col sm:flex-row gap-3 p-5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            <button
                                @click="closeModal"
                                class="order-2 sm:order-1 flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-all"
                            >
                                Cancel
                            </button>
                            <button
                                @click="submitForm"
                                :disabled="form.processing"
                                class="order-1 sm:order-2 flex-1 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60 shadow-lg shadow-blue-500/20 transition-all"
                            >
                                {{ form.processing ? 'Saving...' : (editingUser ? 'Update User' : 'Create User') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Delete Confirm Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDeleteConfirm = false" />
                    <div class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-gray-800 shadow-2xl p-6">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                            <Trash2 class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Confirm Deletion</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                            Are you sure you want to delete <strong class="text-gray-900 dark:text-white">{{ deletingUser?.name }}</strong>?
                        </p>
                        <div class="flex gap-3">
                            <button
                                @click="showDeleteConfirm = false"
                                class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                            >
                                No, Keep
                            </button>
                            <button
                                @click="deleteUser"
                                class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700 shadow-lg shadow-red-500/20 transition-colors"
                            >
                                Yes, Delete
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </AppLayout>
</template>

<style scoped>
/* Smooth scrollbar for the modal body */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.3);
    border-radius: 10px;
}
.dark .overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(75, 85, 99, 0.5);
}
</style>
