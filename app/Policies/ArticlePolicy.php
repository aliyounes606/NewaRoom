<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;


class ArticlePolicy
{
    /**
     * Determine whether the user can view a list of articles.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific article.
     */
    public function view(?User $user, Article $article): bool
    {
        if ($article->status === 'published') {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $article->user_id;
    }

    /**
     * Determine whether the user can create articles.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isWriter();
    }

    /**
     * Determine whether the user can update the article.
     */
    public function update(User $user, Article $article): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isWriter() && $user->id === $article->user_id;
    }

    /**
     * Determine whether the user can delete the article.
     */
    public function delete(User $user, Article $article): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isWriter() && $user->id === $article->user_id;
    }
}
