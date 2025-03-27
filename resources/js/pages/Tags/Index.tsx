import { Link, useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Tag, BreadcrumbItem } from '@/types';
import { EnhancedDataTable, type PaginatedData, type DataTableFilters } from '@/components/ui/enhanced-data-table'; // Changed import

interface Props {
  tags: PaginatedData<Tag>;
  filters: DataTableFilters;
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Tag Management',
    href: '/admin/tags',
  },
];

export default function Tags({ tags, filters }: Props) {
  const { processing, delete: destroy } = useForm({});

  const handleDelete = (tag: Tag) => {
    if (confirm(`Are you sure you want to delete the tag "${tag.name}"?`)) {
      destroy(route('admin.tags.destroy', tag.id));
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
      render: (tag: Tag) => tag.description || '-',
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
      render: (tag: Tag) => new Date(tag.created_at).toLocaleDateString(),
    },
    {
      key: 'actions' as const,
      label: 'Actions',
      render: (tag: Tag) => (
        <div className="flex flex-col sm:flex-row gap-2 sm:justify-end w-full">
          <Link href={route('admin.tags.show', tag.id)} className="w-full sm:w-auto">
            <Button variant="outline" size="sm" className="w-full sm:w-auto">
              View
            </Button>
          </Link>
          <Link href={route('admin.tags.edit', tag.id)} className="w-full sm:w-auto">
            <Button variant="outline" size="sm" className="w-full sm:w-auto">
              Edit
            </Button>
          </Link>
          <Button
            variant="outline"
            size="sm"
            onClick={() => handleDelete(tag)}
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
      <Head title="Tag Management" />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex flex-col gap-4">
          <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h1 className="text-2xl font-semibold sm:text-xl md:text-lg lg:text-base">Tag Management</h1>
            <div className="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center w-full sm:w-auto">
              <div className="flex-1 sm:flex-none">
                <Link href={route('admin.tags.create')} className="block w-full">
                  <Button className="w-full">Create Tag</Button>
                </Link>
              </div>
            </div>
          </div>
        </div>

        {/* Changed to EnhancedDataTable */}
        <EnhancedDataTable<Tag>
          data={tags}
          columns={columns}
          filters={filters}
          onBulkAction={(action, selectedTags) => {
            if (action === 'delete') {
              if (confirm(`Are you sure you want to delete ${selectedTags.length} tags?`)) {
                selectedTags.forEach(tag => {
                  destroy(route('admin.tags.destroy', tag.id));
                });
              }
            }
          }}
        />
      </div>
    </AppLayout>
  );
}
