<?php

namespace App\Rules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class DecodeArrayHashIdRule implements Rule
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

        if(str_contains($attribute, "s."))
        {
            $data = explode(".", $attribute);

            $permissions = request()[$data[0]];

            $permissions[$data[1]] = $model->id;

            request()[$data[0]] = $permissions;
        }

        else request()[$attribute] = $model->id;
        
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
