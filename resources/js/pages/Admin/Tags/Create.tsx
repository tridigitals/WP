import { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import TagForm from './TagForm';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Tags',
    href: route('admin.tags.index'),
  },
  {
    title: 'Create Tag',
    href: route('admin.tags.create'),
  },
];

export default function Create() {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Create Tag" />
      <div className="flex h-full flex-1 flex-col gap-4 p-4">
        <div className="flex justify-between items-center">
          <h2 className="text-3xl font-bold tracking-tight">Create Tag</h2>
        </div>

        <div className="max-w-2xl">
          <TagForm
            action={route('admin.tags.store')}
          />
        </div>
      </div>
    </AppLayout>
  );
}