import { Head } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Post, BreadcrumbItem } from '@/types';

interface Props {
  post: Post;
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Post Management',
    href: '/admin/posts',
  },
  {
    title: 'View Post',
    href: '/admin/posts/show',
  },
];

export default function Show({ post }: Props) {
  const statusColors = {
    published: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    scheduled: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`View Post - ${post.title}`} />
      
      <div className="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <h1 className="text-2xl font-semibold">{post.title}</h1>
          <div className="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <Link href={route('admin.posts.edit', post.id)}>
              <Button variant="outline" className="w-full sm:w-auto">Edit Post</Button>
            </Link>
            <Link href={route('admin.posts.index')}>
              <Button variant="outline" className="w-full sm:w-auto">Back to List</Button>
            </Link>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div className="lg:col-span-2 space-y-4">
            {post.featured_image && (
              <img
                src={post.featured_image}
                alt={post.title}
                className="w-full rounded-lg object-cover max-h-96"
              />
            )}

            <Card>
              <CardContent className="p-6">
                <div className="prose prose-sm dark:prose-invert max-w-none"
                  dangerouslySetInnerHTML={{ __html: post.content }}
                />
              </CardContent>
            </Card>

            {post.excerpt && (
              <Card>
                <CardContent className="p-6">
                  <h2 className="text-lg font-semibold mb-2">Excerpt</h2>
                  <p className="text-gray-600 dark:text-gray-300">
                    {post.excerpt}
                  </p>
                </CardContent>
              </Card>
            )}
          </div>

          <div className="space-y-4">
            <Card>
              <CardContent className="p-4 space-y-4">
                <div>
                  <h2 className="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Status
                  </h2>
                  <span
                    className={`mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                      statusColors[post.status as keyof typeof statusColors]
                    }`}
                  >
                    {post.status.charAt(0).toUpperCase() + post.status.slice(1)}
                  </span>
                </div>

                <div>
                  <h2 className="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Author
                  </h2>
                  <p className="mt-1">{post.author?.name}</p>
                </div>

                {post.published_at && (
                  <div>
                    <h2 className="text-sm font-medium text-gray-500 dark:text-gray-400">
                      Published At
                    </h2>
                    <p className="mt-1">
                      {new Date(post.published_at).toLocaleDateString()}
                    </p>
                  </div>
                )}

                <div>
                  <h2 className="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Categories
                  </h2>
                  <div className="mt-1 flex flex-wrap gap-1">
                    {post.categories?.map(category => (
                      <span
                        key={category.id}
                        className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                      >
                        {category.name}
                      </span>
                    ))}
                  </div>
                </div>

                <div>
                  <h2 className="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Tags
                  </h2>
                  <div className="mt-1 flex flex-wrap gap-1">
                    {post.tags?.map(tag => (
                      <span
                        key={tag.id}
                        className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
                      >
                        {tag.name}
                      </span>
                    ))}
                  </div>
                </div>
              </CardContent>
            </Card>

            {post.postMeta && post.postMeta.length > 0 && (
              <Card>
                <CardContent className="p-4 space-y-4">
                  <h2 className="font-semibold">Meta Fields</h2>
                  <div className="space-y-2">
                    {post.postMeta.map(meta => (
                      <div key={meta.id} className="grid grid-cols-2 gap-2">
                        <span className="text-sm font-medium text-gray-500 dark:text-gray-400">
                          {meta.key}
                        </span>
                        <span className="text-sm">{meta.value}</span>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            )}
          </div>
        </div>
      </div>
    </AppLayout>
  );
}