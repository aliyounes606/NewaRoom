<?php

namespace App\Rules;

use App\Models\Tag;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;


class ExistingTags implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute  The attribute name (e.g. "tags")
     * @param  mixed   $value      The submitted value (expected: array of tag IDs)
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail('The :attribute must be an array of tag IDs.');

            return;
        }

        // Filter to valid integer-like values
        $ids = array_filter($value, fn($id) => is_numeric($id));

        if (count($ids) !== count($value)) {
            $fail('The :attribute must contain only valid numeric IDs.');

            return;
        }

        // Single query — count how many of the submitted IDs actually exist
        $existingCount = Tag::whereIn('id', $ids)->count();

        if ($existingCount !== count($ids)) {
            $fail('One or more of the selected tags do not exist.');
        }
    }
}
