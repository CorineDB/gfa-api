<?php

namespace App\Http\Requests\commentaire;

use App\Models\Commentaire;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentaireRequest extends FormRequest
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
            'contenu' => 'required|max:255',
            'commentaireId' => ['sometimes', 'required', new HashValidatorRule(new Commentaire())]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'contenu.required' => 'Le contenu est obligatoire.',
            'commentaireId.required' => 'Veuillez préciser le commentaire parent de celui-ci.'
        ];
    }
}
