import { Tag, BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import TagForm from './TagForm';

interface Props {
  tag: Tag;
}

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
    title: 'Edit Tag',
    href: '#',
  },
];

export default function Edit({ tag }: Props) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Edit Tag - ${tag.name}`} />
      <div className="flex h-full flex-1 flex-col gap-4 p-4">
        <div className="flex justify-between items-center">
          <div className="flex items-center gap-4">
            <Button
              variant="ghost"
              size="icon"
              onClick={() => router.get(route('admin.tags.index'))}
            >
              <ArrowLeft className="h-4 w-4" />
            </Button>
            <h2 className="text-3xl font-bold tracking-tight">Edit Tag</h2>
          </div>
        </div>

        <div className="max-w-2xl">
          <TagForm
            tag={tag}
            action={route('admin.tags.update', tag.id)}
          />
        </div>
      </div>
    </AppLayout>
  );
}