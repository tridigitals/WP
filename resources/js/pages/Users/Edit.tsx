import { useForm } from '@inertiajs/react';
import AdminLayout from '@/layouts/AdminLayout';
import { Button } from '@/components/ui/button';
import { Role, User } from '@/types';

interface Props {
  user: User;
  roles: Role[];
}

export default function EditUser({ user, roles }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    name: user.name,
    email: user.email,
    password: '',
    password_confirmation: '',
    roles: user.roles.map(r => r.id),
    bio: user.bio || '',
    website: user.website || '',
    social_media_links: user.social_media_links || {},
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.users.update', user.id));
  };

  const updateSocialMedia = (platform: string, url: string) => {
    setData('social_media_links', {
      ...data.social_media_links,
      [platform]: url,
    });
  };

  const isUserSuperAdmin = user.roles.some(role => role.name === 'super-admin');

  return (
    <AdminLayout title="Edit User">
      <div className="container py-12">
        <div className="max-w-3xl mx-auto">
          <h1 className="text-2xl font-semibold mb-6">Edit User: {user.name}</h1>

          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Basic Information */}
            <div className="bg-white shadow rounded-lg p-6 space-y-4">
              <h2 className="text-lg font-medium">Basic Information</h2>
              
              <div>
                <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                  Name
                </label>
                <input
                  type="text"
                  id="name"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  value={data.name}
                  onChange={e => setData('name', e.target.value)}
                  disabled={isUserSuperAdmin}
                />
                {errors.name && (
                  <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                )}
              </div>

              <div>
                <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                  Email
                </label>
                <input
                  type="email"
                  id="email"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  value={data.email}
                  onChange={e => setData('email', e.target.value)}
                  disabled={isUserSuperAdmin}
                />
                {errors.email && (
                  <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                )}
              </div>

              <div>
                <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                  New Password (leave blank to keep current)
                </label>
                <input
                  type="password"
                  id="password"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  value={data.password}
                  onChange={e => setData('password', e.target.value)}
                  disabled={isUserSuperAdmin}
                />
                {errors.password && (
                  <p className="mt-1 text-sm text-red-600">{errors.password}</p>
                )}
              </div>

              <div>
                <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">
                  Confirm New Password
                </label>
                <input
                  type="password"
                  id="password_confirmation"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  value={data.password_confirmation}
                  onChange={e => setData('password_confirmation', e.target.value)}
                  disabled={isUserSuperAdmin}
                />
              </div>
            </div>

            {/* Roles */}
            <div className="bg-white shadow rounded-lg p-6 space-y-4">
              <h2 className="text-lg font-medium">Roles</h2>
              
              <div className="space-y-2">
                {roles.map((role) => (
                  <label key={role.id} className="inline-flex items-center mr-4">
                    <input
                      type="checkbox"
                      className="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      checked={data.roles.includes(role.id)}
                      onChange={(e) => {
                        const roles = e.target.checked
                          ? [...data.roles, role.id]
                          : data.roles.filter((id) => id !== role.id);
                        setData('roles', roles);
                      }}
                      disabled={isUserSuperAdmin}
                    />
                    <span className="ml-2 text-sm text-gray-600">
                      {role.name}
                    </span>
                  </label>
                ))}
              </div>
              {errors.roles && (
                <p className="mt-1 text-sm text-red-600">{errors.roles}</p>
              )}
            </div>

            {/* Profile Information */}
            <div className="bg-white shadow rounded-lg p-6 space-y-4">
              <h2 className="text-lg font-medium">Profile Information</h2>
              
              <div>
                <label htmlFor="bio" className="block text-sm font-medium text-gray-700">
                  Bio
                </label>
                <textarea
                  id="bio"
                  rows={3}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  value={data.bio}
                  onChange={e => setData('bio', e.target.value)}
                  disabled={isUserSuperAdmin}
                />
                {errors.bio && (
                  <p className="mt-1 text-sm text-red-600">{errors.bio}</p>
                )}
              </div>

              <div>
                <label htmlFor="website" className="block text-sm font-medium text-gray-700">
                  Website
                </label>
                <input
                  type="url"
                  id="website"
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  value={data.website}
                  onChange={e => setData('website', e.target.value)}
                  disabled={isUserSuperAdmin}
                />
                {errors.website && (
                  <p className="mt-1 text-sm text-red-600">{errors.website}</p>
                )}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Social Media Links
                </label>
                {['twitter', 'linkedin', 'github'].map((platform) => (
                  <div key={platform} className="mt-2">
                    <label htmlFor={platform} className="block text-sm text-gray-600 capitalize">
                      {platform}
                    </label>
                    <input
                      type="url"
                      id={platform}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      value={data.social_media_links[platform] || ''}
                      onChange={e => updateSocialMedia(platform, e.target.value)}
                      placeholder={`https://${platform}.com/username`}
                      disabled={isUserSuperAdmin}
                    />
                  </div>
                ))}
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
              <Button 
                type="submit" 
                disabled={processing || isUserSuperAdmin}
              >
                Update User
              </Button>
            </div>
          </form>
        </div>
      </div>
    </AdminLayout>
  );
}