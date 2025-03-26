import { useForm } from '@inertiajs/react';
import AdminLayout from '@/layouts/AdminLayout';
import { Button } from '@/components/ui/button';
import { Permission } from '@/types';

interface Props {
  permission: Permission;
}

export default function EditPermission({ permission }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    name: permission.name,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.permissions.update', permission.id));
  };

  return (
    <AdminLayout title="Edit Permission">
      <div className="container py-12">
        <div className="max-w-3xl mx-auto">
          <h1 className="text-2xl font-semibold mb-6">
            Edit Permission: {permission.name}
          </h1>

          <form onSubmit={handleSubmit} className="space-y-6">
            <div>
              <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                Permission Name
              </label>
              <input
                type="text"
                id="name"
                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value={data.name}
                onChange={e => setData('name', e.target.value)}
              />
              {errors.name && (
                <p className="mt-1 text-sm text-red-600">{errors.name}</p>
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
              <Button type="submit" disabled={processing}>
                Update Permission
              </Button>
            </div>
          </form>
        </div>
      </div>
    </AdminLayout>
  );
}