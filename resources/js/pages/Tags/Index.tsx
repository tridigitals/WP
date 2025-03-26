import { Link, useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Tag, BreadcrumbItem } from '@/types';
import { DataTable, type PaginatedData, type DataTableFilters } from '@/components/ui/data-table';

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
  const { delete: destroy, processing } = useForm({});

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
        <div className="flex justify-end gap-2">
          <Link href={route('admin.tags.show', tag.id)}>
            <Button variant="outline" size="sm">
              View
            </Button>
          </Link>
          <Link href={route('admin.tags.edit', tag.id)}>
            <Button variant="outline" size="sm">
              Edit
            </Button>
          </Link>
          <Button
            variant="outline"
            size="sm"
            onClick={() => handleDelete(tag)}
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
      <Head title="Tag Management" />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-semibold">Tag Management</h1>
          <Link href={route('admin.tags.create')}>
            <Button>Create Tag</Button>
          </Link>
        </div>

        <DataTable<Tag>
          data={tags}
          columns={columns}
          filters={filters}
        />
      </div>
    </AppLayout>
  );
}
