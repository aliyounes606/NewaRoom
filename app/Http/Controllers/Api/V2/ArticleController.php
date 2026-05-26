<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * V2 Article API Controller.
 */
class ArticleController extends Controller
{
    /**
     * List published articles (V2).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $articles = Article::published()
            ->with(['user', 'tags'])
            ->withCount('comments')
            ->latest('published_at')
            ->paginate($request->integer('per_page', 15));

        return ArticleResource::collection($articles);
    }

    /**
     * Show a single article (V2).
     */
    public function show(Request $request, Article $article): ArticleResource
    {
        $user = Auth::guard('sanctum')->user();

        Gate::forUser($user)->authorize('view', $article);

        $article->load(['user', 'tags'])->loadCount('comments');

        return new ArticleResource($article);
    }
}
