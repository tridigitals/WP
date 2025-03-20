<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

abstract class BaseTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;
    protected string $testDisk = 'public';

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();

        // Configure test storage
        Storage::fake($this->testDisk);
    }

    /**
     * Create a test image file.
     */
    protected function createTestImage(string $name = 'test.jpg', int $width = 1200, int $height = 800): UploadedFile
    {
        return UploadedFile::fake()->image($name, $width, $height);
    }

    /**
     * Create a test document.
     */
    protected function createTestDocument(string $name = 'test.pdf', int $kilobytes = 1024): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kilobytes);
    }

    /**
     * Create a full post with relationships.
     */
    protected function createPost(array $attributes = []): Post
    {
        return Post::factory()
            ->for($this->admin)
            ->for(Category::factory())
            ->has(Tag::factory()->count(2))
            ->withMeta()
            ->withFeaturedImage()
            ->create($attributes);
    }

    /**
     * Create a category hierarchy.
     */
    protected function createCategoryHierarchy(int $depth = 2, int $children = 2): Category
    {
        return Category::factory()
            ->withFullHierarchy()
            ->create();
    }

    /**
     * Create test media items.
     */
    protected function createMediaItems(int $count = 3): void
    {
        Media::factory()
            ->count($count)
            ->for($this->admin)
            ->create();
    }

    /**
     * Assert response is paginated.
     */
    protected function assertResponseIsPaginated(TestResponse $response): void
    {
        $response->assertJsonStructure([
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total']
        ]);
    }

    /**
     * Assert model has meta information.
     */
    protected function assertHasMeta($model): void
    {
        $this->assertNotNull($model->meta);
        $this->assertNotNull($model->meta->meta_title);
        $this->assertNotNull($model->meta->meta_description);
    }

    /**
     * Assert file exists in storage.
     */
    protected function assertFileExistsInStorage(string $path): void
    {
        Storage::disk($this->testDisk)->assertExists($path);
    }

    /**
     * Assert file does not exist in storage.
     */
    protected function assertFileNotExistsInStorage(string $path): void
    {
        Storage::disk($this->testDisk)->assertMissing($path);
    }

    /**
     * Create a post with specific status.
     */
    protected function createPostWithStatus(string $status, array $attributes = []): Post
    {
        return $this->createPost(array_merge([
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null
        ], $attributes));
    }

    /**
     * Assert post has required relationships.
     */
    protected function assertPostHasRelationships(Post $post): void
    {
        $this->assertNotNull($post->category);
        $this->assertNotEmpty($post->tags);
        $this->assertHasMeta($post);
        $this->assertNotNull($post->user);
    }

    /**
     * Assert model is properly ordered.
     */
    protected function assertModelIsOrdered(string $model, string $column = 'order'): void
    {
        $items = $model::orderBy($column)->pluck($column)->toArray();
        $this->assertEquals(range(1, count($items)), $items);
    }

    /**
     * Assert response has error message.
     */
    protected function assertResponseHasError(TestResponse $response, string $field): void
    {
        $response->assertStatus(302);
        $response->assertSessionHasErrors($field);
    }

    /**
     * Assert response is successful with message.
     */
    protected function assertResponseIsSuccessfulWithMessage(TestResponse $response, string $message): void
    {
        $response->assertStatus(200);
        $response->assertSessionHas('success', $message);
    }

    /**
     * Get valid model data.
     */
    protected function getValidModelData(string $model): array
    {
        return match($model) {
            'post' => [
                'title' => 'Test Post',
                'content' => 'Test content',
                'category_id' => Category::factory()->create()->id,
                'status' => 'draft'
            ],
            'category' => [
                'name' => 'Test Category',
                'description' => 'Test description'
            ],
            'tag' => [
                'name' => 'Test Tag'
            ],
            default => []
        };
    }

    /**
     * Assert model was created with data.
     */
    protected function assertModelWasCreated(string $model, array $data): void
    {
        $this->assertDatabaseHas((new $model)->getTable(), $data);
    }

    /**
     * Assert model was updated with data.
     */
    protected function assertModelWasUpdated(string $model, array $data): void
    {
        $this->assertDatabaseHas((new $model)->getTable(), $data);
    }

    /**
     * Assert model was deleted.
     */
    protected function assertModelWasDeleted(string $model, int $id): void
    {
        $this->assertDatabaseMissing((new $model)->getTable(), ['id' => $id]);
    }
}