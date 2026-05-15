<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

type Theme = 'nis-light' | 'nis-dark' | 'system';
type FontSize = 'sm' | 'md' | 'lg';
type AccentColor = 'green' | 'gold' | 'blue' | 'rice' | 'brown';

const selectedTheme = ref<Theme>('nis-light');
const selectedFontSize = ref<FontSize>('md');
const selectedAccent = ref<AccentColor>('green');
const saved = ref(false);

onMounted(() => {
    const t = localStorage.getItem('nis-theme') as Theme | null;
    const f = localStorage.getItem('nis-font-size') as FontSize | null;
    const a = localStorage.getItem('nis-accent') as AccentColor | null;
    if (t) selectedTheme.value = t;
    if (f) selectedFontSize.value = f;
    if (a) selectedAccent.value = a;
    applyAll();
});

function applyAll() {
    applyTheme(selectedTheme.value);
    applyFontSize(selectedFontSize.value);
    applyAccent(selectedAccent.value);
}

function applyTheme(theme: Theme) {
    const root = document.documentElement;
    if (theme === 'nis-dark') {
        root.classList.remove('nis-light');
        root.classList.add('dark');
    } else if (theme === 'nis-light') {
        root.classList.remove('dark');
        root.classList.add('nis-light');
    } else {
        root.classList.remove('nis-light');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        root.classList.toggle('dark', prefersDark);
    }
}

function applyFontSize(size: FontSize) {
    const map: Record<FontSize, string> = { sm: '14px', md: '16px', lg: '18px' };
    document.documentElement.style.fontSize = map[size];
}

function applyAccent(accent: AccentColor) {
    // Full hsl() values matching your app.css format
    const map: Record<AccentColor, { primary: string; primaryFg: string; ring: string }> = {
        green: {
            primary:   'hsl(137 56% 23%)',  // #1a5c2a
            primaryFg: 'hsl(0 0% 98%)',
            ring:      'hsl(137 56% 23%)',
        },
        gold: {
            primary:   'hsl(43 72% 47%)',   // #c9a227
            primaryFg: 'hsl(0 0% 9%)',
            ring:      'hsl(43 72% 47%)',
        },
        blue: {
            primary:   'hsl(207 69% 37%)',  // #1d6fa4
            primaryFg: 'hsl(0 0% 98%)',
            ring:      'hsl(207 69% 37%)',
        },
        rice: {
            primary:   'hsl(83 46% 50%)',   // #8fb84a
            primaryFg: 'hsl(0 0% 9%)',
            ring:      'hsl(83 46% 50%)',
        },
        brown: {
            primary:   'hsl(25 46% 32%)',   // #7a4f2d
            primaryFg: 'hsl(0 0% 98%)',
            ring:      'hsl(25 46% 32%)',
        },
    };

    const root = document.documentElement;
    const { primary, primaryFg, ring } = map[accent];
    root.style.setProperty('--primary', primary);
    root.style.setProperty('--primary-foreground', primaryFg);
    root.style.setProperty('--ring', ring);
    root.style.setProperty('--sidebar-primary', primary);
    root.style.setProperty('--sidebar-primary-foreground', primaryFg);
    root.style.setProperty('--sidebar-ring', ring);
}

function saveSettings() {
    localStorage.setItem('nis-theme',     selectedTheme.value);
    localStorage.setItem('nis-font-size', selectedFontSize.value);
    localStorage.setItem('nis-accent',    selectedAccent.value);
    applyAll();
    saved.value = true;
    setTimeout(() => (saved.value = false), 2500);
}

const themes: {
    value: Theme;
    label: string;
    description: string;
    preview: { bg: string; sidebar: string; accent: string; text: string };
}[] = [
    {
        value: 'nis-light',
        label: 'NIS Light',
        description: 'Daylight — green fields & clear water',
        preview: { bg: '#f0f7f1', sidebar: '#1a5c2a', accent: '#c9a227', text: '#1a3320' },
    },
    {
        value: 'nis-dark',
        label: 'NIS Dark',
        description: 'Night ops — deep forest & moonlit canals',
        preview: { bg: '#0d1f10', sidebar: '#091509', accent: '#e8b84b', text: '#c8dfc8' },
    },
    {
        value: 'system',
        label: 'System',
        description: 'Follows your device preference',
        preview: { bg: '#f3f3f3', sidebar: '#3d3d3d', accent: '#888', text: '#1a1a1a' },
    },
];

