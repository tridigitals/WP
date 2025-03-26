import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { useAuth } from '@/hooks/useAuth';
import { Button } from '@/components/ui/button';

interface Props {
  title: string;
  children: React.ReactNode;
}

export default function AdminLayout({ title, children }: Props) {
  const { user } = useAuth();

  const navigation = [
    { name: 'Dashboard', href: route('dashboard') },
    { name: 'Users', href: route('admin.users.index') },
    { name: 'Roles', href: route('admin.roles.index') },
    { name: 'Permissions', href: route('admin.permissions.index') },
  ];

  return (
    <>
      <Head title={title} />
      
      <div className="min-h-screen bg-gray-100">
        {/* Navigation */}
        <nav className="bg-white shadow">
          <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div className="flex h-16 justify-between">
              <div className="flex">
                <div className="flex flex-shrink-0 items-center">
                  <Link href={route('dashboard')}>
                    <img
                      className="h-8 w-auto"
                      src="/logo.svg"
                      alt="Your Company"
                    />
                  </Link>
                </div>
                <div className="hidden sm:ml-6 sm:flex sm:space-x-8">
                  {navigation.map((item) => (
                    <Link
                      key={item.name}
                      href={item.href}
                      className={cn(
                        'inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium',
                        route().current(item.href)
                          ? 'border-indigo-500 text-gray-900'
                          : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                      )}
                    >
                      {item.name}
                    </Link>
                  ))}
                </div>
              </div>

              <div className="flex items-center">
                <div className="hidden sm:ml-6 sm:flex sm:items-center">
                  <div className="relative ml-3">
                    <div className="flex items-center gap-4">
                      <span className="text-sm text-gray-500">
                        {user?.name}
                      </span>
                      <Link href={route('logout')} method="post" as="button">
                        <Button variant="outline" size="sm">
                          Logout
                        </Button>
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </nav>

        {/* Page Content */}
        <main>
          {children}
        </main>
      </div>
    </>
  );
}