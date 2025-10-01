<?php

namespace App\Http\Requests\enquetes_de_gouvernance\evaluation_de_gouvernance\soumissions_factuel;

use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance;
use App\Models\enquetes_de_gouvernance\FormulaireFactuelDeGouvernance;
use App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance;
use App\Models\enquetes_de_gouvernance\QuestionFactuelDeGouvernance;
use App\Models\Organisation;
use App\Models\Programme;
use App\Models\SourceDeVerification;
use App\Rules\HashValidatorRule;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SoumissionFactuelRequest extends FormRequest
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

        return (request()->user()->hasPermissionTo("creer-une-soumission") || request()->user()->hasRole("unitee-de-gestion", "organisation")) && $this->evaluation_de_gouvernance->statut == 0;

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

            'factuel'                                               => ['sometimes', 'array', 'min:1'],

            'factuel.comite_members'                                => ['sometimes', 'array', 'min:1'],
            'factuel.comite_members.*.nom'                          => ['sometimes', 'string'],
            'factuel.comite_members.*.prenom'                       => ['sometimes', 'string'],
            'factuel.comite_members.*.contact'                      => ['sometimes', 'distinct', 'numeric', 'digits_between:8,24'],

            'factuel.response_data.*.questionId'                    => [
                'sometimes',
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
            'factuel.response_data.*.optionDeReponseId'             => ['sometimes', new HashValidatorRule(new OptionDeReponseGouvernance()), function ($attribute, $value, $fail) {
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

            'factuel.response_data.*.sourceDeVerificationId'        => ['nullable',
    function ($attribute, $value, $fail) {
        // Step 1: Extraire l’index
        preg_match('/factuel.response_data\.(\d+)\.sourceDeVerificationId/', $attribute, $matches);
        $index = $matches[1] ?? null;

        // Step 2: Si aucun index trouvé → fail
        if ($index === null) {
            return $fail("Impossible d’identifier la question liée.");
        }

        // Step 3: Vérifier si une valeur a été soumise
        if ($value === null || $value === '' || $value === 'null') {
            return; // nullable → on ignore la règle si null
        }

        // Step 4: Exécuter la règle HashValidatorRule manuellement
        $rule = new HashValidatorRule(new SourceDeVerification());

        if (!$rule->passes($attribute, $value)) {
            return $fail("La source de vérification est invalide.");
        }

        // Step 5: Vérifier aussi si la source est trop courte
        $sourceText = request()->input("factuel.response_data.$index.sourceDeVerification");
        if (!empty($sourceText) && strlen($sourceText) < 10) {
            return $fail("La source de vérification doit contenir au moins 10 caractères.");
        }
    }],
            'factuel.response_data.*.sourceDeVerification'          => ['nullable'],
            'factuel.response_data.*.preuves'                       => ['sometimes', "array", "min:0"],
            'factuel.response_data.*.preuves.*'                     => ["file", 'mimes:txt,doc,docx,xls,csv,xlsx,ppt,pdf,jpg,png,jpeg,mp3,wav,mp4,mov,avi,mkv', /* 'mimetypes:text/plain,text/csv,application/pdf,application/msword,application/vnd.ms-excel,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/jpeg,image/png,image/gif,audio/mpeg,audio/wav,video/mp4,video/quicktime,video/x-msvideo,video/x-matroska', */ "max:20480"],
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
            'organisationId.required_if' => "L'organisation est requise pour les unités de gestion.",

            'formulaireDeGouvernanceId.required' => "Le formulaire de gouvernance est requis.",
            'formulaireDeGouvernanceId.exists' => "Le formulaire de gouvernance sélectionné n'existe pas ou pas associé à cette évaluation.",

            'factuel.required_if' => "Les données factuelles sont requises si la perception n'est pas fournie.",
            'factuel.array' => "Les données factuelles doivent être sous forme de tableau.",

            'factuel.comite_members.array' => "Les informations des membres du comité doivent être sous forme de tableau.",
            'factuel.comite_members.min' => "Il doit y avoir au moins des informations d'un membre du comité.",
            'factuel.comite_members.*.nom.string' => "Le nom du membre doit être une chaîne de caractères.",
            'factuel.comite_members.*.prenom.string' => "Le prénom du membre doit être une chaîne de caractères.",
            'factuel.comite_members.*.contact.distinct' => "Chaque contact du membre doit être unique.",
            'factuel.comite_members.*.contact.numeric' => "Le contact du membre doit être un numéro valide.",
            'factuel.comite_members.*.contact.digits_between' => "Le contact du membre doit contenir entre 8 et 24 chiffres.",

            'factuel.response_data.*.questionId.required_if' => "L'ID de la question est requis",
            'factuel.response_data.*.questionId.distinct' => "Les questions doivent être uniques.",
            'factuel.response_data.*.questionId.exists' => "L'indicateur sélectionné n'existe pas.",

            'factuel.response_data.*.optionDeReponseId.required_if' => "L'option de réponse est requise",
            'factuel.response_data.*.optionDeReponseId.exists' => "L'option de réponse sélectionnée est invalide.",
            'factuel.response_data.*.description.required' => 'La description est requise.',
            'factuel.response_data.*.sourceDeVerificationId.required' => 'Veuillez sélectionner une source de vérification.',
            'factuel.response_data.*.sourceDeVerification.required' => 'Le champ source de vérification est requis.',

            'factuel.response_data.*.preuves.array' => "Les preuves doivent être un tableau de fichiers.",
            'factuel.response_data.*.preuves.*.file' => "Chaque preuve doit être un fichier valide.",
            'factuel.response_data.*.preuves.*.mimes' => "Format de fichier non valide. Formats acceptés : txt, doc, docx, xls, csv, xlsx, ppt, pdf, jpg, png, jpeg, mp3, wav, mp4, mov, avi, mkv.",
            'factuel.response_data.*.preuves.*.max' => "La taille maximale du fichier est de 20 Mo."        ];
    }

    /**
     * Returns the number of questions of the formulaire de gouvernance
     * stored in the formulaireCache attribute
     *
     * @return int
     */
    private function getCountOfQuestionsOfAFormular()
    {

        $this->formulaireCache->questions_de_gouvernance->count();
    }
}
