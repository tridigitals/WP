import { Link } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { User, BreadcrumbItem } from '@/types';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

interface Props {
  user: User;
}

const socialMediaPlatforms = [
  {
    name: 'twitter',
    label: 'Twitter',
    icon: 'Twitter',
  },
  {
    name: 'linkedin',
    label: 'LinkedIn',
    icon: 'LinkedIn',
  },
  {
    name: 'github',
    label: 'GitHub',
    icon: 'GitHub',
  },
];

export default function ShowUser({ user }: Props) {
  const isUserSuperAdmin = user.roles.some(role => role.name === 'super-admin');

  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Dashboard',
      href: '/dashboard',
    },
    {
      title: 'User Management',
      href: '/admin/users',
    },
    {
      title: user.name,
      href: `/admin/users/${user.id}`,
    },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`User: ${user.name}`} />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-semibold">User Details</h1>
          <div className="flex gap-2">
            {!isUserSuperAdmin && (
              <Link href={route('admin.users.edit', user.id)}>
                <Button variant="outline">Edit User</Button>
              </Link>
            )}
            <Button
              variant="outline"
              onClick={() => window.history.back()}
            >
              Back
            </Button>
          </div>
        </div>

        {/* Basic Information */}
        <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
          <div className="px-4 py-5 sm:p-6">
            <h2 className="text-lg font-medium mb-4">Basic Information</h2>
            <dl className="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
              <div className="sm:col-span-1">
                <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                <dd className="mt-1 text-sm text-gray-900 dark:text-gray-200">{user.name}</dd>
              </div>
              <div className="sm:col-span-1">
                <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                <dd className="mt-1 text-sm text-gray-900 dark:text-gray-200">{user.email}</dd>
              </div>
              <div className="sm:col-span-2">
                <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Bio</dt>
                <dd className="mt-1 text-sm text-gray-900 dark:text-gray-200">{user.bio || 'No bio provided'}</dd>
              </div>
              <div className="sm:col-span-1">
                <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Website</dt>
                <dd className="mt-1 text-sm text-gray-900 dark:text-gray-200">
                  {user.website ? (
                    <a
                      href={user.website}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-blue-600 hover:underline dark:text-blue-400"
                    >
                      {user.website}
                    </a>
                  ) : (
                    'No website provided'
                  )}
                </dd>
              </div>
              <div className="sm:col-span-1">
                <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                <dd className="mt-1 text-sm text-gray-900 dark:text-gray-200">
                  {new Date(user.created_at).toLocaleDateString()}
                </dd>
              </div>
            </dl>
          </div>
        </div>

        {/* Social Media Links */}
        {Object.keys(user.social_media_links || {}).length > 0 && (
          <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
            <div className="px-4 py-5 sm:p-6">
              <h2 className="text-lg font-medium mb-4">Social Media Links</h2>
              <div className="grid gap-4 sm:grid-cols-3">
                {socialMediaPlatforms.map((platform) => {
                  const url = user.social_media_links?.[platform.name];
                  if (!url) return null;
                  
                  return (
                    <div key={platform.name} className="flex flex-col">
                      <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {platform.label}
                      </dt>
                      <dd className="mt-1 text-sm">
                        <a
                          href={url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="text-blue-600 hover:underline dark:text-blue-400"
                        >
                          {url}
                        </a>
                      </dd>
                    </div>
                  );
                })}
              </div>
            </div>
          </div>
        )}

        {/* Roles */}
        <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
          <div className="px-4 py-5 sm:p-6">
            <h2 className="text-lg font-medium mb-4">Assigned Roles</h2>
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Role Name</TableHead>
                    <TableHead>Permissions</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {user.roles.map((role) => (
                    <TableRow key={role.id}>
                      <TableCell>{role.name}</TableCell>
                      <TableCell>
                        <div className="flex flex-wrap gap-1">
                          {role.permissions?.map((permission) => (
                            <span
                              key={permission.id}
                              className="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/30"
                            >
                              {permission.name}
                            </span>
                          ))}
                        </div>
                      </TableCell>
                      <TableCell>
                        <Link href={route('admin.roles.show', role.id)}>
                          <Button variant="outline" size="sm">
                            View Role
                          </Button>
                        </Link>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}