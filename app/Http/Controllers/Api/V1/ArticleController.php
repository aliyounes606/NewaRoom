<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * V1 Article API Controller.
 */
class ArticleController extends Controller
{
    /**
     * List published articles (V1).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $articles = Article::published()
            ->with('user')
            ->latest('published_at')
            ->paginate($request->integer('per_page', 15));

        return ArticleResource::collection($articles);
    }

    /**
     * Show a single article (V1).
     */
    public function show(Request $request, Article $article): ArticleResource
    {
        $user = Auth::guard('sanctum')->user();

        Gate::forUser($user)->authorize('view', $article);

        $article->load('user');

        return new ArticleResource($article);
    }
}
