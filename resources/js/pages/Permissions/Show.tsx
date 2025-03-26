import { Link } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Permission, Role, BreadcrumbItem } from '@/types';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

interface Props {
  permission: Permission & { roles: Role[] };
}

export default function ShowPermission({ permission }: Props) {
  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Dashboard',
      href: '/dashboard',
    },
    {
      title: 'Permission Management',
      href: '/admin/permissions',
    },
    {
      title: permission.name,
      href: `/admin/permissions/${permission.id}`,
    },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Permission: ${permission.name}`} />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-semibold">Permission Details</h1>
          <div className="flex gap-2">
            <Link href={route('admin.permissions.edit', permission.id)}>
              <Button variant="outline">Edit Permission</Button>
            </Link>
            <Button
              variant="outline"
              onClick={() => window.history.back()}
            >
              Back
            </Button>
          </div>
        </div>

        <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
          <div className="px-4 py-5 sm:p-6">
            <h2 className="text-lg font-medium mb-4">Basic Information</h2>
            <dl className="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
              <div className="sm:col-span-1">
                <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Permission Name</dt>
                <dd className="mt-1 text-sm text-gray-900 dark:text-gray-200">{permission.name}</dd>
              </div>
              <div className="sm:col-span-1">
                <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                <dd className="mt-1 text-sm text-gray-900 dark:text-gray-200">
                  {new Date(permission.created_at).toLocaleDateString()}
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
          <div className="px-4 py-5 sm:p-6">
            <h2 className="text-lg font-medium mb-4">Assigned to Roles</h2>
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Role Name</TableHead>
                    <TableHead>Created At</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {permission.roles?.map((role) => (
                    <TableRow key={role.id}>
                      <TableCell>
                        <span className="font-medium">{role.name}</span>
                      </TableCell>
                      <TableCell>{new Date(role.created_at).toLocaleDateString()}</TableCell>
                      <TableCell>
                        <Link href={route('admin.roles.show', role.id)}>
                          <Button variant="outline" size="sm">
                            View Role
                          </Button>
                        </Link>
                      </TableCell>
                    </TableRow>
                  ))}
                  {!permission.roles?.length && (
                    <TableRow>
                      <TableCell colSpan={3} className="text-center text-gray-500 dark:text-gray-400">
                        No roles are using this permission
                      </TableCell>
                    </TableRow>
                  )}
                </TableBody>
              </Table>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}