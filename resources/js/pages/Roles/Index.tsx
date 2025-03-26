import { Link, useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Role, BreadcrumbItem } from '@/types';
import { DataTable, type PaginatedData, type DataTableFilters } from '@/components/ui/data-table';

interface Props {
  roles: PaginatedData<Role>;
  filters: DataTableFilters;
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Role Management',
    href: '/admin/roles',
  },
];

export default function Roles({ roles, filters }: Props) {
  const { delete: destroy, processing } = useForm({});

  const handleDelete = (role: Role) => {
    if (role.name === 'super-admin') {
      alert('Cannot delete super-admin role.');
      return;
    }
    
    if (confirm(`Are you sure you want to delete the role "${role.name}"?`)) {
      destroy(route('admin.roles.destroy', role.id));
    }
  };

  const columns = [
    {
      key: 'name' as const,
      label: 'Name',
      sortable: true,
    },
    {
      key: 'permissions' as const,
      label: 'Permissions',
      render: (role: Role) => (
        <div className="max-w-md overflow-hidden">
          <p className="truncate">
            {role.permissions.map(p => p.name).join(', ')}
          </p>
        </div>
      ),
    },
    {
      key: 'created_at' as const,
      label: 'Created At',
      sortable: true,
      render: (role: Role) => new Date(role.created_at).toLocaleDateString(),
    },
    {
      key: 'actions' as const,
      label: 'Actions',
      render: (role: Role) => (
        <div className="flex justify-end gap-2">
          <Link href={route('admin.roles.show', role.id)}>
            <Button variant="outline" size="sm">
              View
            </Button>
          </Link>
          <Link href={route('admin.roles.edit', role.id)}>
            <Button variant="outline" size="sm">
              Edit
            </Button>
          </Link>
          <Button
            variant="outline"
            size="sm"
            onClick={() => handleDelete(role)}
            disabled={processing || role.name === 'super-admin'}
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
      <Head title="Role Management" />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-semibold">Role Management</h1>
          <Link href={route('admin.roles.create')}>
            <Button>Create Role</Button>
          </Link>
        </div>

        <DataTable<Role>
          data={roles}
          columns={columns}
          filters={filters}
        />
      </div>
    </AppLayout>
  );
}