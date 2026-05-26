<?php

namespace App\Http\Requests;

use App\Rules\ExistingTags;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Form Request for creating a new article.
 */
class StoreArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Article::class);
    }

    /**
     * Prepare the data for validation.
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
     */
    public function rules(): array
    {
        return [
            'title'   => ['required', 'string', 'min:10', 'unique:articles,title'],
            'content' => ['required', 'string', 'min:100'],
            'tags'    => ['nullable', 'array', new ExistingTags],
            'tags.*'  => ['integer'],
            'status'  => ['sometimes', 'string', 'in:draft,published,archived'],
        ];
    }

    /**
     * Custom validation error messages.
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
