<?php

namespace App\Rules;

use DateTime;
use Illuminate\Contracts\Validation\Rule;

class YearValidationRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $date = DateTime::createFromFormat("Y-m-d", $value);


        $year = $date->format("Y");

        return $year === (string) request("annee");
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {

        return "L'année de la date de suivie doit correspondre à l'année du suivi préciser";
    }
}
