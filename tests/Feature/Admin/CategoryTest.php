<?php

namespace Tests\Feature\Admin;

use Tests\Feature\BaseTestCase;
use App\Models\Category;

class CategoryTest extends BaseTestCase
{
    /**
     * Test admin can view categories list.
     */
    public function test_admin_can_view_categories_list(): void
    {
        $categories = Category::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
    }

    /**
     * Test admin can create category.
     */
    public function test_admin_can_create_category(): void
    {
        $categoryData = $this->getValidModelData('category');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $categoryData);

        $response->assertStatus(302);
        $this->assertModelWasCreated(Category::class, [
            'name' => $categoryData['name']
        ]);
    }

    /**
     * Test admin can update category.
     */
    public function test_admin_can_update_category(): void
    {
        $category = Category::factory()->create();
        $updatedData = [
            'name' => 'Updated Category',
            'slug' => 'updated-category',
            'description' => 'Updated description'
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), $updatedData);

        $response->assertStatus(302);
        $this->assertModelWasUpdated(Category::class, [
            'id' => $category->id,
            'name' => 'Updated Category'
        ]);
    }

    /**
     * Test admin can delete category.
     */
    public function test_admin_can_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertStatus(302);
        $this->assertModelWasDeleted(Category::class, $category->id);
    }

    /**
     * Test admin can reorder categories.
     */
    public function test_admin_can_reorder_categories(): void
    {
        $categories = Category::factory()->count(3)->create();
        
        $reorderData = [
            'categories' => $categories->map(function ($category, $index) {
                return [
                    'id' => $category->id,
                    'order' => $index + 1
                ];
            })->toArray()
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.reorder'), $reorderData);

        $response->assertStatus(200);
        $this->assertModelIsOrdered(Category::class);
    }

    /**
     * Test non-admin cannot access categories.
     */
    public function test_non_admin_cannot_access_categories(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.categories.index'));

        $response->assertStatus(403);
    }

    /**
     * Test category validation rules.
     */
    public function test_category_validation(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), []);

        $this->assertResponseHasError($response, 'name');
    }

    /**
     * Test category hierarchy management.
     */
    public function test_category_hierarchy_management(): void
    {
        $parent = $this->createCategoryHierarchy();
        
        $this->assertNotNull($parent->children);
        $this->assertGreaterThan(0, $parent->children->count());
        
        // Test moving category
        $child = $parent->children->first();
        $newParent = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.categories.move', $child), [
                'parent_id' => $newParent->id
            ]);

        $response->assertStatus(200);
        $this->assertEquals($newParent->id, $child->fresh()->parent_id);
    }

    /**
     * Test category with meta information.
     */
    public function test_category_with_meta(): void
    {
        $category = Category::factory()
            ->withMeta()
            ->create();

        $this->assertHasMeta($category);
    }

    /**
     * Test category posts relationship.
     */
    public function test_category_posts_relationship(): void
    {
        $category = Category::factory()
            ->withPosts(3)
            ->create();

        $this->assertEquals(3, $category->posts()->count());
        $this->assertEquals(3, $category->meta_data['posts_count']);
    }
}