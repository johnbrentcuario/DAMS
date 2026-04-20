<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
// 1. Import your PNG file
import logoUrl from '@/assets/nia.png';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>
<template>
    <img
        :src="logoUrl"
        :class="className"
        v-bind="$attrs"
        class="object-contain scale-125"  />
</template>

<img
    :src="logoUrl"
    :class="className"
    v-bind="$attrs"
    style="filter: drop-shadow(0px 0px 2px rgba(255, 255, 255, 0.8));"
/>
