<?php

namespace App\Http\Requests;

use App\Models\Programme;
use App\Models\PtabScope;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class PtabRevisionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->user()->hasPermissionTo("faire-revision-ptab") || request()->user()->hasRole("unitee-de-gestion");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'programmeId' => ['required', new HashValidatorRule(new Programme())]
        ];
    }
}
