<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-white shadow-sm"
    >
        <AppLogoIcon class="size-6 object-contain" />
    </div>

    <div class="ml-3 flex flex-col justify-center">
        <span class="text-sm font-bold leading-none tracking-wide">
            D.A.M.S.
        </span>
        <span class="mt-1 text-[9px] font-medium leading-[1.1] text-slate-500 uppercase">
            Digital Archive &<br />Mapping System
        </span>
    </div>
</template>
