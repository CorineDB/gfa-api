<?php

namespace App\Http\Requests\reponseAnos;

use App\Models\Ano;
use App\Models\ReponseAno;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
        return  [
            'commentaire'       => 'sometimes',
            'statut'            => 'required|int|min:-1|max:1',
            'anoId' => ['required', new HashValidatorRule(new Ano())],
            'reponseId' => ['sometimes', 'required', new HashValidatorRule(new ReponseAno())],
            //'documents'         => 'required|array|min:0',
           // 'documents.*'       => 'file|mimes:jpg,png,jpeg,webp,svg,ico|max:2048',

        ];
    }
}
