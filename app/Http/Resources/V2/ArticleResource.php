<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * V2 Article API Resource.
 *
 * Response shape (V1 fields + additions):
 * - title
 * - content
 * - author_name
 * - published_at
 * - reading_time (minutes)
 * - comments_count (from withCount)
 * - tags (from eager-loaded relationship)
 *
 * V2 duplicates V1 fields cleanly rather than extending, because
 * API resource inheritance can create coupling issues when V1 needs
 * to evolve independently. Both versions remain fully decoupled.
 *
 * Performance: comments_count uses withCount (single aggregate query),
 * tags and user are eager-loaded — no N+1.
 */
class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ─── V1 fields ──────────────────────────────────────
            'id'              => $this->id,
            'title'           => $this->title,
            'content'         => $this->content,
            'author_name'     => $this->whenLoaded('user', fn () => $this->user->name),
            'published_at'    => $this->published_at?->toIso8601String(),

            // ─── V2 additions ───────────────────────────────────
            'reading_time'    => $this->reading_time,
            'comments_count'  => $this->whenCounted('comments'),
            'tags'            => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($tag) => [
                'id'   => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])),
        ];
    }
}
