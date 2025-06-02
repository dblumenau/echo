<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Languages, Shield, TestTube, Gamepad2 } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage();
const isAuthenticated = computed(() => page.props.auth?.user);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Welcome',
            href: '/welcome',
            icon: LayoutGrid,
        },
    ];

    // Add Dashboard for authenticated users
    if (isAuthenticated.value) {
        items.push({
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        });
    }

    // For non-authenticated users, show sample items
    if (!isAuthenticated.value) {
        items.push({
            title: 'Mini Games',
            href: '#',
            icon: Gamepad2,
            description: 'Coming soon - Login to access',
        });
        return items;
    }

    // Authenticated user content below
    const user = page.props.auth.user;
    
    // Add Vocabulary section
    const vocabularyItems: NavItem[] = [];
    
    // All users can view word pairs
    vocabularyItems.push({
        title: 'View Word Pairs',
        href: route('vocabulary.word-pairs.index'),
    });

    // Only approved and superadmin users can add word pairs
    if (['approved', 'superadmin'].includes(user.user_type)) {
        vocabularyItems.push({
            title: 'Add Word Pair',
            href: route('vocabulary.word-pairs.create'),
        });
    }

    // Only superadmins can manage categories
    if (user.user_type === 'superadmin') {
        vocabularyItems.push({
            title: 'Manage Categories',
            href: route('vocabulary.categories.index'),
        });
    }

    items.push({
        title: 'Vocabulary',
        icon: Languages,
        items: vocabularyItems,
    });

    // Add Mini-games section
    const gamesItems: NavItem[] = [
        {
            title: 'Match Madness',
            href: route('games.match-madness'),
        },
    ];

    items.push({
        title: 'Mini-games',
        icon: Gamepad2,
        items: gamesItems,
    });

    // Add Admin section for superadmins
    if (user.user_type === 'superadmin') {
        const adminItems: NavItem[] = [
            {
                title: 'Manage Users',
                href: route('admin.users.index'),
            },
        ];

        items.push({
            title: 'Admin',
            icon: Shield,
            items: adminItems,
        });
    }

    // Add Error Testing section for superadmins (development/testing)
    if (user.user_type === 'superadmin') {
        items.push({
            title: 'Error Testing',
            href: route('error-testing.index'),
            icon: TestTube,
        });
    }

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter v-if="isAuthenticated">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
