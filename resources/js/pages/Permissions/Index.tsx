import { Link, useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Permission, BreadcrumbItem } from '@/types';
import { EnhancedDataTable, type PaginatedData, type DataTableFilters } from '@/components/ui/enhanced-data-table';

interface Props {
  permissions: PaginatedData<Permission>;
  filters: DataTableFilters;
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Permission Management',
    href: '/admin/permissions',
  },
];

export default function Permissions({ permissions, filters }: Props) {
  const { processing, delete: destroy } = useForm({});

  const handleDelete = (permission: Permission) => {
    if (confirm(`Are you sure you want to delete the permission "${permission.name}"?`)) {
      destroy(route('admin.permissions.destroy', permission.id));
    }
  };

  const columns = [
    {
      key: 'name' as const,
      label: 'Name',
      sortable: true,
    },
    {
      key: 'created_at' as const,
      label: 'Created At',
      sortable: true,
      render: (permission: Permission) => new Date(permission.created_at).toLocaleDateString(),
    },
    {
      key: 'actions' as const,
      label: 'Actions',
      render: (permission: Permission) => (
        <div className="flex justify-end gap-2">
          <Link href={route('admin.permissions.show', permission.id)}>
            <Button variant="outline" size="sm">
              View
            </Button>
          </Link>
          <Link href={route('admin.permissions.edit', permission.id)}>
            <Button variant="outline" size="sm">
              Edit
            </Button>
          </Link>
          <Button
            variant="outline"
            size="sm"
            onClick={() => handleDelete(permission)}
            disabled={processing}
            className="text-red-600 hover:text-red-700 hover:border-red-700 dark:text-red-500 dark:hover:text-red-400 dark:hover:border-red-400"
          >
            Delete
          </Button>
        </div>
      ),
    },
  ] as const;

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Permission Management" />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-semibold sm:text-xl md:text-lg lg:text-base">Permission Management</h1>
          <div className="flex items-center space-x-2">
            <Link href={route('admin.permissions.create')}>
              <Button>Create Permission</Button>
            </Link>
          </div>
        </div>

        <EnhancedDataTable<Permission>
          data={permissions}
          columns={columns}
          filters={filters}
          onBulkAction={(action, selectedPermissions) => {
            if (action === 'delete') {
              if (confirm(`Are you sure you want to delete ${selectedPermissions.length} permissions?`)) {
                selectedPermissions.forEach(permission => {
                  destroy(route('admin.permissions.destroy', permission.id));
                });
              }
            }
          }}
        />
      </div>
    </AppLayout>
  );
}