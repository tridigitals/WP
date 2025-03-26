import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import { useState, useEffect } from 'react';

function NavMenuItem({ item, isChild = false }: { item: NavItem; isChild?: boolean }) {
    const page = usePage();
    const [isOpen, setIsOpen] = useState(false);
    const hasChildren = item.children && item.children.length > 0;

    // Check if current item or any of its children are active
    const isActive = item.href === page.url || (
        hasChildren && item.children?.some(child => child.href === page.url)
    );

    // If active and has children, auto-expand
    useEffect(() => {
        if (isActive && hasChildren) {
            setIsOpen(true);
        }
    }, [isActive, hasChildren]);

    const content = hasChildren ? (
        <div className="w-full">
            <SidebarMenuButton
                className={`w-full transition-colors ${isActive ? 'text-primary bg-accent' : ''}`}
                isActive={isActive}
                tooltip={{ children: item.title }}
                onClick={() => setIsOpen(!isOpen)}
            >
                <div className="flex items-center justify-between w-full">
                    <div className="flex items-center gap-2">
                        {item.icon && <item.icon className={isActive ? 'text-primary' : ''} />}
                        <span>{item.title}</span>
                    </div>
                    <ChevronDown
                        className={`transition-transform duration-200 ${isOpen ? 'rotate-180' : ''} ${isActive ? 'text-primary' : ''}`}
                        size={16}
                    />
                </div>
            </SidebarMenuButton>
            {isOpen && hasChildren && (
                <div className="ml-4 mt-1 space-y-1 border-l border-border pl-4">
                    {item.children?.map((child) => (
                        <div key={child.title} className="py-1">
                            <SidebarMenuButton
                                asChild
                                isActive={child.href === page.url}
                                tooltip={{ children: child.title }}
                                className={`transition-colors ${child.href === page.url ? 'text-primary bg-accent' : ''}`}
                            >
                                <Link href={child.href} prefetch>
                                    <div className="flex items-center gap-2">
                                        {child.icon && <child.icon className={child.href === page.url ? 'text-primary' : ''} />}
                                        <span>{child.title}</span>
                                    </div>
                                </Link>
                            </SidebarMenuButton>
                        </div>
                    ))}
                </div>
            )}
        </div>
    ) : (
        <SidebarMenuButton
            asChild
            isActive={isActive}
            tooltip={{ children: item.title }}
            className={`transition-colors ${isActive ? 'text-primary bg-accent' : ''} ${isChild ? 'pl-2' : ''}`}
        >
            <Link href={item.href} prefetch>
                <div className="flex items-center gap-2">
                    {item.icon && <item.icon className={isActive ? 'text-primary' : ''} />}
                    <span>{item.title}</span>
                </div>
            </Link>
        </SidebarMenuButton>
    );

    return (
        <SidebarMenuItem>
            {content}
        </SidebarMenuItem>
    );
}

export function NavMain({ items = [] }: { items: NavItem[] }) {
    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>Platform</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => (
                    <NavMenuItem key={item.title} item={item} />
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
