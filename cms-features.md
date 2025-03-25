# CMS Features Todo List

Gunakan editor text free yang tidak menggunakan limitasi sama sekali
Gunakan page builder dengan fitur seperti di elementor
tambahkan unit test di setiap fitur

## 1. Database & Model Setup
- [x] Posts
  - Title, content, excerpt, status, featured_image
  - Timestamps, soft deletes (trash)
  - Author relationship
  - Category & tag relationships
- [x] Categories
  - Name, slug, description
  - Parent category relationship
  - Posts relationship
- [x] Tags
  - Name, slug
  - Posts relationship
- [x] Users (extend existing)
  - Role (admin/author)
  - Profile fields
  - Posts relationship
- [x] Media Library
  - File path, name, type
  - Alt text, caption
  - Dimensions for images
- [x] Comments
  - Content, author
  - Post relationship
  - Parent comment (for replies)
- [x] Menu Management
  - Name, location
  - Menu items structure
- [x] Settings/Options
  - Key-value storage
  - Group settings

## 2. Backend Features
### Authentication & Authorization
- [ ] RBAC dengan spatie permission
- [ ] Admin middleware
- [ ] User management
- [ ] Role management
- [ ] Permission management

### Post Management
- [ ] CRUD operations
- [ ] Draft & publish workflow
- [ ] Featured image handling
- [ ] Rich text editor integration
- [ ] Post meta handling

### Category & Tag Management
- [ ] CRUD operations
- [ ] Hierarchical categories
- [ ] Slug generation
- [ ] Post associations

### Media Management
- [ ] File upload system
- [ ] Image optimization
- [ ] Gallery management
- [ ] File type validation

### Menu Builder
- [ ] Custom menu creation
- [ ] Menu position management
- [ ] Dynamic menu items

### Settings Management
- [ ] Site settings
- [ ] Theme options
- [ ] SEO settings

## 3. Frontend Admin Panel (React)
### Dashboard
- [ ] Statistics overview
- [ ] Recent content widgets
- [ ] Quick draft feature
- [ ] Activity log

### Post Editor
- [ ] Rich text editor integration
- [ ] Media uploader
- [ ] Category/tag selector
- [ ] SEO metadata editor
- [ ] Preview functionality

### Media Library Interface
- [ ] Grid/list view toggle
- [ ] Drag & drop upload
- [ ] Image editor
- [ ] Bulk actions
- [ ] Search & filter

### Menu Builder Interface
- [ ] Drag & drop builder
- [ ] Multiple menu locations
- [ ] Custom link support
- [ ] Menu item settings

### Settings Pages
- [ ] General settings
- [ ] User management
- [ ] Theme customization
- [ ] Plugin settings

## 4. Frontend Theme System
### Core Components
- [ ] Header customization
- [ ] Footer management
- [ ] Sidebar widgets
- [ ] Archive templates
- [ ] Single post template

### Widget System
- [ ] Recent posts widget
- [ ] Categories widget
- [ ] Tags cloud widget
- [ ] Custom widget support

### Theme Customization
- [ ] Color scheme settings
- [ ] Typography options
- [ ] Layout customization
- [ ] Custom CSS support

## 5. Additional Features
### SEO Tools
- [ ] Meta tags management
- [ ] Sitemap generation
- [ ] Permalink settings
- [ ] Social media integration

### API Development
- [ ] RESTful endpoints
- [ ] API authentication
- [ ] Rate limiting
- [ ] Documentation

### Performance
- [ ] Caching system
- [ ] Image optimization
- [ ] Lazy loading
- [ ] Database optimization

### Security
- [ ] CSRF protection
- [ ] XSS prevention
- [ ] Input validation
- [ ] Role-based access control

## 6. Testing & Documentation
### Testing
- [ ] Unit tests
- [ ] Feature tests
- [ ] API tests
- [ ] Performance testing
- [ ] Security testing

### Documentation
- [ ] API documentation
- [ ] User manual
- [ ] Developer guide
- [ ] Deployment guide

## Priority Order
1. Basic Model & Database Setup
2. Authentication & Core Admin Features
3. Post Management System
4. Media Library
5. Theme System
6. Additional Features
7. Testing & Documentation