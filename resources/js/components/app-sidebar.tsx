import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, User } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, Users, Shield, Key, Tag as TagIcon } from 'lucide-react';
import { ChevronDownIcon } from '@heroicons/react/20/solid'
import AppLogo from './app-logo';
import { useAuth } from '@/hooks/useAuth';
import { hasPermission } from '@/lib/utils';

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { user } = useAuth();

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
    ];

    if (hasPermission(user, 'view users')) {
        mainNavItems.push({
            title: 'User Management',
            href: '/admin/users',
            icon: Users,
        });
    }

    if (hasPermission(user, 'view roles')) {
        mainNavItems.push({
            title: 'Role Management',
            href: '/admin/roles',
            icon: Shield,
        });
    }

    if (hasPermission(user, 'view permissions')) {
        mainNavItems.push({
            title: 'Permission Management',
            href: '/admin/permissions',
            icon: Key,
        });
    }

    if (hasPermission(user, 'view tags')) {
        mainNavItems.push({
            title: 'Tag Management',
            href: '/admin/tags',
            icon: TagIcon,
        });
    }

    if (hasPermission(user, 'view categories')) {
        mainNavItems.push({
            title: 'Category Management',
            href: '/admin/categories',
            icon: ChevronDownIcon,
        });
    }

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
