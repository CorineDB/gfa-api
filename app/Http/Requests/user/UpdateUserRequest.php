<?php

namespace App\Http\Requests\user;

use App\Models\Role;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            /*'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'contact' => 'required|string|max:12',*/
        ];
    }
}
