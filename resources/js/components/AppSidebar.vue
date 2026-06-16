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
        title: 'Inactive 201 Files',
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
        href: '/separation-modes',
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
    <!-- Developer signature — hidden when sidebar is collapsed to icon mode -->
    <div class="group-data-[collapsible=icon]:hidden px-3 py-0.5 border-t border-sidebar-border text-[9px] text-muted-foreground/70 tracking-tight">
        <p>Developed by:
            <a href="https://www.facebook.com/john.brent.cuario/"
               target="_blank"
               rel="noopener noreferrer"
               class="font-semibold hover:text-blue-400 hover:underline decoration-blue-400/40 underline-offset-2 transition-all duration-200">
                John Brent Arroyo Cuario
            </a>
        </p>
        <p>brentcuario01@gmail.com</p>
        <p>© {{ new Date().getFullYear() }}</p>
    </div>
</SidebarFooter>
    </Sidebar>
    <slot />
</template>
