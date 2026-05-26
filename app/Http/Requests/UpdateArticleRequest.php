<?php

namespace App\Http\Requests;

use App\Rules\ExistingTags;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Form Request for updating an existing article.
 *
 * Mirrors StoreArticleRequest with two key differences:
 * 1. authorize() delegates to ArticlePolicy::update() (ownership check)
 * 2. The title uniqueness rule ignores the current article's ID
 */
class UpdateArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to ArticlePolicy::update() — checks that the user
     * is admin or the article's author.
     */
    public function authorize(): bool
    {
        $article = $this->route('article');

        return $this->user()->can('update', $article);
    }

    /**
     * Prepare the data for validation.
     *
     * Same cleaning behavior as StoreArticleRequest:
     * - Trims title whitespace
     * - Normalizes multiple consecutive spaces
     * - Applies title-case capitalization
     *
     * Content is NOT manipulated to avoid damaging intentional formatting.
     */
    protected function prepareForValidation(): void
    {
        $mergeData = [];

        if ($this->has('title')) {
            $title = $this->input('title');
            $title = trim($title);
            $title = preg_replace('/\s+/', ' ', $title);
            $title = Str::title($title);

            $mergeData['title'] = $title;
        }

        if (! empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The title uniqueness rule uses `ignore` to exclude the current
     * article's ID — otherwise updating an article without changing its
     * title would fail validation.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $articleId = $this->route('article')?->id ?? $this->route('article');

        return [
            'title'   => ['sometimes', 'required', 'string', 'min:10', 'unique:articles,title,' . $articleId],
            'content' => ['sometimes', 'required', 'string', 'min:100'],
            'tags'    => ['nullable', 'array', new ExistingTags],
            'tags.*'  => ['integer'],
            'status'  => ['sometimes', 'string', 'in:draft,published,archived'],
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'  => 'The article title is required.',
            'title.min'       => 'The article title must be at least 10 characters.',
            'title.unique'    => 'An article with this title already exists.',
            'content.required' => 'The article content is required.',
            'content.min'     => 'The article content must be at least 100 characters.',
            'status.in'       => 'The status must be one of: draft, published, or archived.',
        ];
    }
}
