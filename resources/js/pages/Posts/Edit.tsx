import { useState, useEffect } from 'react';
import { Head, useForm } from '@inertiajs/react';
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
import { CKEditor } from '@/components/ui/ckeditor';
import { Category, Tag, BreadcrumbItem, Post, PostMetaInput } from '@/types';
import { MultiSelect } from '@/components/ui/multi-select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { CircleIcon } from 'lucide-react';
import { FormDataConvertible } from '@inertiajs/core';

interface Props {
  post: Post;
  categories: Category[];
  tags: Tag[];
}

type PostStatus = 'draft' | 'published' | 'scheduled';

interface FormData {
  _method: string;
  title: string;
  content: string;
  excerpt: string;
  status: PostStatus;
  featured_image: File | null;
  category_ids: number[];
  tag_ids: number[];
  meta: PostMetaInput;
  published_at: string;
  [key: string]: FormDataConvertible | PostMetaInput;
}

interface SeoData {
  title: string;
  description: string;
  focusKeyphrase: string;
  ogTitle: string;
  ogDescription: string;
  ogImage: File | null;
}

const MAX_META_TITLE_LENGTH = 60;
const MAX_META_DESCRIPTION_LENGTH = 160;

export default function Edit({ post, categories, tags }: Props) {
  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Post Management', href: '/admin/posts' },
    { title: 'Edit Post', href: `/admin/posts/${post.id}/edit` },
  ];

  // Convert post meta data to form state
  const initialMeta: PostMetaInput = {
    meta_title: post.meta?.meta_title || '',
    meta_description: post.meta?.meta_description || '',
    focus_keyphrase: post.meta?.focus_keyphrase || '',
    og_title: post.meta?.og_title || '',
    og_description: post.meta?.og_description || '',
  };

  const { data, setData, post: submitForm, processing, errors } = useForm<FormData>({
    _method: 'PUT',
    title: post.title || '',
    content: post.content || '',
    excerpt: post.excerpt || '',
    status: post.status as PostStatus,
    featured_image: null,
    category_ids: post.categories?.map(cat => cat.id) || [],
    tag_ids: post.tags?.map(tag => tag.id) || [],
    meta: initialMeta,
    published_at: post.published_at || '',
  });

  const [seoData, setSeoData] = useState<SeoData>({
    title: post.meta?.meta_title || `${post.title} | Your Site Name` || '',
    description: post.meta?.meta_description || post.excerpt || '',
    focusKeyphrase: post.meta?.focus_keyphrase || '',
    ogTitle: post.meta?.og_title || post.title || '',
    ogDescription: post.meta?.og_description || post.excerpt || '',
    ogImage: null,
  });

  // Update meta data whenever SEO fields change
  useEffect(() => {
    const metaObject: PostMetaInput = {
      meta_title: seoData.title,
      meta_description: seoData.description,
      focus_keyphrase: seoData.focusKeyphrase,
      og_title: seoData.ogTitle,
      og_description: seoData.ogDescription,
    };
    setData('meta', metaObject);
  }, [seoData]);

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    console.log('Submitting meta:', data.meta); // Debug log
    submitForm(route('admin.posts.update', post.id));
  };

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files?.[0]) {
      setData('featured_image', e.target.files[0]);
    }
  };

  const handleOgImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files?.[0]) {
      setSeoData(prev => ({
        ...prev,
        ogImage: e.target.files![0]
      }));

      const reader = new FileReader();
      reader.onload = () => {
        const updatedMeta = {
          ...data.meta,
          og_image: reader.result as string
        };
        setData('meta', updatedMeta);
      };
      reader.readAsDataURL(e.target.files[0]);
    }
  };

  const SeoPreview = () => (
    <div className="border rounded-md p-4 bg-slate-50 space-y-2">
      <div className="text-blue-600 text-lg hover:underline cursor-pointer truncate">
        {seoData.title || 'Page Title'}
      </div>
      <div className="text-green-700 text-sm">
        {window.location.origin}/{post.slug}
      </div>
      <div className="text-gray-600 text-sm">
        {seoData.description || 'Add a meta description to preview how this post might appear in search results.'}
      </div>
    </div>
  );

  const SeoScoreIndicator = ({ score }: { score: number }) => {
    let color = 'text-red-500';
    if (score >= 80) color = 'text-green-500';
    else if (score >= 50) color = 'text-yellow-500';

    return (
      <div className="flex items-center gap-2">
        <CircleIcon className={`h-4 w-4 ${color}`} />
        <span>SEO Score: {score}</span>
      </div>
    );
  };

  const calculateSeoScore = (): number => {
    let score = 0;
    
    if (seoData.title.length > 0 && seoData.title.length <= MAX_META_TITLE_LENGTH) score += 20;
    if (seoData.description.length > 0 && seoData.description.length <= MAX_META_DESCRIPTION_LENGTH) score += 20;
    
    if (seoData.focusKeyphrase) {
      score += 20;
      if (seoData.title.toLowerCase().includes(seoData.focusKeyphrase.toLowerCase())) score += 10;
      if (seoData.description.toLowerCase().includes(seoData.focusKeyphrase.toLowerCase())) score += 10;
    }

    if (seoData.ogTitle && seoData.ogDescription && (seoData.ogImage || post.meta?.og_image)) score += 20;

    return score;
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`Edit Post: ${post.title}`} />
      
      <form onSubmit={handleSubmit} encType="multipart/form-data" className="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <h1 className="text-2xl font-semibold">Edit Post</h1>
          <div className="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <Select
              value={data.status}
              onValueChange={(value: PostStatus) => {
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
              Update Post
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
                  <CKEditor
                    value={data.content}
                    onChange={(value) => setData('content', value)}
                    error={errors.content}
                  />
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
                  {post.featured_image && (
                    <div className="mb-2">
                      <img
                        src={post.featured_image}
                        alt="Featured"
                        className="max-h-32 rounded"
                      />
                    </div>
                  )}
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
              <CardHeader>
                <CardTitle className="flex justify-between items-center">
                  <span>SEO Settings</span>
                  <SeoScoreIndicator score={calculateSeoScore()} />
                </CardTitle>
              </CardHeader>
              <CardContent className="p-4 space-y-4">
                <div>
                  <Label>SEO Preview</Label>
                  <SeoPreview />
                </div>

                <div>
                  <Label>Focus Keyphrase</Label>
                  <Input
                    value={seoData.focusKeyphrase}
                    onChange={e => setSeoData(prev => ({ ...prev, focusKeyphrase: e.target.value }))}
                    placeholder="Enter focus keyphrase"
                  />
                  {seoData.focusKeyphrase && (
                    <Alert className="mt-2">
                      <AlertDescription>
                        Keyphrase density: {(data.content.toLowerCase().split(seoData.focusKeyphrase.toLowerCase()).length - 1)} occurrences
                      </AlertDescription>
                    </Alert>
                  )}
                </div>

                <div>
                  <Label>
                    Meta Title 
                    <span className={`text-sm ml-2 ${seoData.title.length > MAX_META_TITLE_LENGTH ? 'text-red-500' : 'text-gray-500'}`}>
                      ({seoData.title.length}/{MAX_META_TITLE_LENGTH})
                    </span>
                  </Label>
                  <Input
                    value={seoData.title}
                    onChange={e => setSeoData(prev => ({ ...prev, title: e.target.value }))}
                    placeholder="Enter meta title"
                  />
                </div>

                <div>
                  <Label>
                    Meta Description
                    <span className={`text-sm ml-2 ${seoData.description.length > MAX_META_DESCRIPTION_LENGTH ? 'text-red-500' : 'text-gray-500'}`}>
                      ({seoData.description.length}/{MAX_META_DESCRIPTION_LENGTH})
                    </span>
                  </Label>
                  <Textarea
                    value={seoData.description}
                    onChange={e => setSeoData(prev => ({ ...prev, description: e.target.value }))}
                    placeholder="Enter meta description"
                  />
                </div>

                <div className="border-t pt-4">
                  <Label className="text-lg font-semibold">Social Media Preview</Label>
                  
                  <div className="space-y-4 mt-4">
                    <div>
                      <Label>Facebook/OG Title</Label>
                      <Input
                        value={seoData.ogTitle}
                        onChange={e => setSeoData(prev => ({ ...prev, ogTitle: e.target.value }))}
                        placeholder="Enter social media title"
                      />
                    </div>

                    <div>
                      <Label>Facebook/OG Description</Label>
                      <Textarea
                        value={seoData.ogDescription}
                        onChange={e => setSeoData(prev => ({ ...prev, ogDescription: e.target.value }))}
                        placeholder="Enter social media description"
                      />
                    </div>

                    <div>
                      <Label>Facebook/OG Image</Label>
                      {post.meta?.og_image && (
                        <div className="mb-2">
                          <img
                            src={post.meta.og_image}
                            alt="OG"
                            className="max-h-32 rounded"
                          />
                        </div>
                      )}
                      <Input
                        type="file"
                        onChange={handleOgImageChange}
                        accept="image/*"
                      />
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </form>
    </AppLayout>
  );
}