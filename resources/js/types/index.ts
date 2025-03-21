import { LucideIcon } from 'lucide-react';

export interface NavItem {
    title: string;
    href: string;
    icon: LucideIcon;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    parent_id: number | null;
    parent?: Category;
    children?: Category[];
    posts_count?: number;
    created_at: string;
    updated_at: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    created_at: string;
    updated_at: string;
}

export interface Tag {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    posts_count?: number;
    created_at: string;
    updated_at: string;
}

export interface Post {
    id: number;
    title: string;
    slug: string;
    content: string;
    excerpt: string | null;
    featured_image: string | null;
    status: 'draft' | 'published' | 'archived';
    published_at: string | null;
    author_id: number;
    meta: Record<string, any> | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    author: User;
    category_id: number | null;
    category?: Category;
    tags?: Tag[];
}