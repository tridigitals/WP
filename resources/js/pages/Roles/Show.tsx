import { Link } from '@inertiajs/react';
import AdminLayout from '@/layouts/AdminLayout';
import { Button } from '@/components/ui/button';
import { Permission, Role } from '@/types';
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
  return (
    <AdminLayout title={`Role: ${role.name}`}>
      <div className="container py-12">
        <div className="max-w-3xl mx-auto">
          <div className="flex justify-between items-center mb-6">
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

          <div className="bg-white shadow rounded-lg">
            <div className="px-4 py-5 sm:p-6">
              <dl className="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div className="sm:col-span-1">
                  <dt className="text-sm font-medium text-gray-500">Role Name</dt>
                  <dd className="mt-1 text-sm text-gray-900">{role.name}</dd>
                </div>
                <div className="sm:col-span-1">
                  <dt className="text-sm font-medium text-gray-500">Created At</dt>
                  <dd className="mt-1 text-sm text-gray-900">
                    {new Date(role.created_at).toLocaleDateString()}
                  </dd>
                </div>
                <div className="sm:col-span-2">
                  <dt className="text-sm font-medium text-gray-500 mb-2">Permissions</dt>
                  <dd className="mt-1">
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Permission Name</TableHead>
                          <TableHead>Created At</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {role.permissions.map((permission) => (
                          <TableRow key={permission.id}>
                            <TableCell>{permission.name}</TableCell>
                            <TableCell>
                              {new Date(permission.created_at).toLocaleDateString()}
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}