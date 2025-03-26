import { Link } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Role, Permission, BreadcrumbItem } from '@/types';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

interface Props {
  role: Role & { permissions: Permission[] };
}

export default function ShowRole({ role }: Props) {
  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Dashboard',
      href: '/dashboard',
    },
    {
      title: 'Role Management',
      href: '/admin/roles',
    },
    {
      title: role.name,
      href: `/admin/roles/${role.id}`,
    },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Role: ${role.name}`} />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-semibold">Role Details</h1>
          <div className="flex gap-2">
            {role.name !== 'super-admin' && (
              <Link href={route('admin.roles.edit', role.id)}>
                <Button variant="outline">Edit Role</Button>
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

        <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
          <div className="px-4 py-5 sm:p-6">
            <h2 className="text-lg font-medium mb-4">Basic Information</h2>
            <dl className="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
              <div className="sm:col-span-1">
                <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Role Name</dt>
                <dd className="mt-1 text-sm text-gray-900 dark:text-gray-200">{role.name}</dd>
              </div>
              <div className="sm:col-span-1">
                <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                <dd className="mt-1 text-sm text-gray-900 dark:text-gray-200">
                  {new Date(role.created_at).toLocaleDateString()}
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
          <div className="px-4 py-5 sm:p-6">
            <h2 className="text-lg font-medium mb-4">Role Permissions</h2>
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Permission Name</TableHead>
                    <TableHead>Created At</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {role.permissions?.map((permission) => (
                    <TableRow key={permission.id}>
                      <TableCell>
                        <span className="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/30">
                          {permission.name}
                        </span>
                      </TableCell>
                      <TableCell>{new Date(permission.created_at).toLocaleDateString()}</TableCell>
                    </TableRow>
                  ))}
                  {!role.permissions?.length && (
                    <TableRow>
                      <TableCell colSpan={2} className="text-center text-gray-500 dark:text-gray-400">
                        No permissions assigned to this role
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