import { useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Tag, BreadcrumbItem } from '@/types';

interface Props {
  tag: Tag;
}

export default function EditTag({ tag }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    name: tag.name,
    slug: tag.slug,
    description: tag.description ?? '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.tags.update', tag.id));
  };

  const generateSlug = (text: string) => {
    return text
      .toString()
      .toLowerCase()
      .trim()
      .replace(/\s+/g, '-')     // Replace spaces with -
      .replace(/[^\w\-]+/g, '') // Remove all non-word chars
      .replace(/\-\-+/g, '-')   // Replace multiple - with single -
      .replace(/^-+/, '')       // Trim - from start of text
      .replace(/-+$/, '');      // Trim - from end of text
  };

  const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const name = e.target.value;
    setData({
      ...data,
      name,
      slug: generateSlug(name),
    });
  };

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
      title: `Edit ${tag.name}`,
      href: `/admin/tags/${tag.id}/edit`,
    },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Edit Tag: ${tag.name}`} />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 sm:p-2 md:p-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-semibold sm:text-xl md:text-lg">Edit Tag: {tag.name}</h1>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
            <div className="px-4 py-5 sm:p-6">
              <h2 className="text-lg font-medium mb-4">Basic Information</h2>
              <div className="space-y-4 sm:space-y-2">
                <div>
                  <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Tag Name
                  </label>
                  <input
                    type="text"
                    id="name"
                    className="mt-1 block w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                    value={data.name}
                    onChange={handleNameChange}
                  />
                  {errors.name && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.name}</p>
                  )}
                </div>
                <div>
                  <label htmlFor="slug" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Slug
                  </label>
                  <input
                    type="text"
                    id="slug"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                    value={data.slug}
                    onChange={e => setData('slug', e.target.value)}
                  />
                  {errors.slug && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.slug}</p>
                  )}
                </div>
                <div>
                  <label htmlFor="description" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Description
                  </label>
                  <textarea
                    id="description"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                    value={data.description ?? ''}
                    onChange={e => setData('description', e.target.value)}
                  />
                  {errors.description && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.description}</p>
                  )}
                </div>
              </div>
            </div>
          </div>

          <div className="flex justify-end gap-4">
            <Button
              type="button"
              variant="outline"
              onClick={() => window.history.back()}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={processing}>
              Update Tag
            </Button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
}