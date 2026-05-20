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
            <div class="group-data-[collapsible=icon]:hidden px-3 py-1 border-t border-sidebar-border">
                <p class="text-[5px] text-muted-foreground/60 leading-none">Developed by</p>
                <p class="text-[6px] font-semibold text-muted-foreground/80 leading-none">John Brent Arroyo Cuario (v.1.0.0)</p>
                <p class="text-[6px] font-semibold text-muted-foreground/80 leading-none">brentcuario01@gmail.com</p>
                <p class="text-[6px] text-muted-foreground/50 leading-none">© {{ new Date().getFullYear() }}</p>
            </div>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
