<script setup lang="ts">
import { ref } from 'vue';
import { Form, Head } from '@inertiajs/vue3';
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/user-password';
import { type BreadcrumbItem } from '@/types';
import { Eye, EyeOff } from 'lucide-vue-next';

const showCurrentPassword      = ref(false);
const showNewPassword          = ref(false);
const showConfirmPassword      = ref(false);
</script>

<template>
    <AppLayout>

        <h1 class="sr-only">Password Settings</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Update password"
                    description="Ensure your account is using a long, random password to stay secure"
                />

                <Form
                    v-bind="PasswordController.update.form()"
                    :options="{
                        preserveScroll: true,
                    }"
                    reset-on-success
                    :reset-on-error="[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <!-- Current Password -->
                    <div class="grid gap-2">
                        <Label for="current_password">Current password</Label>
                        <div class="relative">
                            <Input
                                id="current_password"
                                name="current_password"
                                :type="showCurrentPassword ? 'text' : 'password'"
                                class="mt-1 block w-full pr-10"
                                autocomplete="current-password"
                                placeholder="Current password"
                            />
                            <button
                                type="button"
                                @click="showCurrentPassword = !showCurrentPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                                :title="showCurrentPassword ? 'Hide password' : 'Show password'"
                            >
                                <EyeOff v-if="showCurrentPassword" class="h-4 w-4" />
                                <Eye v-else class="h-4 w-4" />
                            </button>
                        </div>
                        <InputError :message="errors.current_password" />
                    </div>

                    <!-- New Password -->
                    <div class="grid gap-2">
                        <Label for="password">New password</Label>
                        <div class="relative">
                            <Input
                                id="password"
                                name="password"
                                :type="showNewPassword ? 'text' : 'password'"
                                class="mt-1 block w-full pr-10"
                                autocomplete="new-password"
                                placeholder="New password"
                            />
                            <button
                                type="button"
                                @click="showNewPassword = !showNewPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                                :title="showNewPassword ? 'Hide password' : 'Show password'"
                            >
                                <EyeOff v-if="showNewPassword" class="h-4 w-4" />
                                <Eye v-else class="h-4 w-4" />
                            </button>
                        </div>
                        <InputError :message="errors.password" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirm password</Label>
                        <div class="relative">
                            <Input
                                id="password_confirmation"
                                name="password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                class="mt-1 block w-full pr-10"
                                autocomplete="new-password"
                                placeholder="Confirm password"
                            />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                                :title="showConfirmPassword ? 'Hide password' : 'Show password'"
                            >
                                <EyeOff v-if="showConfirmPassword" class="h-4 w-4" />
                                <Eye v-else class="h-4 w-4" />
                            </button>
                        </div>
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-password-button"
                        >Save password</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
