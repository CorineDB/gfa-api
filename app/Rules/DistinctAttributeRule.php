<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DistinctAttributeRule implements Rule
{
    protected $attribute;
    protected $deepth;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($deepth = -2)
    {
        //$this->attribute = $attribute; // The attribute to check for distinct values (e.g., 'id')
        //$this->field = $field; // The field name for error message clarity
        //dd($attribute);
        $this->deepth = $deepth; 
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
        $this->attribute = $attribute;

        // Get the input data
        $data = request()->input();

        // Get the dynamic scope based on the attribute
        $scope = $this->getScope($attribute);

        // Check how many times this value appears in the scoped data
        $count = collect(data_get($data, $scope, []))->where('id', $value)->count();

        // Ensure this value appears only once
        return $count <= 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        // Split the string by dots
        $parts = explode('.', $this->attribute);

        // Get the last part
        $lastPart = end($parts);

        return "The {$lastPart} must be distinct.";
    }

    protected function getScope($attribute)
    {
        // Split the attribute into parts
        $parts = explode('.', $attribute);

        // Identify the prefix for the scope, keeping everything up to the last segment
        array_splice($parts, $this->deepth); // Remove the last part (the actual value)

        // Return the scope as a string
        return implode('.', $parts);
    }

}
