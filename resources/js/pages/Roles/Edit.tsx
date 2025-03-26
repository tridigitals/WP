import { useForm } from '@inertiajs/react';
import AdminLayout from '@/layouts/AdminLayout';
import { Button } from '@/components/ui/button';
import { Permission, Role } from '@/types';

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

  return (
    <AdminLayout title="Edit Role">
      <div className="container py-12">
        <div className="max-w-3xl mx-auto">
          <h1 className="text-2xl font-semibold mb-6">Edit Role: {role.name}</h1>

          <form onSubmit={handleSubmit} className="space-y-6">
            <div>
              <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                Role Name
              </label>
              <input
                type="text"
                id="name"
                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value={data.name}
                onChange={e => setData('name', e.target.value)}
                disabled={role.name === 'super-admin'}
              />
              {errors.name && (
                <p className="mt-1 text-sm text-red-600">{errors.name}</p>
              )}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Permissions
              </label>
              <div className="space-y-2">
                {permissions.map((permission) => (
                  <label
                    key={permission.id}
                    className="inline-flex items-center mr-4"
                  >
                    <input
                      type="checkbox"
                      className="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      checked={data.permissions.includes(permission.id)}
                      onChange={(e) => {
                        const permissions = e.target.checked
                          ? [...data.permissions, permission.id]
                          : data.permissions.filter((id) => id !== permission.id);
                        setData('permissions', permissions);
                      }}
                      disabled={role.name === 'super-admin'}
                    />
                    <span className="ml-2 text-sm text-gray-600">
                      {permission.name}
                    </span>
                  </label>
                ))}
              </div>
              {errors.permissions && (
                <p className="mt-1 text-sm text-red-600">{errors.permissions}</p>
              )}
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
      </div>
    </AdminLayout>
  );
}