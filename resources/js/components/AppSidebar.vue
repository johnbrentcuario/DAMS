<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ClipboardList, Folder, FileBarChart2, LayoutGrid, List, MapPin, Scissors, Users } from 'lucide-vue-next';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem, type AppPageProps } from '@/types';
import AppLogo from './AppLogo.vue';

const page = usePage<AppPageProps>();
const isAdmin = computed(() => page.props.auth.user?.role === 'admin');

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Folders',
        href: '/files',
        icon: Folder,
    },
    {
        title: 'Employment Types',
        href: '/lists',
        icon: List,
    },
    {
        title: 'Locations',
        href: '/physical-locations',
        icon: MapPin,
    },
    {
        title: 'Mode of Separation',
        href: '/separation-modes',  // 👈 adjust to your actual route
        icon: Scissors,
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'User Management',
        href: '/users',
        icon: Users,
    },
    {
        title: 'Activity Log',
        href: '/activity-log',
        icon: ClipboardList,
    },
    {
        title: 'Reports',
        href: '/reports',
        icon: FileBarChart2,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain v-if="isAdmin" :items="adminNavItems" label="Admin" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
