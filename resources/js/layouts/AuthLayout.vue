<script setup lang="ts">
interface Props {
    title?: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <!--
      The 'overflow-hidden' on the wrapper prevents any weird
      scrolling if the image tries to expand.
    -->
    <div class="relative h-screen w-screen overflow-hidden flex items-center justify-center px-4">

        <!--
          The Background Image Layer
          We put it in its own div to control the scale perfectly.
        -->
        <div
            class="absolute inset-0 z-0"
            style="
                background-image: url('/images/landingbg.png');
                background-position: center;
                background-repeat: no-repeat;
                background-size: 100% 100%;
            "
        ></div>

        <!-- Dark Tint Overlay -->
        <div class="absolute inset-0 z-10 bg-black/50"></div>

        <!-- Transparent Auth Card -->
        <div class="relative z-20 w-full max-w-md overflow-hidden rounded-2xl border border-white/20 bg-white/10 p-8 shadow-2xl backdrop-blur-md dark:bg-black/40">

            <div class="flex flex-col space-y-2 text-center mb-8">
                <h1 v-if="title" class="text-2xl font-bold tracking-tight text-white">
                    {{ title }}
                </h1>
                <p v-if="description" class="text-sm text-zinc-300">
                    {{ description }}
                </p>
            </div>

            <!-- Form Content -->
            <slot />
        </div>
    </div>
</template>

<style scoped>
/* Ensures labels and text inside the slot are white to stay visible */
:deep(label) {
    color: rgba(255, 255, 255, 0.9) !important;
}

:deep(input) {
    background-color: rgba(255, 255, 255, 0.1) !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
}

:deep(input::placeholder) {
    color: rgba(255, 255, 255, 0.5);
}

/* Fix for "Remember Me" checkbox text */
:deep(span) {
    color: white;
}
</style>
