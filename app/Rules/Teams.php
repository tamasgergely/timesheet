<?php

namespace App\Rules;

use Closure;
use App\Models\Team;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Validation\ValidationRule;

class Teams implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $teamIds = Arr::pluck($value, 'value');

        if (Team::whereIn('id',$teamIds)->count() !== count($value)){
            $fail('The team doesn\'t exist.');
        }
    }
}
