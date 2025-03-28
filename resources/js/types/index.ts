import { LucideIcon } from "lucide-react";
import { FormDataConvertible } from "@inertiajs/core";

export interface User {
  id: number;
  name: string;
  email: string;
  permissions: string[];
  roles: { id: number; name: string }[];
  created_at: string;
  updated_at: string;
}

export interface NavItem {
  title: string;
  href: string;
  icon?: LucideIcon;
  children?: NavItem[];
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface PaginationData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export interface Tag {
    id: number;
    name: string;
    slug: string;
    description?: string;
    created_at: string;
    updated_at: string;
}

export interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    parent_id?: number;
    parent?: Category | null;
    created_at: string;
    updated_at: string;
}

export interface Permission {
    id: number;
    name: string;
    created_at: string;
    updated_at: string;
}

export interface Role {
    id: number;
    name: string;
    permissions: Permission[];
    created_at: string;
    updated_at: string;
}

export interface PostMetaInput {
    meta_title?: string;
    meta_description?: string;
    focus_keyphrase?: string;
    og_title?: string;
    og_description?: string;
    og_image?: string;
    [key: string]: FormDataConvertible | undefined;
}

export interface PostMeta {
    id: number;
    post_id: number;
    meta_key: string;
    meta_value: string;
    created_at: string;
    updated_at: string;
}

export interface Post {
    id: number;
    title: string;
    slug: string;
    content: string;
    excerpt?: string;
    status: 'draft' | 'published' | 'scheduled';
    featured_image?: string;
    author_id: number;
    published_at?: string;
    created_at: string;
    updated_at: string;
    deleted_at?: string;
    author?: {
        id: number;
        name: string;
    };
    categories?: Category[];
    tags?: Tag[];
    postMeta?: PostMeta[];
    meta?: PostMetaInput;
    comments_count?: number;
}