<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MetaRequest;
use App\Models\Meta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MetaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Meta::query()
            ->when($request->type, function ($query, $type) {
                $query->where('metable_type', $type);
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('meta_title', 'like', "%{$search}%")
                        ->orWhere('meta_description', 'like', "%{$search}%")
                        ->orWhere('meta_keywords', 'like', "%{$search}%");
                });
            });

        $meta = $query->paginate($request->input('per_page', 15));

        return response()->json($meta);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MetaRequest $request): JsonResponse
    {
        try {
            $meta = Meta::create($request->validated());

            return response()->json([
                'message' => 'Meta information created successfully',
                'meta' => $meta
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating meta information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Meta $meta): JsonResponse
    {
        return response()->json([
            'meta' => $meta,
            'html_tags' => $meta->generateMetaTags(),
            'schema_markup' => $meta->getSchemaMarkup()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MetaRequest $request, Meta $meta): JsonResponse
    {
        try {
            $meta->update($request->validated());

            return response()->json([
                'message' => 'Meta information updated successfully',
                'meta' => $meta
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating meta information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Meta $meta): JsonResponse
    {
        try {
            $meta->delete();

            return response()->json([
                'message' => 'Meta information deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting meta information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze SEO for the given meta information.
     */
    public function analyze(Meta $meta): JsonResponse
    {
        $analysis = [
            'title' => $this->analyzeTitleLength($meta->meta_title),
            'description' => $this->analyzeDescriptionLength($meta->meta_description),
            'keywords' => $this->analyzeKeywords($meta->meta_keywords),
            'og_tags' => $this->analyzeOpenGraphTags($meta),
            'twitter_tags' => $this->analyzeTwitterTags($meta),
            'schema' => $this->analyzeSchemaMarkup($meta->schema_markup),
        ];

        return response()->json([
            'analysis' => $analysis,
            'score' => $this->calculateSeoScore($analysis)
        ]);
    }

    /**
     * Bulk update meta information.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'meta' => ['required', 'array'],
            'meta.*.id' => ['required', 'exists:meta,id'],
            'meta.*.meta_title' => ['nullable', 'string', 'max:60'],
            'meta.*.meta_description' => ['nullable', 'string', 'max:160']
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->meta as $metaData) {
                Meta::where('id', $metaData['id'])->update(array_filter($metaData));
            }

            DB::commit();

            return response()->json([
                'message' => 'Meta information updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating meta information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate meta tags for a specific model.
     */
    public function generateForModel(Request $request): JsonResponse
    {
        $request->validate([
            'model_type' => ['required', 'string'],
            'model_id' => ['required', 'integer']
        ]);

        try {
            $modelClass = $request->model_type;
            $model = $modelClass::findOrFail($request->model_id);

            // Generate meta information based on model content
            $meta = [
                'meta_title' => $model->title ?? $model->name,
                'meta_description' => $this->generateDescription($model),
                'og_title' => $model->title ?? $model->name,
                'og_description' => $this->generateDescription($model),
                'schema_markup' => $this->generateSchemaMarkup($model)
            ];

            return response()->json([
                'meta' => $meta,
                'preview' => Meta::renderMetaTags($meta)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating meta information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Private analysis methods
    private function analyzeTitleLength(?string $title): array
    {
        $length = strlen($title ?? '');
        return [
            'length' => $length,
            'optimal' => $length >= 30 && $length <= 60,
            'message' => $this->getTitleLengthMessage($length)
        ];
    }

    private function analyzeDescriptionLength(?string $description): array
    {
        $length = strlen($description ?? '');
        return [
            'length' => $length,
            'optimal' => $length >= 120 && $length <= 160,
            'message' => $this->getDescriptionLengthMessage($length)
        ];
    }

    private function analyzeKeywords(?string $keywords): array
    {
        if (!$keywords) {
            return ['message' => 'No keywords provided'];
        }

        $keywordArray = array_map('trim', explode(',', $keywords));
        return [
            'count' => count($keywordArray),
            'optimal' => count($keywordArray) >= 3 && count($keywordArray) <= 7,
            'message' => 'Found ' . count($keywordArray) . ' keywords'
        ];
    }

    private function analyzeOpenGraphTags(Meta $meta): array
    {
        $required = ['og_title', 'og_description', 'og_image'];
        $missing = array_filter($required, fn($tag) => empty($meta->$tag));
        
        return [
            'complete' => empty($missing),
            'missing' => $missing
        ];
    }

    private function analyzeTwitterTags(Meta $meta): array
    {
        $required = ['twitter_card', 'twitter_title', 'twitter_description'];
        $missing = array_filter($required, fn($tag) => empty($meta->$tag));
        
        return [
            'complete' => empty($missing),
            'missing' => $missing
        ];
    }

    private function analyzeSchemaMarkup(?array $schema): array
    {
        if (!$schema) {
            return ['message' => 'No Schema.org markup provided'];
        }

        return [
            'valid' => isset($schema['@context']) && isset($schema['@type']),
            'type' => $schema['@type'] ?? null
        ];
    }

    private function calculateSeoScore(array $analysis): int
    {
        $score = 0;
        
        if ($analysis['title']['optimal']) $score += 20;
        if ($analysis['description']['optimal']) $score += 20;
        if ($analysis['keywords']['optimal']) $score += 20;
        if ($analysis['og_tags']['complete']) $score += 20;
        if ($analysis['twitter_tags']['complete']) $score += 20;

        return $score;
    }

    private function getTitleLengthMessage(int $length): string
    {
        if ($length === 0) return 'Title is missing';
        if ($length < 30) return 'Title is too short (minimum 30 characters)';
        if ($length > 60) return 'Title is too long (maximum 60 characters)';
        return 'Title length is optimal';
    }

    private function getDescriptionLengthMessage(int $length): string
    {
        if ($length === 0) return 'Description is missing';
        if ($length < 120) return 'Description is too short (minimum 120 characters)';
        if ($length > 160) return 'Description is too long (maximum 160 characters)';
        return 'Description length is optimal';
    }
}
