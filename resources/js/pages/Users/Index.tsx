import { Link, useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { User, BreadcrumbItem } from '@/types';
import { EnhancedDataTable, type PaginatedData, type DataTableFilters } from '@/components/ui/enhanced-data-table';

interface Props {
  users: PaginatedData<User>;
  filters: DataTableFilters;
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'User Management',
    href: '/admin/users',
  },
];

export default function Users({ users, filters }: Props) {
  const { processing, delete: destroy } = useForm({});

  const columns = [
    {
      key: 'name' as const,
      label: 'Name',
      sortable: true,
    },
    {
      key: 'email' as const,
      label: 'Email',
      sortable: true,
    },
    {
      key: 'roles' as const,
      label: 'Roles',
      render: (user: User) => (
        <div className="flex flex-wrap gap-1">
          {user.roles.map((role) => (
            <span
              key={role.id}
              className="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/30"
            >
              {role.name}
            </span>
          ))}
        </div>
      ),
    },
    {
      key: 'created_at' as const,
      label: 'Created At',
      sortable: true,
      render: (user: User) => new Date(user.created_at).toLocaleDateString(),
    },
    {
      key: 'actions' as const,
      label: 'Actions',
      render: (user: User) => (
        <div className="flex flex-col sm:flex-row gap-2 sm:justify-end w-full">
          <Link href={route('admin.users.show', user.id)} className="w-full sm:w-auto">
            <Button variant="outline" size="sm" className="w-full sm:w-auto">
              View
            </Button>
          </Link>
          {!user.roles.some(role => role.name === 'super-admin') && (
            <>
              <Link href={route('admin.users.edit', user.id)} className="w-full sm:w-auto">
                <Button variant="outline" size="sm" className="w-full sm:w-auto">
                  Edit
                </Button>
              </Link>
              <Button
                variant="outline"
                size="sm"
                onClick={() => destroy(route('admin.users.destroy', user.id))}
                disabled={processing}
                className="w-full sm:w-auto text-red-600 hover:text-red-700 hover:border-red-700 dark:text-red-500 dark:hover:text-red-400 dark:hover:border-red-400"
              >
                Delete
              </Button>
            </>
          )}
        </div>
      ),
    },
  ] as const;

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="User Management" />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex flex-col gap-4">
          <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h1 className="text-2xl font-semibold sm:text-xl md:text-lg lg:text-base">User Management</h1>
            <div className="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center w-full sm:w-auto">
              <div className="flex-1 sm:flex-none">
                <Link href={route('admin.users.create')} className="block w-full">
                  <Button className="w-full">Create User</Button>
                </Link>
              </div>
            </div>
          </div>
        </div>

        <EnhancedDataTable<User>
          data={users}
          columns={columns}
          filters={filters}
          onBulkAction={(action, selectedUsers) => {
            if (action === 'delete') {
              if (confirm(`Are you sure you want to delete ${selectedUsers.length} users?`)) {
                selectedUsers.forEach(user => {
                  if (!user.roles.some(role => role.name === 'super-admin')) {
                    destroy(route('admin.users.destroy', user.id));
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