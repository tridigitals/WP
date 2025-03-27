import { Link, useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Role, BreadcrumbItem, Permission } from '@/types';
import { EnhancedDataTable, type PaginatedData, type DataTableFilters } from '@/components/ui/enhanced-data-table';

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
  const { processing, delete: destroy } = useForm({});

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
        <div className="overflow-hidden" style={{ maxWidth: '300px', whiteSpace: 'pre-wrap', fontSize: 'small' }}>
          {role.permissions.map(p => p.name).join(', ')}
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
        <div className="flex flex-col sm:flex-row gap-2 sm:justify-end w-full">
          <Link href={route('admin.roles.show', role.id)} className="w-full sm:w-auto">
            <Button variant="outline" size="sm" className="w-full sm:w-auto">
              View
            </Button>
          </Link>
          <Link href={route('admin.roles.edit', role.id)} className="w-full sm:w-auto">
            <Button variant="outline" size="sm" className="w-full sm:w-auto">
              Edit
            </Button>
          </Link>
          <Button
            variant="outline"
            size="sm"
            onClick={() => handleDelete(role)}
            disabled={processing || role.name === 'super-admin'}
            className="w-full sm:w-auto text-red-600 hover:text-red-700 hover:border-red-700 dark:text-red-500 dark:hover:text-red-400 dark:hover:border-red-400"
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
        <div className="flex flex-col gap-4">
          <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h1 className="text-2xl font-semibold sm:text-xl md:text-lg lg:text-base">Role Management</h1>
            <div className="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center w-full sm:w-auto">
              <div className="flex-1 sm:flex-none">
                <Link href={route('admin.roles.create')} className="block w-full">
                  <Button className="w-full">Create Role</Button>
                </Link>
              </div>
            </div>
          </div>
        </div>

        <EnhancedDataTable<Role>
          data={roles}
          columns={columns}
          filters={filters}
          onBulkAction={(action, selectedRoles) => {
            if (action === 'delete') {
              if (confirm(`Are you sure you want to delete ${selectedRoles.length} roles?`)) {
                selectedRoles.forEach(role => {
                  if (role.name !== 'super-admin') {
                    destroy(route('admin.roles.destroy', role.id));
                  }
                });
              }
            }
          }}
        />
      </div>
    </AppLayout>
  );
}