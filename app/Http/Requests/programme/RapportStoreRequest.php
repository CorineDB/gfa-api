<?php

namespace App\Http\Requests\programme;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RapportStoreRequest extends FormRequest
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
            'rapport' => 'required',
            'nom' => 'required',
            'document' => ["sometimes", "file", 'mimes:txt,doc,docx,xls,csv,xlsx,ppt,pdf,jpg,png,jpeg,mp3,wav,mp4,mov,avi,mkv', "max:20480"],

        ];
    }

}
