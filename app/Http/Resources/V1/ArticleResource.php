<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * V1 Article API Resource.
 *
 * Response shape:
 * - title
 * - content
 * - author_name
 * - published_at
 *
 * Intentionally minimal — V2 extends this with reading_time,
 * comments_count, and tags.
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
            'id'           => $this->id,
            'title'        => $this->title,
            'content'      => $this->content,
            'author_name'  => $this->whenLoaded('user', fn () => $this->user->name),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
