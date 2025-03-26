import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, User } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, Users, Shield, Key, Tag as TagIcon, Settings } from 'lucide-react';
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

    // Add User Management section with nested items
    if (hasPermission(user, 'view users')) {
        const userManagementChildren: NavItem[] = [];
        
        if (hasPermission(user, 'view users')) {
            userManagementChildren.push({
                title: 'User Management',
                href: '/admin/users',
                icon: Users,
            });
        }
        // Add Role Management if user has permission
        if (hasPermission(user, 'view roles')) {
            userManagementChildren.push({
                title: 'Role Management',
                href: '/admin/roles',
                icon: Shield,
            });
        }

        // Add Permission Management if user has permission
        if (hasPermission(user, 'view permissions')) {
            userManagementChildren.push({
                title: 'Permission Management',
                href: '/admin/permissions',
                icon: Key,
            });
        }


        // Add User Management section with children
        mainNavItems.push({
            title: 'User Management',
            href: '#',
            icon: Users,
            children: userManagementChildren,
        });
    }

    // Add Category Management if user has permission
    if (hasPermission(user, 'view categories')) {
        mainNavItems.push({
            title: 'Category Management',
            href: '/admin/categories',
            icon: Settings,
        });
    }

    // Add Tag Management as a separate section
    if (hasPermission(user, 'view tags')) {
        mainNavItems.push({
            title: 'Tag Management',
            href: '/admin/tags',
            icon: TagIcon,
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
