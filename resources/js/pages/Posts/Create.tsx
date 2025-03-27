import { useState } from 'react';
import { Head } from '@inertiajs/react';
import { useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import { Category, Tag, BreadcrumbItem } from '@/types';
import { MultiSelect } from '@/components/ui/multi-select';
import { Card, CardContent } from '@/components/ui/card';

interface Props {
  categories: Category[];
  tags: Tag[];
}

type PostStatus = 'draft' | 'published' | 'scheduled';

interface FormData extends Record<string, any> {
  title: string;
  content: string;
  excerpt: string;
  status: PostStatus;
  featured_image: File | null;
  category_ids: number[];
  tag_ids: number[];
  meta: Record<string, string>;
  published_at: string;
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
    title: 'Create Post',
    href: '/admin/posts/create',
  },
];

export default function Create({ categories, tags }: Props) {
  const { data, setData, post, processing, errors } = useForm<FormData>({
    title: '',
    content: '',
    excerpt: '',
    status: 'draft',
    featured_image: null,
    category_ids: [],
    tag_ids: [],
    meta: {},
    published_at: '',
  });

  const [metaFields, setMetaFields] = useState<{ key: string; value: string }[]>([
    { key: '', value: '' },
  ]);

  const editor = useEditor({
    extensions: [StarterKit],
    content: '',
    onUpdate: ({ editor }) => {
      setData('content', editor.getHTML());
    },
  });

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    // Convert meta fields to object
    const metaObject = metaFields.reduce((acc, { key, value }) => {
      if (key && value) {
        acc[key] = value;
      }
      return acc;
    }, {} as Record<string, string>);
    setData('meta', metaObject);

    post(route('admin.posts.store'));
  };

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files?.[0]) {
      setData('featured_image', e.target.files[0]);
    }
  };

  const addMetaField = () => {
    setMetaFields([...metaFields, { key: '', value: '' }]);
  };

  const updateMetaField = (index: number, field: 'key' | 'value', value: string) => {
    const newFields = [...metaFields];
    newFields[index][field] = value;
    setMetaFields(newFields);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Create Post" />
      
      <form onSubmit={handleSubmit} className="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <h1 className="text-2xl font-semibold">Create Post</h1>
          <div className="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <Select
              value={data.status}
              onValueChange={(value: 'draft' | 'published' | 'scheduled') => {
                setData('status', value);
                if (value === 'scheduled' && !data.published_at) {
                  setData('published_at', new Date().toISOString().split('T')[0]);
                }
              }}
            >
              <SelectTrigger className="w-full sm:w-[200px]">
                <SelectValue placeholder="Select status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="draft">Draft</SelectItem>
                <SelectItem value="published">Published</SelectItem>
                <SelectItem value="scheduled">Scheduled</SelectItem>
              </SelectContent>
            </Select>
            <Button type="submit" disabled={processing} className="w-full sm:w-auto">
              Save Post
            </Button>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div className="lg:col-span-2 space-y-4">
            <div>
              <Label htmlFor="title">Title</Label>
              <Input
                id="title"
                value={data.title}
                onChange={e => setData('title', e.target.value)}
                placeholder="Enter post title"
              />
              {errors.title && (
                <span className="text-sm text-red-500">{errors.title}</span>
              )}
            </div>

            <div>
              <Label>Content</Label>
              <Card>
                <CardContent className="p-3">
                  <div className="prose prose-sm dark:prose-invert max-w-none">
                    <EditorContent editor={editor} />
                  </div>
                </CardContent>
              </Card>
              {errors.content && (
                <span className="text-sm text-red-500">{errors.content}</span>
              )}
            </div>

            <div>
              <Label htmlFor="excerpt">Excerpt</Label>
              <Textarea
                id="excerpt"
                value={data.excerpt}
                onChange={e => setData('excerpt', e.target.value)}
                placeholder="Enter post excerpt"
              />
            </div>
          </div>

          <div className="space-y-4">
            <Card>
              <CardContent className="p-4 space-y-4">
                <div>
                  <Label>Featured Image</Label>
                  <Input
                    type="file"
                    onChange={handleImageChange}
                    accept="image/*"
                  />
                  {errors.featured_image && (
                    <span className="text-sm text-red-500">
                      {errors.featured_image}
                    </span>
                  )}
                </div>

                <div>
                  <Label>Categories</Label>
                  <MultiSelect
                    options={categories.map(cat => ({
                      value: cat.id.toString(),
                      label: cat.name,
                    }))}
                    value={data.category_ids.map(id => id.toString())}
                    onChange={values =>
                      setData(
                        'category_ids',
                        values.map(v => parseInt(v))
                      )
                    }
                  />
                </div>

                <div>
                  <Label>Tags</Label>
                  <MultiSelect
                    options={tags.map(tag => ({
                      value: tag.id.toString(),
                      label: tag.name,
                    }))}
                    value={data.tag_ids.map(id => id.toString())}
                    onChange={values =>
                      setData(
                        'tag_ids',
                        values.map(v => parseInt(v))
                      )
                    }
                  />
                </div>

                {data.status === 'scheduled' && (
                  <div>
                    <Label htmlFor="published_at">Publish Date</Label>
                    <Input
                      type="datetime-local"
                      id="published_at"
                      value={data.published_at}
                      onChange={e => setData('published_at', e.target.value)}
                    />
                  </div>
                )}
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-4 space-y-4">
                <div className="flex justify-between items-center">
                  <Label>Meta Fields</Label>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={addMetaField}
                  >
                    Add Field
                  </Button>
                </div>
                
                {metaFields.map((field, index) => (
                  <div key={index} className="grid grid-cols-2 gap-2">
                    <Input
                      placeholder="Key"
                      value={field.key}
                      onChange={e => updateMetaField(index, 'key', e.target.value)}
                    />
                    <Input
                      placeholder="Value"
                      value={field.value}
                      onChange={e =>
                        updateMetaField(index, 'value', e.target.value)
                      }
                    />
                  </div>
                ))}
              </CardContent>
            </Card>
          </div>
        </div>
      </form>
    </AppLayout>
  );
}