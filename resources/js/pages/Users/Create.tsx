import { useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Role, BreadcrumbItem } from '@/types';

interface Props {
  roles: Role[];
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'User Management',
    href: '/admin/users',
  },
  {
    title: 'Create User',
    href: '/admin/users/create',
  },
];

const socialMediaPlatforms = [
  {
    name: 'twitter',
    label: 'Twitter',
    placeholder: 'https://twitter.com/username',
    icon: 'Twitter',
  },
  {
    name: 'linkedin',
    label: 'LinkedIn',
    placeholder: 'https://linkedin.com/in/username',
    icon: 'LinkedIn',
  },
  {
    name: 'github',
    label: 'GitHub',
    placeholder: 'https://github.com/username',
    icon: 'GitHub',
  },
];

export default function CreateUser({ roles }: Props) {
  const { data, setData, post, processing, errors } = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [] as number[],
    bio: '',
    website: '',
    social_media_links: {} as Record<string, string>,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.users.store'));
  };

  const updateSocialMedia = (platform: string, url: string) => {
    setData('social_media_links', {
      ...data.social_media_links,
      [platform]: url,
    });
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Create User" />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-semibold">Create New User</h1>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          {/* Basic Information */}
          <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
            <div className="px-4 py-5 sm:p-6">
              <h2 className="text-lg font-medium mb-4">Basic Information</h2>
              <div className="space-y-4">
                <div>
                  <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Name
                  </label>
                  <input
                    type="text"
                    id="name"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                    value={data.name}
                    onChange={e => setData('name', e.target.value)}
                  />
                  {errors.name && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.name}</p>
                  )}
                </div>

                <div>
                  <label htmlFor="email" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Email
                  </label>
                  <input
                    type="email"
                    id="email"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                    value={data.email}
                    onChange={e => setData('email', e.target.value)}
                  />
                  {errors.email && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.email}</p>
                  )}
                </div>

                <div>
                  <label htmlFor="password" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Password
                  </label>
                  <input
                    type="password"
                    id="password"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                    value={data.password}
                    onChange={e => setData('password', e.target.value)}
                  />
                  {errors.password && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.password}</p>
                  )}
                </div>

                <div>
                  <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Confirm Password
                  </label>
                  <input
                    type="password"
                    id="password_confirmation"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                    value={data.password_confirmation}
                    onChange={e => setData('password_confirmation', e.target.value)}
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Roles */}
          <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
            <div className="px-4 py-5 sm:p-6">
              <h2 className="text-lg font-medium mb-4">Roles</h2>
              <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
                {roles.map((role) => (
                  <label key={role.id} className="inline-flex items-center">
                    <input
                      type="checkbox"
                      className="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600"
                      checked={data.roles.includes(role.id)}
                      onChange={(e) => {
                        const roles = e.target.checked
                          ? [...data.roles, role.id]
                          : data.roles.filter((id) => id !== role.id);
                        setData('roles', roles);
                      }}
                    />
                    <span className="ml-2 text-sm text-gray-600 dark:text-gray-300">
                      {role.name}
                    </span>
                  </label>
                ))}
              </div>
              {errors.roles && (
                <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.roles}</p>
              )}
            </div>
          </div>

          {/* Profile Information */}
          <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border">
            <div className="px-4 py-5 sm:p-6">
              <h2 className="text-lg font-medium mb-4">Profile Information</h2>
              <div className="space-y-4">
                <div>
                  <label htmlFor="bio" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Bio
                  </label>
                  <textarea
                    id="bio"
                    rows={3}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                    value={data.bio}
                    onChange={e => setData('bio', e.target.value)}
                  />
                  {errors.bio && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.bio}</p>
                  )}
                </div>

                <div>
                  <label htmlFor="website" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Website
                  </label>
                  <div className="mt-1 flex rounded-md shadow-sm">
                    <input
                      type="url"
                      id="website"
                      className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                      value={data.website}
                      onChange={e => setData('website', e.target.value)}
                      placeholder="https://example.com"
                    />
                  </div>
                  {errors.website && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.website}</p>
                  )}
                </div>

                <div className="space-y-4">
                  <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Social Media Links
                  </label>
                  <div className="grid gap-4 sm:grid-cols-3">
                    {socialMediaPlatforms.map((platform) => (
                      <div key={platform.name} className="space-y-1">
                        <label htmlFor={platform.name} className="block text-sm text-gray-600 dark:text-gray-300">
                          {platform.label}
                        </label>
                        <div className="mt-1 flex rounded-md shadow-sm">
                          <input
                            type="url"
                            id={platform.name}
                            className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                            value={data.social_media_links[platform.name] || ''}
                            onChange={e => updateSocialMedia(platform.name, e.target.value)}
                            placeholder={platform.placeholder}
                          />
                        </div>
                      </div>
                    ))}
                  </div>
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
              Create User
            </Button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
}