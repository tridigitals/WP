import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Tag, BreadcrumbItem } from '@/types';
import { Link } from '@inertiajs/react';

interface Props {
  tag: Tag;
}

export default function ShowTag({ tag }: Props) {
  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Dashboard',
      href: '/dashboard',
    },
    {
      title: 'Tag Management',
      href: '/admin/tags',
    },
    {
      title: tag.name,
      href: `/admin/tags/${tag.id}`,
    },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Tag: ${tag.name}`} />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 sm:p-2 md:p-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-semibold sm:text-xl md:text-lg">Tag Details</h1>
          <div className="flex gap-2">
            <Link href={route('admin.tags.edit', tag.id)}>
              <Button variant="outline">Edit Tag</Button>
            </Link>
            <Button
              variant="outline"
              onClick={() => window.history.back()}
            >
              Back
            </Button>
          </div>
        </div>

        <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
          <div className="px-4 py-5 sm:p-6">
            <h2 className="text-lg font-medium mb-4">Basic Information</h2>
            <dl className="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
              <div className="sm:col-span-1">
                <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Tag Name</dt>
                <dd className="mt-1 text-sm text-gray-900 dark:text-gray-200">{tag.name}</dd>
              </div>
              <div className="sm:col-span-1">
                <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Slug</dt>
                <dd className="mt-1 text-sm text-gray-900 dark:text-gray-200">{tag.slug}</dd>
              </div>
              <div className="sm:col-span-1">
                <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                <dd className="mt-1 text-sm text-gray-900 dark:text-gray-200">{tag.description}</dd>
              </div>
              <div className="sm:col-span-1">
                <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                <dd className="mt-1 text-sm text-gray-900 dark:text-gray-200">
                  {new Date(tag.created_at).toLocaleDateString()}
                </dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}