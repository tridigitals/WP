import PostForm from './PostForm';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { type BreadcrumbItem, type Category } from '@/types';

interface Props {
  categories: Category[];
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Posts',
    href: route('admin.posts.index'),
  },
  {
    title: 'Buat Post',
    href: route('admin.posts.create'),
  },
];

export default function Create({ categories }: Props) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Buat Post" />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex justify-between items-center">
          <h2 className="text-3xl font-bold tracking-tight">Buat Post Baru</h2>
        </div>

        <div className="max-w-2xl">
          <PostForm
            categories={categories}
            action={route('admin.posts.store')}
          />
        </div>
      </div>
    </AppLayout>
  );
}