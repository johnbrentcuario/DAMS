<script setup lang="ts">
interface Props {
    title?: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="relative h-screen w-screen overflow-hidden flex items-center justify-center px-4">

        <div
            class="absolute inset-0 z-0"
            style="
                background-image: url('/images/landingbg.png');
                background-position: center;
                background-repeat: no-repeat;
                background-size: 100% 100%;
            "
        ></div>

        <div class="absolute inset-0 z-10 bg-black/50"></div>

        <div class="relative z-20 w-full max-w-md overflow-hidden rounded-2xl border border-white/20 bg-white/10 p-8 shadow-2xl backdrop-blur-md dark:bg-black/40">

            <!-- Logo + System Name -->
            <div class="flex flex-col items-center gap-3 mb-8">
                <div class="logo-wrap">
                    <img
                        src="/favicon.ico"
                        alt="DAMS Logo"
                        class="h-14 w-14 object-contain relative z-10"
                    />
                </div>
                <p class="system-name">
                    Digital Archives &amp; Mapping System
                </p>
            </div>

            <!-- Title + Description -->
            <div class="flex flex-col items-center gap-1 mb-8">
                <h1 v-if="title" class="auth-title">
                    {{ title }}
                </h1>
                <p v-if="description" class="auth-description">
                    {{ description }}
                </p>
            </div>

            <slot />
        </div>
    </div>
</template>

<style scoped>
.logo-wrap {
    position: relative;
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: radial-gradient(circle at 40% 40%, #1a4a2e, #0f2a1e);
    border: 1px solid rgba(74, 222, 128, 0.25);
    box-shadow:
        0 0 0 4px rgba(74, 222, 128, 0.08),
        0 0 20px rgba(74, 222, 128, 0.2),
        0 4px 16px rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4px;
}

.logo-wrap::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 16px;
    background: radial-gradient(circle at 30% 30%, rgba(74, 222, 128, 0.12), transparent 70%);
    pointer-events: none;
}

.system-name {
    font-size: 0.875rem;
    font-weight: 500;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: rgba(74, 222, 128, 0.85);
    text-align: center;
    line-height: 1.4;
    margin: 0;
}

.auth-title {
    font-size: 1.375rem;
    font-weight: 600;
    letter-spacing: -0.02em;
    color: #ffffff;
    text-align: center;
    line-height: 1.3;
    margin: 0;
}

.auth-description {
    font-size: 0.8125rem;
    font-weight: 400;
    color: rgba(255, 255, 255, 0.55);
    text-align: center;
    line-height: 1.5;
    margin: 0;
}

:deep(label) {
    color: rgba(255, 255, 255, 0.85) !important;
    font-size: 0.8125rem !important;
    font-weight: 500 !important;
    letter-spacing: 0.01em !important;
}

:deep(input) {
    background-color: rgba(255, 255, 255, 0.08) !important;
    border-color: rgba(255, 255, 255, 0.15) !important;
    color: white !important;
    font-size: 0.875rem !important;
}

:deep(input::placeholder) {
    color: rgba(255, 255, 255, 0.35);
}

:deep(span) {
    color: white;
}
</style>
