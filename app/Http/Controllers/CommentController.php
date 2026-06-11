<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store a new comment on an article.
     */
    public function store(Request $request, Article $article): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1'],
        ]);

        $comment = $article->comments()->create([
            'user_id' => $request->user()->id,
            'body'    => $validated['body'],
        ]);

        // Notify the article owner — but not if they are the commenter.
        if ($article->user_id !== $request->user()->id) {
            $article->load('user');
            $article->user->notify(new NewCommentNotification($comment));
        }

        return response()->json($comment, 201);
    }
}
