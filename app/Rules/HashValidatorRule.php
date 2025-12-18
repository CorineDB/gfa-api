<?php

namespace App\Rules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class HashValidatorRule implements Rule
{
    private $object;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Model $object )
    {
        $this->object = $object;

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
        $model = $this->object->findByKey($value);
        
        if($model === null || $model instanceof Builder) return false;

        // Set the found model ID back into the request, at the correct attribute path
        $this->setNestedAttributeValue($attribute, $model->id);
        /*$result = $model->id;

        request()[$attribute] = $result;*/
        
        return true;
    }

    /**
     * Set the value for a nested attribute in the request data.
     *
     * @param string $attribute
     * @param mixed $value
     */
    private function setNestedAttributeValue($attribute, $value)
    {
        // Split the attribute by '.' to get the individual levels (for example, 'response_data.factuel.0.indicateurDeGouvernanceId')
        $keys = explode('.', $attribute);

        // Get the full request data and store it in a variable
        $input = request()->all();
    
        // Traverse through the keys and create the nested array path
        $current = &$input; // Reference to the root of the array

        // Traverse the keys to get the nested attribute
        foreach ($keys as $key) {
            // If the key doesn't exist, create it as an empty array
            if (!isset($current[$key])) {
                $current[$key] = [];
            }
            // Traverse deeper into the array by reference
            $current = &$current[$key];
        }

        // Set the final value
        $current = $value;
    
        // Now put the modified data back into the request
        request()->merge($input);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        
        $class_name = strtolower(str_replace('App\\Models\\','', get_class($this->object)));

        return ucfirst($class_name)." inexistant. Veuillez préciser un {$class_name} existant.";
    }
}
