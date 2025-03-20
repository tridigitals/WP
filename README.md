# Laravel CMS

A modern, flexible content management system built with Laravel.

## Features

- 📝 Post and Page Management
- 🗂️ Categories and Tags
- 🖼️ Media Library with Image Optimization
- 🔍 SEO Tools and Meta Management
- 📱 Responsive Interface
- 🔒 Role-based Access Control
- 🚀 Cache Management
- ⚙️ Configurable Settings

## Requirements

- PHP >= 8.1
- MySQL >= 5.7 or PostgreSQL >= 10.0
- Composer
- Node.js & NPM
- Required PHP Extensions:
  - BCMath
  - Ctype
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - GD
  - Fileinfo

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/your-cms.git
cd your-cms
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install and compile frontend assets:
```bash
npm install
npm run build
```

4. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

5. Set up your database in `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run the CMS installer:
```bash
php artisan cms:install
```

Optional: Seed sample data:
```bash
php artisan cms:install --seed
```

## Configuration

The CMS can be configured through:

1. Admin Panel: `/admin/settings`
2. Configuration files in `config/cms.php`
3. Environment variables

### Key Configuration Options

```php
// config/cms.php

return [
    'name' => env('CMS_NAME', 'Laravel CMS'),
    
    'media' => [
        'disk' => env('CMS_MEDIA_DISK', 'public'),
        'max_file_size' => env('CMS_MEDIA_MAX_FILE_SIZE', 5120),
        'optimize_images' => env('CMS_OPTIMIZE_IMAGES', true),
    ],
    
    'cache' => [
        'enabled' => env('CMS_CACHE_ENABLED', true),
        'duration' => env('CMS_CACHE_DURATION', 3600),
    ],
];
```

## Basic Usage

1. Access the admin panel at `/admin`
2. Log in with your admin credentials
3. Start managing content:
   - Create categories and tags
   - Upload media
   - Create posts and pages
   - Configure SEO settings

## Content Management

### Posts and Pages
- Create, edit, and delete posts
- Schedule posts for future publication
- Organize content with categories and tags
- Add featured images
- Manage meta information

### Media Library
- Upload and organize media files
- Automatic image optimization
- Generate responsive images
- Support for various file types

### SEO Tools
- Meta information management
- Open Graph tags
- Twitter Cards
- Schema.org markup
- SEO analysis and recommendations

## API Endpoints

The CMS provides a RESTful API for content management:

```
# Posts
GET    /api/v1/posts
POST   /api/v1/posts
GET    /api/v1/posts/{id}
PUT    /api/v1/posts/{id}
DELETE /api/v1/posts/{id}

# Categories
GET    /api/v1/categories
POST   /api/v1/categories
GET    /api/v1/categories/{id}
PUT    /api/v1/categories/{id}
DELETE /api/v1/categories/{id}

# Tags
GET    /api/v1/tags
POST   /api/v1/tags
GET    /api/v1/tags/{id}
PUT    /api/v1/tags/{id}
DELETE /api/v1/tags/{id}

# Media
GET    /api/v1/media
POST   /api/v1/media
GET    /api/v1/media/{id}
DELETE /api/v1/media/{id}
```

## Maintenance

### Cache Management
```bash
php artisan cache:clear    # Clear cache
php artisan view:clear    # Clear compiled views
php artisan config:clear  # Clear config cache
```

### Database Management
```bash
php artisan migrate:fresh  # Reset database
php artisan db:seed       # Seed sample data
```

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests.

## Security

If you discover any security related issues, please email security@example.com instead of using the issue tracker.

## License

This CMS is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
