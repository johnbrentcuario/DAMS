<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
</script>

<template>
    <AuthBase
        title="Create account"
        description="Fill in your details to get started"
    >
        <Head title="Register" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-3"
        >
            <div class="grid gap-3">
                <!-- Name & Email in a row on larger than mobile, stacked on mobile -->
                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="name" class="text-xs">Name</Label>
                        <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" name="name" placeholder="Full name" class="h-9" />
                        <InputError :message="errors.name" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="email" class="text-xs">Email</Label>
                        <Input id="email" type="email" required :tabindex="2" autocomplete="email" name="email" placeholder="email@example.com" class="h-9" />
                        <InputError :message="errors.email" />
                    </div>
                </div>

                <!-- ID Number (Full Width) -->
                <div class="grid gap-1.5">
                    <Label for="id_number" class="text-xs">ID Number</Label>
                    <Input id="id_number" type="text" required :tabindex="3" name="id_number" placeholder="ID Number" class="h-9" />
                    <InputError :message="errors.id_number" />
                </div>

                <!-- Password Row -->
                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="password" class="text-xs">Password</Label>
                        <Input id="password" type="password" required :tabindex="4" autocomplete="new-password" name="password" placeholder="Password" class="h-9" />
                        <InputError :message="errors.password" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="password_confirmation" class="text-xs">Confirm</Label>
                        <Input id="password_confirmation" type="password" required :tabindex="4" name="password_confirmation" placeholder="Confirm" class="h-9" />
                        <InputError :message="errors.password_confirmation" />
                    </div>
                </div>

                <Button
                    type="submit"
                    class="mt-1 w-full h-9"
                    tabindex="5"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" class="mr-2 h-4 w-4" />
                    Register
                </Button>
            </div>

            <div class="text-center text-xs text-muted-foreground">
                Joined already?
                <TextLink :href="login()" class="underline underline-offset-2" :tabindex="6">Log in</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
