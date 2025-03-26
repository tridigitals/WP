import { Page } from '@inertiajs/core';
import { type LucideIcon } from 'lucide-react';

export interface BreadcrumbItem {
  title: string;
  href: string;
}

export interface NavItem {
  title: string;
  href: string;
  icon: LucideIcon;
}

export type PageProps = {
  auth: {
    user: User | null;
  };
  flash: {
    success?: string;
    error?: string;
  };
  [key: string]: unknown;
};

export interface User {
  id: number;
  name: string;
  email: string;
  bio: string | null;
  avatar: string | null;
  website: string | null;
  social_media_links: Record<string, string>;
  roles: Role[];
  permissions: Permission[];
  created_at: string;
  updated_at: string;
}

export interface Tag {
  id: number;
  name: string;
  slug: string;
  description: string | null;
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

export interface Permission {
  id: number;
  name: string;
  created_at: string;
  updated_at: string;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  created_at: string;
  updated_at: string;
}

export interface Column<T> {
  key: keyof T | 'actions';
  label: string;
  sortable?: boolean;
  render?: (item: T) => React.ReactNode;
}