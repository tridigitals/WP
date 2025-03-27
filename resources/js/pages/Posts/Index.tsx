import { Link, useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Post, BreadcrumbItem } from '@/types';
import { EnhancedDataTable, type PaginatedData, type DataTableFilters } from '@/components/ui/enhanced-data-table'; 
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

interface Props {
  posts: PaginatedData<Post>;
  filters: DataTableFilters & {
    status: 'all' | 'draft' | 'published' | 'scheduled' | 'trash';
  };
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
];

export default function Posts({ posts, filters }: Props) {
  const { delete: destroy, post: postRequest, processing } = useForm({});

  const handleDelete = (post: Post) => {
    if (confirm(`Are you sure you want to move "${post.title}" to trash?`)) {
      destroy(route('admin.posts.destroy', post.id));
    }
  };

  const handleForceDelete = (post: Post) => {
    if (confirm(`Are you sure you want to permanently delete "${post.title}"? This action cannot be undone.`)) {
      destroy(route('admin.posts.force-delete', post.id));
    }
  };

  const handleRestore = (post: Post) => {
    if (confirm(`Are you sure you want to restore "${post.title}" from trash?`)) {
      postRequest(route('admin.posts.restore', post.id));
    }
  };

  const tableColumns = [
    {
      key: 'title' as const,
      label: 'Title',
      sortable: true,
      className: 'break-words whitespace-normal max-w-[200px] md:max-w-full',
      render: (post: Post) => (
        <div className="max-w-[200px] md:max-w-full truncate">
          {post.title}
        </div>
      ),
    },
    {
      key: 'status' as const,
      label: 'Status',
      sortable: true,
    },
    {
      key: 'author' as const,
      label: 'Author',
      render: (post: Post) => post.author?.name || '-',
    },
    {
      key: 'published_at' as const,
      label: 'Published',
      sortable: true,
      render: (post: Post) =>
        post.published_at
          ? new Date(post.published_at).toLocaleDateString()
          : '-',
    },
    {
      key: 'created_at' as const,
      label: 'Created',
      sortable: true,
      render: (post: Post) => new Date(post.created_at).toLocaleDateString(),
    },
    {
      key: 'actions' as const,
      label: 'Actions',
      render: (post: Post) => (
        <div className="flex flex-col sm:flex-row gap-2 sm:justify-end w-full">
          {filters.status === 'trash' ? (
            <div className="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
              <Button
                variant="outline"
                size="sm"
                onClick={() => handleRestore(post)}
                disabled={processing}
                className="w-full sm:w-auto text-green-600 hover:text-green-700 hover:border-green-700 dark:text-green-500 dark:hover:text-green-400 dark:hover:border-green-400"
              >
                Restore
              </Button>
              <Button
                variant="outline"
                size="sm"
                onClick={() => handleForceDelete(post)}
                disabled={processing}
                className="w-full sm:w-auto text-red-600 hover:text-red-700 hover:border-red-700 dark:text-red-500 dark:hover:text-red-400 dark:hover:border-red-400"
              >
                Delete Permanently
              </Button>
            </div>
          ) : (
            <div className="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
              <Link href={route('admin.posts.show', post.id)} className="w-full sm:w-auto">
                <Button variant="outline" size="sm" className="w-full sm:w-auto">
                  View
                </Button>
              </Link>
              <Link href={route('admin.posts.edit', post.id)} className="w-full sm:w-auto">
                <Button variant="outline" size="sm" className="w-full sm:w-auto">
                  Edit
                </Button>
              </Link>
              <Button
                variant="outline"
                size="sm"
                onClick={() => handleDelete(post)}
                disabled={processing}
                className="w-full sm:w-auto text-red-600 hover:text-red-700 hover:border-red-700 dark:text-red-500 dark:hover:text-red-400 dark:hover:border-red-400"
              >
                Move to Trash
              </Button>
            </div>
          )}
        </div>
      ),
    },
  ] as const;

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Post Management" />
      <div className="max-w-full flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex flex-col gap-4">
          <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h1 className="text-2xl font-semibold sm:text-xl md:text-lg lg:text-base">Post Management</h1>
            <div className="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center w-full sm:w-auto">
              <div className="flex-1 sm:flex-none">
                <Select
                  value={filters.status}
                  onValueChange={(value) => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('status', value);
                    window.location.href = url.toString();
                  }}
                >
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Posts</SelectItem>
                    <SelectItem value="published">Published</SelectItem>
                    <SelectItem value="draft">Drafts</SelectItem>
                    <SelectItem value="scheduled">Scheduled</SelectItem>
                    <SelectItem value="trash">Trash</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              
              {filters.status !== 'trash' && (
                <div className="flex-1 sm:flex-none">
                  <Link href={route('admin.posts.create')} className="block w-full">
                    <Button className="w-full">Create Post</Button>
                  </Link>
                </div>
              )}
            </div>
          </div>
        </div>

        <div className="w-full overflow-hidden rounded-md border">
          <div className="overflow-x-auto md:overflow-visible">
            <EnhancedDataTable<Post>
              data={posts}
              columns={tableColumns}
              filters={filters}
              onBulkAction={(action, selectedPosts) => {
                if (action === 'delete') {
                  const isTrashView = filters.status === 'trash';
                  const confirmMessage = isTrashView
                    ? `Are you sure you want to permanently delete ${selectedPosts.length} posts?`
                    : `Are you sure you want to move ${selectedPosts.length} posts to trash?`;

                  if (confirm(confirmMessage)) {
                    selectedPosts.forEach(post => {
                      if (isTrashView) {
                        destroy(route('admin.posts.force-delete', post.id));
                      } else {
                        destroy(route('admin.posts.destroy', post.id));
                      }
                    });
                  }
                }
              }}
            />
          </div>
        </div>
      </div>
    </AppLayout>
  );
}