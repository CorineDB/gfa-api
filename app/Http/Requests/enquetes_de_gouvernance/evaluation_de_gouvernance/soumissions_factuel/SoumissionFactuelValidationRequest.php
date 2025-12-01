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
use App\Models\enquetes_de_gouvernance\SourceDeVerification as EnqSourceDeVerification;
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
                'array'
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
            'factuel.response_data.*.optionDeReponseId'   => ["required"],
            'factuel.response_data.*.description'                   => ["nullable"],
            'factuel.response_data.*.sourceDeVerificationId'        => ["nullable"],
            'factuel.response_data.*.sourceDeVerification'          => [
                "nullable"
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
            } */
            ],


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

                            $optionDeReponseId = null;
                            $formOption = null;

                            // Step 3: Retrieve the questionId from the request input based on the index
                            if ($index !== null) {
                                $optionDeReponseId = request()->input('factuel.response_data.*.optionDeReponseId')[$index] ?? null;

                                $formOption = $this->formulaireCache->options_de_reponse()->wherePivot('optionId', $optionDeReponseId)->first();
                            } else {
                                $fail("Option de reponse introuvable.");
                            }

                            if ($formOption) {

                                if ($formOption->pivot->preuveIsRequired) {

                                    $reponse = $question->reponses()->where('soumissionId', request()->input('soumissionId'))->first();

                                    if ($reponse) {
                                        if ((!$reponse->preuves_de_verification()->count() && empty(request()->input($attribute))) && $reponse->preuveIsRequired) {
                                            $fail("La preuve est required.");
                                        }
                                    } else {

                                        if (empty(request()->input($attribute))) {
                                            $fail("La preuve est required.");
                                        }
                                    }
                                }
                            } else {
                                $fail("Option inconnu du formulaire.");
                            }
                        } else {

                            $fail("Formulaire factuel inconnu.");
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

            'factuel.response_data.*.description.required' => 'La description est requise.',
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            // Vérifier le formulaire factuel
            $formulaireId = request()->input('formulaireDeGouvernanceId');
            $formulaire = $this->evaluation_de_gouvernance
                ->formulaires_factuel_de_gouvernance()
                ->where('formulaireFactuelId', $formulaireId)
                ->first();

            // Check if formulaireDeGouvernanceId exists within the related formulaire_factuel_de_gouvernance
            /*$formulaire = $this->evaluation_de_gouvernance
			->formulaires_factuel_de_gouvernance()
                        ->where('formulaireFactuelId', request()->input('formulaireDeGouvernanceId'))
                        ->first();*/

            if (!$formulaire) {
                $validator->errors()->add('formulaireDeGouvernanceId', 'Formulaire factuel inconnu.');
                return;
            }

            $this->formulaireCache = $formulaire;

            $responseData = request()->input('factuel.response_data', []);

            if (count($responseData) < $this->getCountOfQuestionsOfAFormular()) {
                $validator->errors()->add('factuel.response_data', 'Veuillez remplir tout le formulaire. Count' . $this->getCountOfQuestionsOfAFormular());
            }

            // 🔹 Vérification des questions attendues
            // Récupérer toutes les questions du formulaire
            $allQuestionIds = $formulaire->questions_de_gouvernance->pluck('id')->toArray();

            // Récupérer toutes les questions envoyées
            $answered = collect($responseData, [])
                ->pluck('questionId')
                ->toArray();


            // Détecter les questions manquantes
            $missing = array_diff($allQuestionIds, $answered);

            if (!empty($missing)) {
                foreach ($missing as $missingId) {
                    // Trouver la question pour afficher son libellé
                    $question = $formulaire->questions_de_gouvernance->firstWhere('id', $missingId);

                    $validator->errors()->add(
                        "factuel.response_data",
                        "La question « {$question->indicateur_de_gouvernance->nom} » n'a pas été répondue."
                    );
                }
            }

            //$responseData = request()->input('factuel.response_data', []);

            foreach ($responseData as $i => $resp) {

                // Vérifier que la question appartient bien au formulaire
                $question = $this->formulaireCache
                    ->questions_de_gouvernance()
                    ->where("formulaireFactuelId", $this->formulaireCache->id)
                    ->find($resp['questionId'] ?? null);

                if (!$question) {
                    $validator->errors()->add("factuel.response_data.$i.questionId", "Cet indicateur n'existe pas.");
                    continue;
                }

                // Vérifier que optionDeReponseId est fourni
                if (empty($resp['optionDeReponseId']) || $resp['optionDeReponseId'] === 'null' || $resp['optionDeReponseId'] === null) {
                    $validator->errors()->add("factuel.response_data.$i.optionDeReponseId",  "L'option de réponse est requise.");
                    continue;
                }

                // Valider avec HashValidatorRule et récupérer l'ID décodé
                $hashRule = new HashValidatorRule(new OptionDeReponseGouvernance());
                if (!$hashRule->passes("factuel.response_data.$i.optionDeReponseId", $resp['optionDeReponseId'])) {
                    $validator->errors()->add("factuel.response_data.$i.optionDeReponseId", "L'option de réponse sélectionnée est invalide.");
                    continue;
                }

                // Récupérer l'ID décodé et l'assigner à $resp
                $optionModel = OptionDeReponseGouvernance::findByKey($resp['optionDeReponseId']);
                if (!$optionModel) {
                    $validator->errors()->add("factuel.response_data.$i.optionDeReponseId", "L'option de réponse sélectionnée est invalide.");
                    continue;
                }
                $decodedOptionId = $optionModel->id;
                $resp['optionDeReponseId'] = $decodedOptionId;

                // Vérifier que l'option appartient bien au formulaire
                $formOption = $this->formulaireCache
                    ->options_de_reponse()
                    ->wherePivot('optionId', $resp['optionDeReponseId'])
                    ->withPivot('preuveIsRequired', 'sourceIsRequired', 'descriptionIsRequired')
                    ->first();

                if (!$formOption) {
                    $validator->errors()->add("factuel.response_data.$i.optionDeReponseId", "Option inconnue du formulaire.");
                    continue;
                } else {

                    /**
                     * 🔎 Validation de la description
                     */
                    if ($formOption && $formOption->pivot->descriptionIsRequired == 1) {
                        $description = $resp['description'] ?? null;

                        if (empty($description)) {
                            $validator->errors()->add(
                                "factuel.response_data.$i.description",
                                "La description est requise."
                            );
                        } elseif (!is_string($description) || mb_strlen(trim($description)) < 10) {
                            $validator->errors()->add(
                                "factuel.response_data.$i.description",
                                "La description doit contenir au moins 10 caractères."
                            );
                        }
                    }

                    /**
                     * 🔎 Validation de la sourceDeVerificationId
                     */
                    if ($formOption && $formOption->pivot->preuveIsRequired == 1) {
                        $sourceDeVerification = $resp['sourceDeVerification'] ?? null;
                        $sourceDeVerificationId = $resp['sourceDeVerificationId'] ?? null;

                        if (empty($sourceDeVerification) && empty($sourceDeVerificationId)) {
                            $validator->errors()->add(
                                "factuel.response_data.$i.sourceDeVerificationId",
                                "La source de vérification est requise."
                            );
                        } else {
                            // Vérifier que l’ID est valide
                            if (!empty($sourceDeVerificationId) && $sourceDeVerificationId != 'null') {
                                $rule = new HashValidatorRule(new EnqSourceDeVerification());

                                if (!$rule->passes("factuel.response_data.$i.sourceDeVerificationId", $sourceDeVerificationId)) {
                                    $validator->errors()->add(
                                        "factuel.response_data.$i.sourceDeVerificationId",
                                        "La source de vérification est invalide."
                                    );
                                }
                                else {
                                    $validator->errors()->add(
                                        "factuel.response_data.$i.sourceDeVerificationId",
                                        "Veuillez preciser la source de verification. Vérifier que l’ID est valide"
                                    );
                                }
                            }
                            // Si une source textuelle est fournie → vérifier qu’elle est une string valide et min 10 caractères
                            elseif (!empty($sourceDeVerification)) {
                                if (!is_string($sourceDeVerification) || mb_strlen(trim($sourceDeVerification)) < 10) {
                                    $validator->errors()->add(
                                        "factuel.response_data.$i.sourceDeVerification",
                                        "La source de vérification doit contenir au moins 10 caractères."
                                    );
                                }
                            }
                            elseif (empty($sourceDeVerification)) {
                                $validator->errors()->add(
                                    "factuel.response_data.$i.sourceDeVerification",
                                    "La source de vérification n'a pas ete renseigne."
                                );
                            }
                            else {
                                $validator->errors()->add(
                                    "factuel.response_data.$i.sourceDeVerificationId",
                                    "Veuillez preciser la source de verification. Vérifier source"
                                );
                            }
                        }
                    }

                    /**
                     * 🔎 Validation des preuves (logique déjà posée)
                     */
                    if ($formOption->pivot->preuveIsRequired) {
                        $reponse = $question->reponses()
                            ->where('soumissionId', request()->input('soumissionId'))
                            ->first();

                        if ($reponse) {
                            if (
                                (!$reponse->preuves_de_verification()->count() && (empty($resp['preuves']) || !is_array($resp['preuves'])))
                                && $reponse->preuveIsRequired
                            ) {
                                $validator->errors()->add("factuel.response_data.$i.preuves", "La preuve est requise.");
                            }
                        } else {
                            if (empty($resp['preuves']) || !is_array($resp['preuves'])) {
                                $validator->errors()->add("factuel.response_data.$i.preuves", "La preuve est requise.");
                            }
                        }


                        // 🔹 Validation de chaque fichier de preuve fourni
                        if (!empty($resp['preuves']) && is_array($resp['preuves'])) {
                            foreach ($resp['preuves'] as $j => $preuve) {
                                if (!($preuve instanceof \Illuminate\Http\UploadedFile)) {
                                    $validator->errors()->add(
                                        "factuel.response_data.$i.preuves.$j",
                                        "La preuve n°" . ($j + 1) . " doit être un fichier valide."
                                    );
                                } else {
                                    // Taille max 20Mo (adapter si nécessaire)
                                    if ($preuve->getSize() > 20 * 1024 * 1024) {
                                        $validator->errors()->add(
                                            "factuel.response_data.$i.preuves.$j",
                                            "La preuve n°" . ($j + 1) . " ne doit pas dépasser 20 Mo."
                                        );
                                    }
                                    // Extensions autorisées
                                    if (!in_array($preuve->getClientOriginalExtension(), ['doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pdf', 'jpg', 'jpeg', 'png', 'mp3', 'wav', 'mp4', 'mov', 'avi', 'mkv'])) {
                                        $validator->errors()->add(
                                            "factuel.response_data.$i.preuves.$j",
                                            "La preuve n°" . ($j + 1) . " doit être un fichier valide (doc, pdf, xls, jpg, png, mp4, etc.)."
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //throw_if($validator->errors()->isNotEmpty(), \Illuminate\Validation\ValidationException::withMessages($validator->errors()->toArray()));

            //throw_if($validator->errors()->isEmpty(), \Illuminate\Validation\ValidationException::withMessages($validator->errors()->toArray()));
        });
    }
}
