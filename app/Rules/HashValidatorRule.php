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

        $result = $model->id;

        request()[$attribute] = $result;
        
        return true;
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
