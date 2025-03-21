import { Category } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import CategoryForm from './CategoryForm';

interface Props {
  categories: Category[];
}

const breadcrumbs = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Categories',
    href: route('admin.categories.index'),
  },
  {
    title: 'Create Category',
    href: route('admin.categories.create'),
  },
];

export default function Create({ categories }: Props) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Create Category" />
      <div className="flex h-full flex-1 flex-col gap-4 p-4">
        <div className="flex justify-between items-center">
          <h2 className="text-3xl font-bold tracking-tight">Create Category</h2>
        </div>

        <div className="max-w-2xl">
          <CategoryForm
            categories={categories}
            action={route('admin.categories.store')}
          />
        </div>
      </div>
    </AppLayout>
  );
}