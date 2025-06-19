<?php

namespace App\Http\Requests\enquetes_de_gouvernance\evaluation_de_gouvernance\soumissions_factuel;

use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance;
use App\Models\enquetes_de_gouvernance\FormulaireFactuelDeGouvernance;
use App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance;
use App\Models\enquetes_de_gouvernance\QuestionFactuelDeGouvernance;
use App\Models\enquetes_de_gouvernance\SoumissionFactuel;
use App\Models\Organisation;
use App\Models\Programme;
use App\Models\SourceDeVerification;
use App\Rules\HashValidatorRule;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SoumissionFactuelValidationRequest extends FormRequest
{
    protected $formulaireCache = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (is_string($this->evaluation_de_gouvernance)) {
            $this->evaluation_de_gouvernance = EvaluationDeGouvernance::findByKey($this->evaluation_de_gouvernance);
        }

        return (request()->user()->hasPermissionTo("valider-une-soumission") || request()->user()->hasRole("unitee-de-gestion", "organisation")) && $this->evaluation_de_gouvernance->statut == 0;

        //return request()->user()->hasRole("unitee-de-gestion") && $this->evaluation_de_gouvernance->statut;

        return (request()->user()->hasRole("unitee-de-gestion") || request()->user()->hasRole("organisation")) && $this->evaluation_de_gouvernance->statut == 0;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'programmeId'   => [Rule::requiredIf(!auth()->check()), new HashValidatorRule(new Programme())],
            'soumissionId'   => ['required', new HashValidatorRule(new SoumissionFactuel())],
            'organisationId'   => [Rule::requiredIf(request()->user()->hasRole("unitee-de-gestion")), new HashValidatorRule(new Organisation())],
            'formulaireDeGouvernanceId'   => [
                "required",
                new HashValidatorRule(new FormulaireFactuelDeGouvernance()),
                function ($attribute, $value, $fail) {

                    // Check if formulaireDeGouvernanceId exists within the related formulaire_factuel_de_gouvernance
                    $formulaire = $this->evaluation_de_gouvernance->formulaires_factuel_de_gouvernance()
                        ->where('formulaireFactuelId', request()->input('formulaireDeGouvernanceId'))
                        ->first();

                    if ($formulaire == null) $fail('The selected formulaire de gouvernance ID is invalid or not associated with this evaluation.');

                    $this->formulaireCache = $formulaire;

                    if (($soumission = $this->evaluation_de_gouvernance->soumissionsFactuel->where('organisationId', request()->input('organisationId') ?? auth()->user()->profilable->id)->where('formulaireFactuelId', request()->input('formulaireDeGouvernanceId'))->first()) && $soumission->statut === true) {
                        $fail('La soumission a déjà été validée.');
                    }
                }
            ],

            'factuel'                               => ['required', 'array', 'min:2'],

            'factuel.comite_members'                => ['required', 'array', 'min:1'],
            'factuel.comite_members.*.nom'          => ['required', 'string'],
            'factuel.comite_members.*.prenom'       => ['required', 'string'],
            'factuel.comite_members.*.contact'      => ['required', 'distinct', 'numeric', 'digits_between:8,24'],
            'factuel.response_data'                 => [
                "required",
                'array',
                function ($attribute, $value, $fail) {

                    if (count($value) < $this->getCountOfQuestionsOfAFormular()) {
                        $fail("Veuillez remplir tout le formulaire.");
                    }
                }
            ],
            'factuel.response_data.*.questionId'      => [
                "required",
                'distinct',
                new HashValidatorRule(new QuestionFactuelDeGouvernance()),
                function ($attribute, $value, $fail) {

                    if ($this->formulaireCache) {
                        $question = QuestionFactuelDeGouvernance::where("formulaireFactuelId", $this->formulaireCache->id)->findByKey($value)->exists();
                        if (!$question) {
                            // Fail validation if no response options are available
                            $fail("Cet Indicateur n'existe pas.");
                        }
                    }
                }
            ],
            'factuel.response_data.*.optionDeReponseId'   => ["required", new HashValidatorRule(new OptionDeReponseGouvernance()), function ($attribute, $value, $fail) {
                /**
                 * Check if the given optionDeReponseId is part of the IndicateurDeGouvernance's options_de_reponse
                 *
                 * If the provided optionDeReponseId is not valid, fail the validation
                 */
                if ($this->formulaireCache) {
                    if (!($this->formulaireCache->options_de_reponse()->where('optionId', request()->input($attribute))->exists())) {
                        $fail('The selected option is invalid for the given formulaire.');
                    }
                }
            }],
            'factuel.response_data.*.sourceDeVerificationId'        => [
            function ($attribute, $value, $fail) {

                if (request()->input('soumissionId') != null) {

                    if ($this->formulaireCache) {

                        // Step 1: Use preg_match to extract the index
                        preg_match('/factuel.response_data\.(\d+)\.sourceDeVerificationId/', $attribute, $matches);

                        // Step 2: Check if the index is found
                        $index = $matches[1] ?? null; // Get the index if it exists

                        $optionDeReponseId = null;
                        $formOption = null;

                        // Step 3: Retrieve the questionId from the request input based on the index
                        if ($index !== null) {
                            $optionDeReponseId = request()->input('factuel.response_data.*.optionDeReponseId')[$index] ?? null;

                            $formOption = $this->formulaireCache->options_de_reponse()->wherePivot('optionId', $optionDeReponseId)->first();

                        } else {
                            $fail("La question introuvable.");
                        }

                        $sourceDeVerification = request()->input('factuel.response_data.*.sourceDeVerification')[$index];

                        if ($formOption) {
                            if ((empty($sourceDeVerification) && empty(request()->input($attribute))) && $formOption->pivot->preuveIsRequired == 1) {
                                $fail("La source de verification est requise.");
                            }
                            else{
                                new HashValidatorRule(new SourceDeVerification());
                            }
                        }
                    } else {
                        $fail("La source de verification est requise.");
                    }
                }
            }],
            'factuel.response_data.*.sourceDeVerification'          => [
            /* function ($attribute, $value, $fail) {

                if (request()->input('soumissionId') != null) {

                    if ($this->formulaireCache) {

                        // Step 1: Use preg_match to extract the index
                        preg_match('/factuel.response_data\.(\d+)\.sourceDeVerification/', $attribute, $matches);

                        // Step 2: Check if the index is found
                        $index = $matches[1] ?? null; // Get the index if it exists

                        // Step 3: Retrieve the questionId from the request input based on the index
                        if ($index !== null) {
                            $questionId = request()->input('factuel.response_data.*.questionId')[$index] ?? null;
                        } else {
                            $fail("La question introuvable.");
                        }

                        $question = QuestionFactuelDeGouvernance::where("formulaireFactuelId", $this->formulaireCache->id)->findByKey($questionId)->first();

                        if (!$question) {
                            // Fail validation if no response options are available
                            $fail("Cet Indicateur n'existe pas.");
                        }

                        $reponse = $question->reponses()->where('soumissionId', request()->input('soumissionId'))->first();

                        $sourceDeVerificationId = request()->input('factuel.response_data.*.sourceDeVerificationId')[$index];

                        if ($reponse) {
                            if ((empty($sourceDeVerificationId) && empty(request()->input($attribute))) && $reponse->preuveIsRequired) {
                                $fail("La source de verification est requise.");
                            }
                        }
                    } else {
                        $fail("La source de verification est requise.");
                    }
                }
            } */],


            'factuel.response_data.*.preuves'                       => [
               "sometimes",
                function ($attribute, $value, $fail) {

                    if (request()->input('soumissionId') != null) {

                        if ($this->formulaireCache) {

                            // Step 1: Use preg_match to extract the index
                            preg_match('/factuel.response_data\.(\d+)\.preuves/', $attribute, $matches);

                            // Step 2: Check if the index is found
                            $index = $matches[1] ?? null; // Get the index if it exists

                            // Step 3: Retrieve the questionId from the request input based on the index
                            if ($index !== null) {
                                $questionId = request()->input('factuel.response_data.*.questionId')[$index] ?? null;
                            } else {
                                $fail("La question introuvable.");
                            }

                            $question = QuestionFactuelDeGouvernance::where("formulaireFactuelId", $this->formulaireCache->id)->findByKey($questionId)->first();

                            if (!$question) {
                                // Fail validation if no response options are available
                                $fail("Cet Indicateur n'existe pas.");
                            }

                            $reponse = $question->reponses()->where('soumissionId', request()->input('soumissionId'))->first();

                            dd($question);

                            if ($reponse) {
                                if ((!$reponse->preuves_de_verification()->count() && empty(request()->input($attribute))) && $reponse->preuveIsRequired) {
                                    $fail("La preuve est required.");
                                }
                            } else {

                                if (empty(request()->input($attribute))) {
                                    $fail("La preuve est required.");
                                }
                            }
                        } else {

                            $fail("La preuve est required.");
                        }
                    }
                },
                "array",
                "min:1"
            ],
            'factuel.response_data.*.preuves.*'                     => ["file", "mimes:doc,docx,xls,csv,xlsx,ppt,pdf,jpg,png,jpeg,mp3,wav,mp4,mov,avi,mkv", /* "mimetypes:application/pdf,application/msword,application/vnd.ms-excel,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/jpeg,image/png,audio/mpeg,audio/wav,video/mp4,video/quicktime,video/x-msvideo,video/x-matroska", */ "max:20480"],
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
            'programmeId.required' => 'Le champ programme est requis si vous n\'êtes pas authentifié.',
            'soumissionId.exists' => 'La soumission sélectionnée est invalide.',
            'organisationId.required' => 'Le champ organisation est requis pour les utilisateurs ayant le rôle "unitee-de-gestion".',
            'formulaireDeGouvernanceId.required' => 'Le champ formulaire de gouvernance est requis.',

            'factuel.required' => 'Les données factuelles sont requises.',
            'factuel.array' => 'Le champ factuel doit être un tableau.',

            'factuel.comite_members.required' => 'Le comité doit contenir au moins un membre.',
            'factuel.comite_members.array' => 'Le comité doit être sous forme de tableau.',
            'factuel.comite_members.*.nom.required' => 'Le nom du membre est requis.',
            'factuel.comite_members.*.prenom.required' => 'Le prénom du membre est requis.',
            'factuel.comite_members.*.contact.required' => 'Le contact du membre est requis.',
            'factuel.comite_members.*.contact.distinct' => 'Chaque contact doit être unique.',
            'factuel.comite_members.*.contact.numeric' => 'Le contact doit être un numéro valide.',
            'factuel.comite_members.*.contact.digits_between' => 'Le contact doit comporter entre 8 et 24 chiffres.',

            'factuel.response_data.required' => 'Veuillez remplir tout le formulaire factuel.',
            'factuel.response_data.array' => 'Les réponses doivent être sous forme de tableau.',

            'factuel.response_data.*.questionId.required' => 'L\'ID de la question est requis.',
            'factuel.response_data.*.questionId.distinct' => 'Chaque question doit être unique.',
            'factuel.response_data.*.questionId.exists' => 'Cette question n\'existe pas.',

            'factuel.response_data.*.optionDeReponseId.required' => 'Veuillez sélectionner une option de réponse.',
            'factuel.response_data.*.optionDeReponseId.exists' => 'L\'option sélectionnée est invalide.',

            'factuel.response_data.*.sourceDeVerificationId.required' => 'Veuillez sélectionner une source de vérification.',
            'factuel.response_data.*.sourceDeVerification.required' => 'Le champ source de vérification est requis.',

            'factuel.response_data.*.preuves.required' => 'Veuillez fournir une preuve de vérification.',
            'factuel.response_data.*.preuves.array' => 'Les preuves doivent être sous forme de tableau.',
            'factuel.response_data.*.preuves.min' => 'Au moins une preuve est requise.',
            'factuel.response_data.*.preuves.*.file' => 'Chaque preuve doit être un fichier valide.',
            'factuel.response_data.*.preuves.*.mimes' => 'Les fichiers doivent être au format : doc, docx, xls, csv, xlsx, ppt, pdf, jpg, png, jpeg, mp3, wav, mp4, mov, avi, mkv.',
            'factuel.response_data.*.preuves.*.max' => 'Chaque fichier ne doit pas dépasser 20 Mo.',

            'perception.required' => 'Veuillez remplir tout le formulaire de perception.',
            'perception.array' => 'Les réponses de perception doivent être sous forme de tableau.',

            'perception.categorieDeParticipant.required' => 'La catégorie de participant est requise.',
            'perception.categorieDeParticipant.in' => 'La catégorie de participant doit être parmi : membre de conseil d\'administration, employé association, membre association, partenaire.',

            'perception.sexe.required' => 'Le sexe est requis.',
            'perception.sexe.in' => 'Le sexe doit être soit masculin, soit féminin.',

            'perception.age.required' => 'L\'âge est requis.',
            'perception.age.in' => 'L\'âge doit être soit <35, soit >35.',

            'perception.response_data.*.questionId.required' => 'L\'ID de la question est requis.',
            'perception.response_data.*.questionId.distinct' => 'Chaque question doit être unique.',
            'perception.response_data.*.questionId.exists' => 'Cette question opérationnelle n\'existe pas.',

            'perception.response_data.*.optionDeReponseId.required' => 'Veuillez sélectionner une option de réponse.',
            'perception.response_data.*.optionDeReponseId.exists' => 'L\'option sélectionnée est invalide.',

            // Custom messages for the 'nom' field
            'nom.required'      => 'Le champ nom est obligatoire.',
            'nom.max'           => 'Le nom ne doit pas dépasser 255 caractères.',
            'nom.unique'        => 'Ce nom est déjà utilisé dans les résultats.',

            // Custom messages for the 'description' field
            'description.max'   => 'La description ne doit pas dépasser 255 caractères.',

            // Custom messages for the 'principeDeGouvernanceId' field
            'principeDeGouvernanceId.required' => 'Le champ principe de gouvernance est obligatoire.',
        ];
    }


    /**
     * Returns the number of questions of the formulaire de gouvernance
     * stored in the formulaireCache attribute
     *
     * @return int
     */
    private function getCountOfQuestionsOfAFormular()
    {
        return $this->formulaireCache->questions_de_gouvernance->count();
    }
}
