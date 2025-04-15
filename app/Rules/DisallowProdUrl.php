<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class DisallowProdUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if (Str::contains(Str::lower($value), ['prd', 'prod', 'pd'])) {
            $fail('Production URLs are not allowed');
        }
    }
}
