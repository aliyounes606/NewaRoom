<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Thin controller for Article resources.
 */
class ArticleController extends Controller
{
    public function __construct(
        protected ArticleService $articleService,
    ) {}

    /**
     * List published articles (public endpoint).
     */
    public function index(Request $request): JsonResponse
    {
        $articles = $this->articleService->listPublished(
            perPage: $request->integer('per_page', 15),
        );

        return response()->json($articles);
    }

    /**
     * Show a single article.
     */
    public function show(Request $request, Article $article): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();

        Gate::forUser($user)->authorize('view', $article);

        $article->load(['user', 'tags', 'comments']);

        return response()->json($article);
    }

    /**
     * Store a new article.
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $article = $this->articleService->create(
            data: [
                'user_id' => $request->user()->id,
                'title'   => $validated['title'],
                'content' => $validated['content'],
                'status'  => $validated['status'] ?? 'draft',
            ],
            tagIds: $validated['tags'] ?? [],
        );

        return response()->json($article, 201);
    }

    /**
     * Update an existing article.
     */
    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        $validated = $request->validated();

        $updatedArticle = $this->articleService->update(
            article: $article,
            data: collect($validated)->only(['title', 'content', 'status'])->toArray(),
            tagIds: $validated['tags'] ?? null,
        );

        return response()->json($updatedArticle);
    }

    /**
     * Delete an article.
     */
    public function destroy(Article $article): JsonResponse
    {
        $this->authorize('delete', $article);

        $this->articleService->delete($article);

        return response()->json(['message' => 'Article deleted.'], 200);
    }
}
