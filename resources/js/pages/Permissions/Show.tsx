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
  permission: Permission & { roles: Role[] };
}

export default function ShowPermission({ permission }: Props) {
  return (
    <AdminLayout title={`Permission: ${permission.name}`}>
      <div className="container py-12">
        <div className="max-w-3xl mx-auto">
          <div className="flex justify-between items-center mb-6">
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

          <div className="bg-white shadow rounded-lg">
            <div className="px-4 py-5 sm:p-6">
              <dl className="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div className="sm:col-span-1">
                  <dt className="text-sm font-medium text-gray-500">Permission Name</dt>
                  <dd className="mt-1 text-sm text-gray-900">{permission.name}</dd>
                </div>
                <div className="sm:col-span-1">
                  <dt className="text-sm font-medium text-gray-500">Created At</dt>
                  <dd className="mt-1 text-sm text-gray-900">
                    {new Date(permission.created_at).toLocaleDateString()}
                  </dd>
                </div>
                <div className="sm:col-span-2">
                  <dt className="text-sm font-medium text-gray-500 mb-2">Assigned to Roles</dt>
                  <dd className="mt-1">
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Role Name</TableHead>
                          <TableHead>Created At</TableHead>
                          <TableHead>Actions</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {permission.roles.map((role) => (
                          <TableRow key={role.id}>
                            <TableCell>{role.name}</TableCell>
                            <TableCell>
                              {new Date(role.created_at).toLocaleDateString()}
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
                        {permission.roles.length === 0 && (
                          <TableRow>
                            <TableCell colSpan={3} className="text-center text-gray-500">
                              No roles are using this permission
                            </TableCell>
                          </TableRow>
                        )}
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