const fontSizes: { value: FontSize; label: string; size: string }[] = [
    { value: 'sm', label: 'Small',  size: 'text-sm'  },
    { value: 'md', label: 'Medium', size: 'text-base' },
    { value: 'lg', label: 'Large',  size: 'text-xl'  },
];

const accents: { value: AccentColor; label: string; hex: string }[] = [
    { value: 'green', label: 'Forest Green', hex: '#1a5c2a' },
    { value: 'gold',  label: 'Harvest Gold', hex: '#c9a227' },
    { value: 'blue',  label: 'Canal Blue',   hex: '#1d6fa4' },
    { value: 'rice',  label: 'Rice Field',   hex: '#8fb84a' },
    { value: 'brown', label: 'Soil Brown',   hex: '#7a4f2d' },
];
</script>

<template>
    <div class="space-y-8">

        <!-- NIS Identity Banner -->
        <div class="relative overflow-hidden rounded-xl bg-[#1a5c2a] px-5 py-4 shadow-md">
            <svg
                class="pointer-events-none absolute inset-0 h-full w-full opacity-[0.08]"
                xmlns="http://www.w3.org/2000/svg"
                preserveAspectRatio="xMidYMid slice"
            >
                <defs>
                    <pattern id="nis-ripple" width="56" height="56" patternUnits="userSpaceOnUse">
                        <circle cx="28" cy="28" r="22" fill="none" stroke="white" stroke-width="1"/>
                        <circle cx="28" cy="28" r="13" fill="none" stroke="white" stroke-width="0.6"/>
                        <circle cx="28" cy="28" r="5"  fill="none" stroke="white" stroke-width="0.6"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#nis-ripple)" />
            </svg>

            <div class="relative flex items-center gap-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-[#c9a227]/60 bg-white/10">
                    <svg class="h-5 w-5 text-[#c9a227]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M3 12h18M12 3a13 13 0 0 1 0 18M12 3a13 13 0 0 0 0 18"/>
                        <path d="M6.5 7.5c2 1.5 3 3 5.5 4.5 2.5-1.5 3.5-3 5.5-4.5"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold tracking-widest text-[#c9a227] uppercase">
                        Digital Archives & Mapping System
                    </p>
                </div>
            </div>
        </div>

        <!-- ── Theme ──────────────────────────────────────────────────────── -->
        <div class="space-y-3">
            <div>
                <Label class="text-sm font-semibold">Color Theme</Label>
                <p class="mt-0.5 text-xs text-muted-foreground">
                    Choose a theme that suits your working environment.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <button
                    v-for="theme in themes"
                    :key="theme.value"
                    type="button"
                    @click="selectedTheme = theme.value"
                    :class="[
                        'group relative flex flex-col overflow-hidden rounded-xl border-2 text-left transition-all duration-200',
                        selectedTheme === theme.value
                            ? 'border-[#1a5c2a] shadow-md ring-2 ring-[#1a5c2a]/20'
                            : 'border-border hover:border-[#1a5c2a]/40',
                    ]"
                >
                    <div
                        class="relative h-[76px] w-full overflow-hidden"
                        :style="{ backgroundColor: theme.preview.bg }"
                    >
                        <div
                            class="absolute left-0 top-0 h-full w-9"
                            :style="{ backgroundColor: theme.preview.sidebar }"
                        >
                            <div class="mt-2.5 space-y-1.5 px-1.5">
                                <div class="h-1.5 w-5 rounded-sm" :style="{ backgroundColor: theme.preview.accent, opacity: 0.9 }"/>
                                <div class="h-1 w-4 rounded-sm bg-white/30"/>
                                <div class="h-1 w-5 rounded-sm bg-white/20"/>
                                <div class="h-1 w-3 rounded-sm bg-white/20"/>
                            </div>
                        </div>
                        <div class="absolute left-11 right-2 top-2.5 space-y-1.5">
                            <div class="h-2 w-14 rounded-sm" :style="{ backgroundColor: theme.preview.text, opacity: 0.6 }"/>
                            <div class="h-1.5 w-20 rounded-sm" :style="{ backgroundColor: theme.preview.text, opacity: 0.25 }"/>
                            <div class="mt-1.5 h-5 w-14 rounded-md" :style="{ backgroundColor: theme.preview.accent, opacity: 0.8 }"/>
                        </div>
                        <Transition
                            enter-active-class="transition duration-150"
                            enter-from-class="opacity-0 scale-75"
                            leave-active-class="transition duration-100"
                            leave-to-class="opacity-0 scale-75"
                        >
                            <div
                                v-if="selectedTheme === theme.value"
                                class="absolute right-2 top-2 flex h-5 w-5 items-center justify-center rounded-full bg-[#1a5c2a] shadow"
                            >
                                <svg class="h-3 w-3 text-white" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 6l3 3 5-5"/>
                                </svg>
                            </div>
                        </Transition>
                    </div>

                    <div class="px-3 py-2.5">
                        <p class="text-sm font-medium leading-none text-foreground">{{ theme.label }}</p>
                        <p class="mt-1 text-xs leading-tight text-muted-foreground">{{ theme.description }}</p>
                    </div>
                </button>
            </div>
        </div>

        <!-- ── Font Size ───────────────────────────────────────────────────── -->
        <div class="space-y-3">
            <div>
                <Label class="text-sm font-semibold">Font Size</Label>
                <p class="mt-0.5 text-xs text-muted-foreground">
                    Adjust the text size for comfortable reading across the portal.
                </p>
            </div>

            <div class="flex gap-3">
                <button
                    v-for="font in fontSizes"
                    :key="font.value"
                    type="button"
                    @click="selectedFontSize = font.value"
                    :class="[
                        'flex flex-col items-center justify-center gap-1 rounded-xl border-2 px-6 py-3 transition-all duration-200',
                        selectedFontSize === font.value
                            ? 'border-[#1a5c2a] bg-[#1a5c2a]/5 text-[#1a5c2a] shadow-sm'
                            : 'border-border text-muted-foreground hover:border-[#1a5c2a]/40',
                    ]"
                >
                    <span :class="['font-bold leading-none', font.size]">Aa</span>
                    <span class="text-xs">{{ font.label }}</span>
                </button>
            </div>
        </div>

        <!-- ── Accent Color ────────────────────────────────────────────────── -->
        <div class="space-y-3">
            <div>
                <Label class="text-sm font-semibold">Accent Color</Label>
                <p class="mt-0.5 text-xs text-muted-foreground">
                    Pick an accent from the NIS brand palette. Affects buttons, links, and focus rings.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button
                    v-for="color in accents"
                    :key="color.value"
                    type="button"
                    :title="color.label"
                    @click="selectedAccent = color.value"
                    :class="[
                        'relative h-9 w-9 rounded-full border-2 shadow-sm transition-transform duration-150 hover:scale-110 focus-visible:outline-none',
                        selectedAccent === color.value
                            ? 'border-white ring-2 ring-offset-2'
                            : 'border-white/60',
                    ]"
                    :style="{
                        backgroundColor: color.hex,
                        '--tw-ring-color': color.hex,
                    }"
                >
                    <Transition
                        enter-active-class="transition duration-150"
                        enter-from-class="opacity-0 scale-50"
                        leave-active-class="transition duration-100"
                        leave-to-class="opacity-0"
                    >
                        <svg
                            v-if="selectedAccent === color.value"
                            class="absolute inset-0 m-auto h-4 w-4 text-white drop-shadow"
                            viewBox="0 0 12 12" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
                        >
                            <path d="M2 6l3 3 5-5"/>
                        </svg>
                    </Transition>
                    <span class="sr-only">{{ color.label }}</span>
                </button>
            </div>

            <p class="text-xs text-muted-foreground">
                Selected: <span class="font-medium text-foreground">{{ accents.find(a => a.value === selectedAccent)?.label }}</span>
            </p>
        </div>

        <!-- ── Save ───────────────────────────────────────────────────────── -->
        <div class="flex items-center gap-4 border-t border-border pt-6">
            <Button
                type="button"
                @click="saveSettings"
                class="bg-[#1a5c2a] text-white hover:bg-[#144d22] focus-visible:ring-[#1a5c2a]"
            >
                Save preferences
            </Button>

            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 translate-y-1"
                leave-active-class="transition duration-150 ease-in"
                leave-to-class="opacity-0"
            >
                <p
                    v-show="saved"
                    class="flex items-center gap-1.5 text-sm font-medium text-[#1a5c2a]"
                >
                    <svg class="h-4 w-4" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M13.78 4.22a.75.75 0 010 1.06l-7.25 7.25a.75.75 0 01-1.06 0L2.22 9.28a.75.75 0 011.06-1.06L6 10.94l6.72-6.72a.75.75 0 011.06 0z"/>
                    </svg>
                    Saved.
                </p>
            </Transition>
        </div>

    </div>
</template>
