<?php

namespace App\Traits\Eloquents;

use Illuminate\Database\Eloquent\Model;

trait FilterTrait
{
    // Define the operator mapping for easy translation of filters
    protected $operatorMapping = [
        'eq'       => '=',    // Equals
        'gt'       => '>',    // Greater than
        'lt'       => '<',    // Less than
        'gte'      => '>=',   // Greater than or equal to
        'lte'      => '<=',   // Less than or equal to
        'neq'      => '!=',   // Not equal
        'like'     => 'LIKE', // Pattern matching
        'in'       => 'IN',   // Value in a list
        'not_in'   => 'NOT IN',// Value not in a list
        'null'     => 'IS NULL', // Is null check
        'not_null' => 'IS NOT NULL', // Is not null check
        'between'   => 'BETWEEN', // Value between a range (non-strict)
        'strict_between' => 'STRICT_BETWEEN', // Strict between (exclusive range)
    ];

    /**
     * Process the request and convert it to filterable conditions
     * 
     * @param array $filtersData
     * @param array $allowedFields // List of allowed fields that can be filtered
     * @return array $filters
     */
    public function formatFilters(array $filtersData, array $allowedFields = []): array
    {
        $filters = [];

        // Loop through each request parameter
        foreach ($filtersData as $field => $value) {
            // Match fields in the format: field__operator (e.g. age__gte, name__like)
            if (preg_match('/(.*)__(eq|gt|lt|gte|lte|neq|like|in|not_in|null|not_null|between|strict_between)$/', $field, $matches)) {

                $column = $matches[1]; // The field name (e.g., age)
                $operatorKey = $matches[2]; // The operator (e.g., gte)

                // Check if the field is allowed for filtering
                if (count($allowedFields) == 0 || in_array($column, $allowedFields)) {
                    $operator = $this->operatorMapping[$operatorKey]; // Map the operator to SQL equivalent

                    // Handle specific cases
                    switch ($operator) {
                        case 'LIKE':
                            $value = '%' . $value . '%'; // Add wildcards for partial matches
                            $filters[] = [$column, $operator, $value];
                            break;

                        case 'IN':
                        case 'NOT IN':
                            $value = explode(',', $value); // Convert comma-separated string to an array
                            $filters[] = [$column, $operator, $value];
                            break;

                        case 'BETWEEN':
                            $range = explode(',', $value); // Expect two values for the range
                            if (count($range) == 2) {
                                $filters[] = [$column, '>=', $range[0]];
                                $filters[] = [$column, '<=', $range[1]];
                            }
                            break;

                        case 'STRICT_BETWEEN':
                            $range = explode(',', $value);
                            if (count($range) == 2) {
                                $filters[] = [$column, '>', $range[0]];
                                $filters[] = [$column, '<', $range[1]];
                            }
                            break;

                        case 'IS NULL':
                        case 'IS NOT NULL':
                            $filters[] = [$column, $operator];
                            break;

                        default:
                            $filters[] = [$column, $operator, $value]; // For regular comparisons
                            break;
                    }
                }
            }
        }

        return $filters; // Return the array of filter conditions
    }

    /**
     * Cast ...
     * 
     * @return mixed $value
     */
    protected function castValue(Model $model, string $attribute, string $value)
    {
        $casts = $model->getCasts();
        
        if (array_key_exists($attribute, $casts)) {
            switch ($casts[$attribute]) {
                case 'integer':
                    return (int)$value;
                case 'boolean':
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                case 'float':
                    return (float)$value;
                default:
                    return $value; // string or other types
            }
        }
        
        return $value; // default
    }
}
