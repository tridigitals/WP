<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()
            ->with(['parent', 'children', 'meta'])
            ->when($request->search, function (Builder $query, string $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->parent_id, function (Builder $query, $parentId) {
                if ($parentId === 'null') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $parentId);
                }
            });

        // Handle different listing types
        if ($request->type === 'tree') {
            $categories = $query->whereNull('parent_id')->with('descendants')->get();
        } elseif ($request->type === 'flat') {
            $categories = $query->ordered()->get();
        } else {
            $categories = $query->ordered()->paginate($request->input('per_page', 15));
        }

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $category = Category::create($request->validated());

            // Create meta information if provided
            if ($request->has('meta')) {
                $category->meta()->create($request->input('meta'));
            }

            DB::commit();

            $category->load(['parent', 'children', 'meta']);

            return response()->json([
                'message' => 'Category created successfully',
                'category' => $category
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        $category->load(['parent', 'children', 'meta', 'posts']);

        // Add breadcrumb trail
        $breadcrumbs = $category->ancestors()->pluck('name', 'id')->all();
        $breadcrumbs[$category->id] = $category->name;

        return response()->json([
            'category' => $category,
            'breadcrumbs' => $breadcrumbs,
            'post_count' => $category->posts()->count()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        try {
            DB::beginTransaction();

            $category->update($request->validated());

            // Update meta information if provided
            if ($request->has('meta')) {
                $category->meta()->updateOrCreate(
                    ['metable_id' => $category->id, 'metable_type' => Category::class],
                    $request->input('meta')
                );
            }

            DB::commit();

            $category->load(['parent', 'children', 'meta']);

            return response()->json([
                'message' => 'Category updated successfully',
                'category' => $category
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Handle children categories
            if ($category->children()->exists()) {
                // Move children to parent category if exists
                $category->children()->update(['parent_id' => $category->parent_id]);
            }

            // Update posts to remove category
            $category->posts()->update(['category_id' => null]);

            // Delete meta
            $category->meta()->delete();
            
            // Delete the category
            $category->delete();

            DB::commit();

            return response()->json([
                'message' => 'Category deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error deleting category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder categories.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'exists:categories,id'],
            'categories.*.order' => ['required', 'integer', 'min:0']
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->categories as $item) {
                Category::where('id', $item['id'])->update(['order' => $item['order']]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Categories reordered successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error reordering categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move category to new parent.
     */
    public function move(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id']
        ]);

        try {
            // Prevent moving to own descendant
            if ($request->parent_id) {
                $parent = Category::find($request->parent_id);
                if ($parent && $parent->isDescendantOf($category)) {
                    return response()->json([
                        'message' => 'Cannot move category to its own descendant'
                    ], 422);
                }
            }

            $category->update(['parent_id' => $request->parent_id]);

            return response()->json([
                'message' => 'Category moved successfully',
                'category' => $category->load(['parent', 'children'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error moving category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
