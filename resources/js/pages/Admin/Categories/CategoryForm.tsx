import { Category } from '@/types';
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { FormLabel } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';

interface Props {
  category?: Category;
  categories: Category[];
  action: string;
}

interface FormData {
  name: string;
  slug: string;
  description: string;
  parent_id: string;
  [key: string]: string | File | Blob | number | boolean | null | undefined;
}

export default function CategoryForm({ category, categories, action }: Props) {
  const { data, setData, post: submit, processing, errors } = useForm<FormData>({
    name: category?.name || '',
    slug: category?.slug || '',
    description: category?.description ?? '',
    parent_id: category?.parent_id ? String(category.parent_id) : '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    submit(action);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="grid gap-4">
        <div className="grid gap-2">
          <FormLabel htmlFor="name">Nama Kategori</FormLabel>
          <Input
            id="name"
            value={data.name}
            onChange={e => setData('name', e.target.value)}
            placeholder="Nama kategori"
          />
          {errors.name && (
            <p className="text-sm font-medium text-destructive">{errors.name}</p>
          )}
        </div>

        <div className="grid gap-2">
          <FormLabel htmlFor="slug">Slug</FormLabel>
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

        <div className="grid gap-2">
          <FormLabel htmlFor="description">Deskripsi</FormLabel>
          <Textarea
            id="description"
            value={data.description}
            onChange={e => setData('description', e.target.value)}
            placeholder="Deskripsi kategori (opsional)"
          />
          {errors.description && (
            <p className="text-sm font-medium text-destructive">
              {errors.description}
            </p>
          )}
        </div>

        <div className="grid gap-2">
          <FormLabel htmlFor="parent_id">Kategori Induk</FormLabel>
          <select
            id="parent_id"
            value={data.parent_id}
            onChange={e => setData('parent_id', e.target.value)}
            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <option value="">Tidak ada</option>
            {categories
              .filter(cat => cat.id !== category?.id)
              .map(cat => (
                <option key={cat.id} value={cat.id}>
                  {cat.name}
                </option>
              ))}
          </select>
          {errors.parent_id && (
            <p className="text-sm font-medium text-destructive">
              {errors.parent_id}
            </p>
          )}
        </div>
      </div>

      <div className="flex justify-end">
        <Button type="submit" disabled={processing}>
          {category ? 'Update Kategori' : 'Buat Kategori'}
        </Button>
      </div>
    </form>
  );
}