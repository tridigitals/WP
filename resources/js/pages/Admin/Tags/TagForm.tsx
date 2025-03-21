import { Tag } from '@/types';
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';

interface Props {
  tag?: Tag;
  action: string;
}

export default function TagForm({ tag, action }: Props) {
  const { data, setData, post: submit, processing, errors } = useForm({
    name: tag?.name || '',
    slug: tag?.slug || '',
    description: tag?.description || '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    submit(action);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="grid gap-4">
        <div className="grid gap-2">
          <label htmlFor="name" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            Nama Tag
          </label>
          <Input
            id="name"
            value={data.name}
            onChange={e => setData('name', e.target.value)}
            placeholder="Nama tag"
          />
          {errors.name && (
            <p className="text-sm font-medium text-destructive">{errors.name}</p>
          )}
        </div>

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

        <div className="grid gap-2">
          <label htmlFor="description" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            Deskripsi
          </label>
          <Textarea
            id="description"
            value={data.description}
            onChange={e => setData('description', e.target.value)}
            placeholder="Deskripsi tag (opsional)"
          />
          {errors.description && (
            <p className="text-sm font-medium text-destructive">{errors.description}</p>
          )}
        </div>
      </div>

      <div className="flex justify-end">
        <Button type="submit" disabled={processing}>
          {tag ? 'Update Tag' : 'Buat Tag'}
        </Button>
      </div>
    </form>
  );
}