<?php

namespace App\Http\Requests\utilisateur;

use Illuminate\Foundation\Http\FormRequest;

class CreatePhotoRequest extends FormRequest
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
            'photo'  => 'required|mimes:jpg,png,jpeg,webp,svg,ico|max:2048',
        ];
    }
}
