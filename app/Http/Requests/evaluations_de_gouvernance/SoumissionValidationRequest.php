<?php

namespace App\Http\Requests\evaluations_de_gouvernance;

use App\Models\EvaluationDeGouvernance;
use App\Models\FormulaireDeGouvernance;
use App\Models\Organisation;
use App\Models\OptionDeReponse;
use App\Models\Programme;
use App\Models\QuestionDeGouvernance;
use App\Models\Soumission;
use App\Models\SourceDeVerification;
use App\Rules\HashValidatorRule;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SoumissionValidationRequest extends FormRequest
{
    protected $formulaireCache = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if(is_string($this->evaluation_de_gouvernance))
        {
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
            'soumissionId'   => ['nullable', new HashValidatorRule(new Soumission())],
            'organisationId'   => [Rule::requiredIf(request()->user()->hasRole("unitee-de-gestion")), new HashValidatorRule(new Organisation())],
            'formulaireDeGouvernanceId'   => ["required", new HashValidatorRule(new FormulaireDeGouvernance()), function ($attribute, $value, $fail) {
                    // Check if formulaireDeGouvernanceId exists within the related formulaires_de_gouvernance
                    $formulaire = $this->evaluation_de_gouvernance->formulaires_de_gouvernance()
                                        ->wherePivot('formulaireDeGouvernanceId', request()->input('formulaireDeGouvernanceId'))
                                        ->first();

                    if($formulaire == null) $fail('The selected formulaire de gouvernance ID is invalid or not associated with this evaluation.');

                    $this->formulaireCache = $formulaire;

                    if(($soumission = $this->evaluation_de_gouvernance->soumissions->where('organisationId', request()->input('organisationId') ?? auth()->user()->profilable->id)->where('formulaireDeGouvernanceId', request()->input('formulaireDeGouvernanceId'))->first()) && $soumission->statut === true){
                        $fail('La soumission a déjà été validée.');
                    }
                }
            ],

            'factuel'                                         => ['required', 'array'],

            'factuel.comite_members'                                        => ['required', 'array', 'min:1'],
            'factuel.comite_members.*.nom'                                  => ['required', 'string'],
            'factuel.comite_members.*.prenom'                               => ['required', 'string'],
            'factuel.comite_members.*.contact'                              => ['required', 'distinct', 'numeric','digits_between:8,24'],
            'factuel.response_data'                                 => [Rule::requiredIf(!request()->input('perception')), 'array', function($attribute, $value, $fail) {

                $fail(count($value));
                    if (count($value) < $this->getCountOfQuestionsOfAFormular()) {
                        $fail("Veuillez remplir tout le formulaire.");
                    }
                }
            ],
            'factuel.response_data.*.questionId'      => [Rule::requiredIf(!request()->input('perception')), 'distinct',
                new HashValidatorRule(new QuestionDeGouvernance()),
                function($attribute, $value, $fail) {

                    if($this->formulaireCache){
                        $question = QuestionDeGouvernance::where("formulaireDeGouvernanceId", $this->formulaireCache->id)->where("type", "indicateur")->findByKey($value)->exists();
                        if (!$question) {
                            // Fail validation if no response options are available
                            $fail("Cet Indicateur n'existe pas.");
                        }
                    }

                    /*$this->indicateurCache = $indicateur;

                    // Check if there are response options
                    if ($indicateur->observations()->where('enqueteDeCollecteId', $this->enquete_de_collecte->id)->where('organisationId', $this->organisationId)->where('indicateurDeGouvernanceId', $indicateur->id)->exists()) {
                        // Fail validation if no response options are available
                        $fail('Cet Indicateur a deja ete observer pour le compte de cette enquete et par rapport a cette structure.');
                    }*/
                }
            ],
            'factuel.response_data.*.optionDeReponseId'   => [Rule::requiredIf(!request()->input('perception')), new HashValidatorRule(new OptionDeReponse()), function($attribute, $value, $fail) {
                /**
                 * Check if the given optionDeReponseId is part of the IndicateurDeGouvernance's options_de_reponse
                 *
                 * If the provided optionDeReponseId is not valid, fail the validation
                 */
                if($this->formulaireCache){
                    if (!($this->formulaireCache->options_de_reponse()->where('optionId', request()->input($attribute))->exists())) {
                        $fail('The selected option is invalid for the given formulaire.');
                    }
                }
            }],
            'factuel.response_data.*.sourceDeVerificationId'        => [Rule::requiredIf(!request()->input('factuel.response_data.*.sourceDeVerification')), new HashValidatorRule(new SourceDeVerification())],
            'factuel.response_data.*.sourceDeVerification'          => [ Rule::requiredIf(!request()->input('factuel.response_data.*.sourceDeVerificationId'))],


            'factuel.response_data.*.preuves'                       => [ Rule::requiredIf(request()->input('soumissionId') == null),
                function($attribute, $value, $fail) {

                    if(request()->input('soumissionId') != null){

                        if($this->formulaireCache){

                            // Step 1: Use preg_match to extract the index
                            preg_match('/factuel.response_data\.(\d+)\.preuves/', $attribute, $matches);

                            // Step 2: Check if the index is found
                            $index = $matches[1] ?? null; // Get the index if it exists

                            // Step 3: Retrieve the questionId from the request input based on the index
                            if ($index !== null) {
                                $questionId = request()->input('factuel.response_data.*.questionId')[$index] ?? null;
                            }
                            else{
                                $fail("La question introuvable.");
                            }

                            $question = QuestionDeGouvernance::where("formulaireDeGouvernanceId", $this->formulaireCache->id)->where("type", "indicateur")->findByKey($questionId)->first();

                            if (!$question) {
                                // Fail validation if no response options are available
                                $fail("Cet Indicateur n'existe pas.");
                            }

                            $reponse = $question->reponses()->where('soumissionId', request()->input('soumissionId'))->first();

                            if((!$reponse || !($reponse->preuves_de_verification()->count())) && empty(request()->input($attribute))){
                                $fail("La preuve est required.");
                            }
                        }
                        else{
                            $fail("La preuve est required.");
                        }
                    }

                }, "array", "min:1"],
            'factuel.response_data.*.preuves.*'                     => ["file", "mimes:doc,docx,xls,csv,xlsx,ppt,pdf,jpg,png,jpeg,mp3,wav,mp4,mov,avi,mkv", /* "mimetypes:application/pdf,application/msword,application/vnd.ms-excel,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/jpeg,image/png,audio/mpeg,audio/wav,video/mp4,video/quicktime,video/x-msvideo,video/x-matroska", */ "max:20480"],

            'perception'                              => [Rule::requiredIf(!request()->input('factuel')), 'array', function($attribute, $value, $fail) {
                    if (count($value) < $this->getCountOfQuestionsOfAFormular()) {
                        $fail("Veuillez remplir tout le formulaire.");
                    }
                }
            ],
            'perception.categorieDeParticipant'       => [Rule::requiredIf(!request()->input('factuel')), 'in:membre_de_conseil_administration,employe_association,membre_association,partenaire'],
            'perception.sexe'                         => [Rule::requiredIf(!request()->input('factuel')), 'in:masculin,feminin'],
            'perception.age'                          => [Rule::requiredIf(!request()->input('factuel')), 'in:<35,>35'],

            'perception.response_data.*.questionId'      => [Rule::requiredIf(!request()->input('factuel')), 'distinct',
                new HashValidatorRule(new QuestionDeGouvernance()),
                function($attribute, $value, $fail) {
                    $question = QuestionDeGouvernance::where("formulaireDeGouvernanceId", $this->formulaireCache->id)->where("type", "question_operationnelle")->findByKey($value)->exists();
                    if (!$question) {
                        // Fail validation if no response options are available
                        $fail("Cette question operationnelle n'existe pas.");
                    }

                    /*$this->indicateurCache = $indicateur;

                    // Check if there are response options
                    if ($indicateur->observations()->where('enqueteDeCollecteId', $this->enquete_de_collecte->id)->where('organisationId', $this->organisationId)->where('indicateurDeGouvernanceId', $indicateur->id)->exists()) {
                        // Fail validation if no response options are available
                        $fail('Cet Indicateur a deja ete observer pour le compte de cette enquete et par rapport a cette structure.');
                    }*/
                }
            ],

            'perception.response_data.*.optionDeReponseId'   => [Rule::requiredIf(!request()->input('factuel')), new HashValidatorRule(new OptionDeReponse()), function($attribute, $value, $fail) {
                /**
                 * Check if the given optionDeReponseId is part of the IndicateurDeGouvernance's options_de_reponse
                 *
                 * If the provided optionDeReponseId is not valid, fail the validation
                 */
                if (!($this->formulaireCache->options_de_reponse()->where('optionId', request()->input($attribute))->exists())) {
                    $fail('The selected option is invalid for the given formulaire.');
                }
            }],

            'perception.commentaire'                => [Rule::requiredIf(!request()->input('factuel')), 'string'],
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
    private function getCountOfQuestionsOfAFormular(){
        return $this->formulaireCache->questions_de_gouvernance->count();
    }
}
