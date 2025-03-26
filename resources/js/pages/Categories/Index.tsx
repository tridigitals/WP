import { Link, useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Category, BreadcrumbItem } from '@/types';
import { DataTable, type PaginatedData, type DataTableFilters } from '@/components/ui/data-table';

interface Props {
  categories: PaginatedData<Category>;
  filters: DataTableFilters;
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Category Management',
    href: '/admin/categories',
  },
];

export default function Categories({ categories, filters }: Props) {
  const { delete: destroy, processing } = useForm({});

  const handleDelete = (category: Category) => {
    if (confirm(`Are you sure you want to delete the category "${category.name}"?`)) {
      destroy(route('admin.categories.destroy', category.id));
    }
  };

  const columns = [
    {
      key: 'name' as const,
      label: 'Name',
      sortable: true,
    },
    {
      key: 'slug' as const,
      label: 'Slug',
      sortable: true,
    },
    {
      key: 'created_at' as const,
      label: 'Created At',
      sortable: true,
      render: (category: Category) => new Date(category.created_at).toLocaleDateString(),
    },
    {
      key: 'actions' as const,
      label: 'Actions',
      render: (category: Category) => (
        <div className="flex justify-end gap-2">
          <Link href={route('admin.categories.show', category.id)}>
            <Button variant="outline" size="sm">
              View
            </Button>
          </Link>
          <Link href={route('admin.categories.edit', category.id)}>
            <Button variant="outline" size="sm">
              Edit
            </Button>
          </Link>
          <Button
            variant="outline"
            size="sm"
            onClick={() => handleDelete(category)}
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
      <Head title="Category Management" />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-semibold">Category Management</h1>
          <Link href={route('admin.categories.create')}>
            <Button>Create Category</Button>
          </Link>
        </div>

        <DataTable<Category>
          data={categories}
          columns={columns}
          filters={filters}
        />
      </div>
    </AppLayout>
  );
}