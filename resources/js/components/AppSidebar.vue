<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    IconCalendarEvent,
    IconChartBar,
    IconChevronRight,
    IconClick,
    IconKey,
    IconLink,
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
import { formatLimit, formatNumber } from '@/lib/metrics';
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
const usage = computed(() => page.props.usage);
const usageMetrics = computed(() =>
    usage.value
        ? [
              { label: 'Links', icon: IconLink, ...usage.value.links },
              { label: 'Events', icon: IconClick, ...usage.value.events },
          ]
        : [],
);

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

        <div
            v-if="usage"
            class="px-3 py-3 group-data-[collapsible=icon]:hidden"
        >
            <span class="text-xs text-sidebar-foreground/60">Usage</span>

            <div class="mt-3 flex flex-col gap-3">
                <div v-for="metric in usageMetrics" :key="metric.label">
                    <div class="mb-1.5 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <component
                                :is="metric.icon"
                                class="size-3.5 text-sidebar-foreground/60"
                            />
                            <span
                                class="text-xs font-medium text-sidebar-foreground/60"
                                >{{ metric.label }}</span
                            >
                        </div>
                        <span
                            class="text-xs font-medium text-sidebar-foreground/60"
                            >{{ formatNumber(metric.used) }} /
                            {{ formatLimit(metric.limit) }}</span
                        >
                    </div>
                    <div
                        v-if="metric.limit !== null"
                        class="overflow-hidden rounded-full bg-sidebar-accent"
                    >
                        <div
                            class="h-1 rounded-full bg-primary"
                            :style="{ width: `${metric.percent}%` }"
                        />
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
    <slot />
</template>
