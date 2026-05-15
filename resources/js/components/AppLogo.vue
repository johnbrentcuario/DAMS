<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <!-- Wrap everything in a flex container that centers its items vertically -->
    <div class="flex items-center justify-center">

        <!-- Logo Container (Now a circle) -->
        <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-white shadow-sm">
            <AppLogoIcon class="size-6 object-contain" />
        </div>

        <!-- Text Container -->
        <div class="ml-2 flex flex-col justify-center">
            <span class="text-[15px] font-bold leading-none tracking-wide">
                DAMS
            </span>
            <span class="mt-1 text-[10px] font-medium leading-[1.1] text-slate-500 uppercase">
                Digital Archives &<br />Mapping System
            </span>
        </div>

    </div>
</template>
