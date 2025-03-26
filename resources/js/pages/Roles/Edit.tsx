import { useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Permission, Role, BreadcrumbItem } from '@/types';

interface Props {
  role: Role & { permissions: Permission[] };
  permissions: Permission[];
}

export default function EditRole({ role, permissions }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    name: role.name,
    permissions: role.permissions.map(p => p.id),
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.roles.update', role.id));
  };

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
      title: `Edit ${role.name}`,
      href: `/admin/roles/${role.id}/edit`,
    },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Edit Role: ${role.name}`} />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-semibold">Edit Role: {role.name}</h1>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          {/* Basic Information */}
          <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
            <div className="px-4 py-5 sm:p-6">
              <h2 className="text-lg font-medium mb-4">Basic Information</h2>
              <div className="space-y-4">
                <div>
                  <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Role Name
                  </label>
                  <input
                    type="text"
                    id="name"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                    value={data.name}
                    onChange={e => setData('name', e.target.value)}
                    disabled={role.name === 'super-admin'}
                  />
                  {errors.name && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.name}</p>
                  )}
                </div>
              </div>
            </div>
          </div>

          {/* Permissions */}
          <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
            <div className="px-4 py-5 sm:p-6">
              <h2 className="text-lg font-medium mb-4">Permissions</h2>
              <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
                {permissions.map((permission) => (
                  <label key={permission.id} className="inline-flex items-center">
                    <input
                      type="checkbox"
                      className="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600"
                      checked={data.permissions.includes(permission.id)}
                      onChange={(e) => {
                        const permissions = e.target.checked
                          ? [...data.permissions, permission.id]
                          : data.permissions.filter((id) => id !== permission.id);
                        setData('permissions', permissions);
                      }}
                      disabled={role.name === 'super-admin'}
                    />
                    <span className="ml-2 text-sm text-gray-600 dark:text-gray-300">
                      {permission.name}
                    </span>
                  </label>
                ))}
              </div>
              {errors.permissions && (
                <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.permissions}</p>
              )}
            </div>
          </div>

          <div className="flex justify-end gap-4">
            <Button
              type="button"
              variant="outline"
              onClick={() => window.history.back()}
            >
              Cancel
            </Button>
            <Button 
              type="submit" 
              disabled={processing || role.name === 'super-admin'}
            >
              Update Role
            </Button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
}
