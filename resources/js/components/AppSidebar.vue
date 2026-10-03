<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    IconCalendarEvent,
    IconChartBar,
    IconChevronRight,
    IconClick,
    IconKey,
    IconPlugConnected,
    IconTag,
    IconUsers,
    IconWorld,
} from '@tabler/icons-vue';
import { computed } from 'vue';

import NavMain from '@/components/NavMain.vue';
import { Avatar } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sidebar,
    SidebarContent,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import WorkspaceMenuContent from '@/components/WorkspaceMenuContent.vue';
import { index as analyticsIndex } from '@/routes/analytics';
import { index as eventsIndex } from '@/routes/events';
import { index as linksIndex } from '@/routes/links';
import { index as apiTokensIndex } from '@/routes/setting/api-tokens';
import { index as domainsIndex } from '@/routes/setting/domains';
import { index as mcpIndex } from '@/routes/setting/mcp';
import { index as tagsIndex } from '@/routes/setting/tags';
import { index as teamMembersIndex } from '@/routes/setting/team-members';
import { type NavItem } from '@/types';

const page = usePage();
const auth = computed(() => page.props.auth);

const navItems: NavItem[] = [
    {
        title: 'Analytics',
        href: analyticsIndex().url,
        icon: IconChartBar,
    },
    {
        title: 'Links',
        href: linksIndex().url,
        icon: IconClick,
    },
    {
        title: 'Events',
        href: eventsIndex().url,
        icon: IconCalendarEvent,
    },
];

// Workspace-level configuration is a place you go to, not something buried two
// clicks deep: it lives in the sidebar. What is personal to the account —
// profile, sign-in — stays behind the user menu at the bottom.
const workspaceNavItems: NavItem[] = [
    {
        title: 'Domains',
        href: domainsIndex().url,
        icon: IconWorld,
    },
    {
        title: 'Tags',
        href: tagsIndex().url,
        icon: IconTag,
    },
    {
        title: 'Members',
        href: teamMembersIndex().url,
        icon: IconUsers,
    },
    {
        title: 'API Tokens',
        href: apiTokensIndex().url,
        icon: IconKey,
    },
    {
        title: 'MCP',
        href: mcpIndex().url,
        icon: IconPlugConnected,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <SidebarMenuButton
                                size="lg"
                                class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                            >
                                <Avatar
                                    :src="
                                        auth.user?.current_workspace?.logo_url
                                    "
                                    :name="
                                        auth.user?.current_workspace?.name ??
                                        '?'
                                    "
                                    class="h-8 w-8 shrink-0 rounded-lg"
                                    fallback-class="bg-violet-100 font-bold text-violet-700"
                                />
                                <div
                                    class="grid flex-1 text-left text-sm leading-tight"
                                >
                                    <span class="truncate font-semibold">
                                        {{
                                            auth.user?.current_workspace
                                                ?.name ?? 'Select workspace'
                                        }}
                                    </span>
                                </div>
                                <IconChevronRight class="ml-auto size-4" />
                            </SidebarMenuButton>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            class="w-[--reka-dropdown-menu-trigger-width] min-w-64 rounded-lg"
                            align="start"
                            side="right"
                            :side-offset="4"
                        >
                            <WorkspaceMenuContent />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="navItems" />
            <NavMain :items="workspaceNavItems" label="Workspace" />
        </SidebarContent>

    </Sidebar>
    <slot />
</template>
