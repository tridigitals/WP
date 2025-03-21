import { Category } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import CategoryForm from './CategoryForm';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import { router } from '@inertiajs/react';

interface Props {
  category: Category;
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
    title: 'Edit Category',
    href: '#',
  },
];

export default function Edit({ category, categories }: Props) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Edit Category - ${category.name}`} />
      <div className="flex h-full flex-1 flex-col gap-4 p-4">
        <div className="flex justify-between items-center">
          <div className="flex items-center gap-4">
            <Button
              variant="ghost"
              size="icon"
              onClick={() => router.get(route('admin.categories.index'))}
            >
              <ArrowLeft className="h-4 w-4" />
            </Button>
            <h2 className="text-3xl font-bold tracking-tight">Edit Category</h2>
          </div>
        </div>

        <div className="max-w-2xl">
          <CategoryForm
            category={category}
            categories={categories}
            action={route('admin.categories.update', category.id)}
          />
        </div>
      </div>
    </AppLayout>
  );
}