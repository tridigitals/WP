import { Post, Category } from '@/types';
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { TagSelect } from '@/components/tag-select';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useCallback, useState } from 'react';

interface Props {
  post?: Post;
  categories: Category[];
  action: string;
}

export default function PostForm({ post, categories, action }: Props) {
  type PostStatus = 'draft' | 'published' | 'archived';
  interface FormData {
    title: string;
    slug: string;
    content: string;
    excerpt: string;
    status: PostStatus;
    category_id: string | number | null;
    featured_image: File | null;
    meta: Record<string, any>;
    tags: number[];
    [key: string]: any;
  }

  const { data, setData, post: submit, processing, errors } = useForm<FormData>({
    title: post?.title || '',
    slug: post?.slug || '',
    content: post?.content || '',
    excerpt: post?.excerpt || '',
    status: post?.status || 'draft',
    category_id: post?.category_id || '',
    featured_image: null,
    meta: post?.meta || {},
    tags: post?.tags?.map(tag => tag.id) || [],
  });

  const [previewImage, setPreviewImage] = useState<string>(
    post?.featured_image || ''
  );

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    submit(action);
  };

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      // Set the file in form data
      setData('featured_image', file);
      
      // Create preview URL
      const imageUrl = URL.createObjectURL(file);
      setPreviewImage(imageUrl);
      
      // Clean up the URL when component unmounts
      return () => URL.revokeObjectURL(imageUrl);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-8">
      <div className="grid gap-6">
        {/* Title Field */}
        <div className="grid gap-2">
          <label htmlFor="title" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            Judul
          </label>
          <Input
            id="title"
            value={data.title}
            onChange={e => setData('title', e.target.value)}
            placeholder="Judul post"
          />
          {errors.title && (
            <p className="text-sm font-medium text-destructive">{errors.title}</p>
          )}
        </div>

        {/* Slug Field */}
        <div className="grid gap-2">
          <label htmlFor="slug" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            Slug
          </label>
          <Input
            id="slug"
            value={data.slug}
            onChange={e => setData('slug', e.target.value)}
            placeholder="url-friendly-slug"
          />
          {errors.slug && (
            <p className="text-sm font-medium text-destructive">{errors.slug}</p>
          )}
        </div>

        {/* Content Field */}
        <div className="grid gap-2">
          <label htmlFor="content" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            Konten
          </label>
          <Textarea
            id="content"
            value={data.content}
            onChange={e => setData('content', e.target.value)}
            placeholder="Tulis konten post di sini..."
            className="min-h-[200px]"
          />
          {errors.content && (
            <p className="text-sm font-medium text-destructive">{errors.content}</p>
          )}
        </div>

        {/* Excerpt Field */}
        <div className="grid gap-2">
          <label htmlFor="excerpt" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            Ringkasan
          </label>
          <Textarea
            id="excerpt"
            value={data.excerpt}
            onChange={e => setData('excerpt', e.target.value)}
            placeholder="Ringkasan singkat post (opsional)"
          />
          {errors.excerpt && (
            <p className="text-sm font-medium text-destructive">{errors.excerpt}</p>
          )}
        </div>

        {/* Status Field */}
        <div className="grid gap-2">
          <label htmlFor="status" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            Status
          </label>
          <select
            id="status"
            value={data.status}
            onChange={e => setData('status', e.target.value as PostStatus)}
            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
          </select>
          {errors.status && (
            <p className="text-sm font-medium text-destructive">{errors.status}</p>
          )}
        </div>

        {/* Category Field */}
        <div className="grid gap-2">
          <label htmlFor="category_id" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            Kategori
          </label>
          <select
            id="category_id"
            value={data.category_id || ''}
            onChange={e => setData('category_id', e.target.value)}
            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <option value="">Pilih Kategori</option>
            {categories.map(category => (
              <option key={category.id} value={category.id}>
                {category.name}
              </option>
            ))}
          </select>
          {errors.category_id && (
            <p className="text-sm font-medium text-destructive">{errors.category_id}</p>
          )}
        </div>

        {/* Tags Field */}
        <div className="grid gap-2">
          <label htmlFor="tags" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            Tags
          </label>
          <TagSelect
            selectedTags={data.tags}
            onTagsChange={(tagIds) => setData('tags', tagIds)}
          />
          {errors.tags && (
            <p className="text-sm font-medium text-destructive">{errors.tags}</p>
          )}
        </div>

        {/* Featured Image Field */}
        <div className="grid gap-2">
          <label htmlFor="featured_image" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            Featured Image
          </label>
          <Input
            id="featured_image"
            type="file"
            accept="image/*"
            onChange={handleImageChange}
          />
          {previewImage && (
            <img
              src={previewImage}
              alt="Preview"
              className="mt-2 max-w-xs rounded-md"
            />
          )}
          {errors.featured_image && (
            <p className="text-sm font-medium text-destructive">{errors.featured_image}</p>
          )}
        </div>
      </div>

      <div className="flex justify-end">
        <Button type="submit" disabled={processing}>
          {post ? 'Update Post' : 'Create Post'}
        </Button>
      </div>
    </form>
  );
}