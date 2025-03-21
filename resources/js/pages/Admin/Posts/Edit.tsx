import { Post, Category, BreadcrumbItem } from '@/types';
import PostForm from './PostForm';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';

interface Props {
  post: Post;
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
    title: 'Edit Post',
    href: '#',
  },
];

export default function Edit({ post, categories }: Props) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Edit Post - ${post.title}`} />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex justify-between items-center">
          <div className="flex items-center gap-4">
            <Button
              variant="ghost"
              size="icon"
              onClick={() => router.get(route('admin.posts.index'))}
            >
              <ArrowLeft className="h-4 w-4" />
            </Button>
            <h2 className="text-3xl font-bold tracking-tight">Edit Post</h2>
          </div>
        </div>

        <div className="max-w-2xl">
          <PostForm
            post={post}
            categories={categories}
            action={route('admin.posts.update', post.id)}
          />
        </div>
      </div>
    </AppLayout>
  );
}