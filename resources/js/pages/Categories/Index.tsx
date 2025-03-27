import { Link, useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Category, BreadcrumbItem } from '@/types';
import { EnhancedDataTable, type PaginatedData, type DataTableFilters } from '@/components/ui/enhanced-data-table';

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
  const { processing, delete: destroy } = useForm({});

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
      key: 'description' as const,
      label: 'Description',
      render: (category: Category) => category.description || '-',
    },
    {
      key: 'parent' as const,
      label: 'Parent Category',
      render: (category: Category) => category.parent?.name || '-',
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
        <div className="flex flex-col sm:flex-row gap-2 sm:justify-end w-full">
          <Link href={route('admin.categories.show', category.id)} className="w-full sm:w-auto">
            <Button variant="outline" size="sm" className="w-full sm:w-auto">
              View
            </Button>
          </Link>
          <Link href={route('admin.categories.edit', category.id)} className="w-full sm:w-auto">
            <Button variant="outline" size="sm" className="w-full sm:w-auto">
              Edit
            </Button>
          </Link>
          <Button
            variant="outline"
            size="sm"
            onClick={() => handleDelete(category)}
            disabled={processing}
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
      <Head title="Category Management" />
      <div className="max-w-full flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex flex-col gap-4">
          <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h1 className="text-2xl font-semibold sm:text-xl md:text-lg lg:text-base">Category Management</h1>
            <div className="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center w-full sm:w-auto">
              <div className="flex-1 sm:flex-none">
                <Link href={route('admin.categories.create')} className="block w-full">
                  <Button className="w-full">Create Category</Button>
                </Link>
              </div>
            </div>
          </div>
        </div>

        <div className="w-full overflow-hidden rounded-md border">
          <div className="overflow-x-auto">
            <EnhancedDataTable<Category>
              data={categories}
              columns={columns}
              filters={filters}
              onBulkAction={(action, selectedCategories) => {
                if (action === 'delete') {
                  if (confirm(`Are you sure you want to delete ${selectedCategories.length} categories?`)) {
                    selectedCategories.forEach(category => {
                      destroy(route('admin.categories.destroy', category.id));
                    });
                  }
                }
              }}
            />
          </div>
        </div>
      </div>
    </AppLayout>
  );
}