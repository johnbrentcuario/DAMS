<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Trash2, Plus, X, Search } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
    id_number: string;
    role: 'admin' | 'staff';
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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'User Management', href: '/users' },
];

const showModal = ref(false);
const editingUser = ref<User | null>(null);
const showDeleteConfirm = ref(false);
const deletingUser = ref<User | null>(null);
const searchQuery = ref('');

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
    role: 'staff' as 'admin' | 'staff',
    password: '',
    password_confirmation: '',
});

function openCreate() {
    editingUser.value = null;
    form.reset();
    form.role = 'staff';
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
}

function submitForm() {
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
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="User Management" />

        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">User Management</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ users.total }} total users</p>
                </div>
                <button
                    @click="openCreate"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition-colors"
                >
                    <Plus class="h-4 w-4" />
                    Add User
                </button>
            </div>

            <!-- Flash messages -->
            <div v-if="$page.props.flash?.success" class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                {{ $page.props.flash.error }}
            </div>

            <!-- Search -->
            <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search users..."
                    class="w-full rounded-lg border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-400"
                />
            </div>

            <!-- Table -->
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60">
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Email</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">ID Number</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Role</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Joined</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr v-if="filteredUsers.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-gray-400 dark:text-gray-500">
                                No users found.
                            </td>
                        </tr>
                        <tr
                            v-for="user in filteredUsers"
                            :key="user.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors"
                        >
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ user.name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ user.email }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-mono text-gray-700 dark:text-gray-300">
                                    {{ user.id_number }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'inline-block rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        user.role === 'admin'
                                            ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'
                                            : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                    ]"
                                >
                                    {{ user.role === 'admin' ? 'Admin' : 'Staff' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ formatDate(user.created_at) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="openEdit(user)"
                                        class="rounded-md p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="confirmDelete(user)"
                                        class="rounded-md p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
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
            <div v-if="users.last_page > 1" class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                <span>Page {{ users.current_page }} of {{ users.last_page }}</span>
                <div class="flex gap-1">
                    <Link
                        v-for="link in users.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        :class="[
                            'px-3 py-1.5 rounded-md transition-colors',
                            link.active ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700',
                            !link.url ? 'pointer-events-none opacity-40' : '',
                        ]"
                        v-html="link.label"
                        preserve-scroll
                    />
                </div>
            </div>
        </div>

        <!-- Create / Edit Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal" />
                <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 shadow-2xl p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ editingUser ? 'Edit User' : 'Create User' }}
                        </h2>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="{ 'border-red-400': form.errors.name }"
                                placeholder="Juan Dela Cruz"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="{ 'border-red-400': form.errors.email }"
                                placeholder="juan@example.com"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                        </div>

                        <!-- ID Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID Number</label>
                            <input
                                v-model="form.id_number"
                                type="text"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="{ 'border-red-400': form.errors.id_number }"
                                placeholder="123456"
                            />
                            <p v-if="form.errors.id_number" class="mt-1 text-xs text-red-500">{{ form.errors.id_number }}</p>
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                            <select
                                v-model="form.role"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="{ 'border-red-400': form.errors.role }"
                            >
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                            <p v-if="form.errors.role" class="mt-1 text-xs text-red-500">{{ form.errors.role }}</p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Password <span v-if="editingUser" class="text-gray-400 font-normal">(leave blank to keep)</span>
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                :class="{ 'border-red-400': form.errors.password }"
                                placeholder="••••••••"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="••••••••"
                            />
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button
                            @click="closeModal"
                            class="flex-1 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitForm"
                            :disabled="form.processing"
                            class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60 transition-colors"
                        >
                            {{ form.processing ? 'Saving...' : (editingUser ? 'Save Changes' : 'Create User') }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Delete Confirm Modal -->
        <Teleport to="body">
            <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showDeleteConfirm = false" />
                <div class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-gray-800 shadow-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete User</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                        Are you sure you want to delete <strong class="text-gray-900 dark:text-white">{{ deletingUser?.name }}</strong>? This action cannot be undone.
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="showDeleteConfirm = false"
                            class="flex-1 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="deleteUser"
                            class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
