import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { User } from '@/types';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function hasPermission(user: User | undefined, permissionName: string): boolean {
  if (!user) {
    return false;
  }

  if (user.roles?.some(role => role.name === 'super-admin' || role.name === 'admin')) {
    return true;
  }

  if (user.permissions?.some(permission => permission.name === permissionName)) {
    return true;
  }

  if (user.roles?.some(role => role.permissions?.some(permission => permission.name === permissionName))) {
    return true;
  }

  return false;
}
