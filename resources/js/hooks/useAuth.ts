import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { User } from '@/types';

export function useAuth() {
  const { auth } = usePage<PageProps>().props;
  return {
    user: auth.user as User | undefined,
    isAuthenticated: !!auth.user,
  };
}