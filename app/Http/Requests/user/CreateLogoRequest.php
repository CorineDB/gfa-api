<?php

namespace App\Http\Requests\user;

use Illuminate\Foundation\Http\FormRequest;

class CreateLogoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("modifier-un-utilisateur") || request()->user()->hasRole("administrateur", "super-admin", "unitee-de-gestion", "organisation");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'logo'  => 'required|mimes:jpg,png,jpeg,webp,svg,ico|max:2048',
        ];
    }
}
