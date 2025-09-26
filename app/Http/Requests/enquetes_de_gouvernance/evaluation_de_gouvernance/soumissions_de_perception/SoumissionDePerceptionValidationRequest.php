<?php

namespace App\Http\Requests\enquetes_de_gouvernance\evaluation_de_gouvernance\soumissions_de_perception;

use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance;
use App\Models\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernance;
use App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance;
use App\Models\enquetes_de_gouvernance\QuestionDePerceptionDeGouvernance;
use App\Models\Programme;
use App\Rules\HashValidatorRule;
use Illuminate\Foundation\Http\FormRequest;

class SoumissionDePerceptionValidationRequest extends FormRequest
{
    protected $formulaireCache = null;
    protected $organisation = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return !auth()->check() && $this->evaluation_de_gouvernance->statut == 0;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(is_string($this->evaluation_de_gouvernance))
        {
            $this->evaluation_de_gouvernance = EvaluationDeGouvernance::findByKey($this->evaluation_de_gouvernance);
        }

        return [
            'identifier_of_participant' => ['required'],
            'token'                     => ['required', 'string', 'max:255', function ($attribute, $value, $fail) {
                $this->organisation = $this->evaluation_de_gouvernance->organisations(null,request()->input('token'))->first();
                if($this->organisation == null) $fail('Token inconnu.');

                $this->merge([
                    'organisationId' => $this->organisation->id, // Add or update the key-value pair
                ]);
            }],
            'formulaireDeGouvernanceId'   => ["required", new HashValidatorRule(new FormulaireDePerceptionDeGouvernance())],
            'perception'                              => ['required', 'array', 'min:4', 'max: 5'],
            'perception.categorieDeParticipant'       => ['required', 'in:membre_de_conseil_administration,employe_association,membre_association,partenaire'],
            'perception.sexe'                         => ['required', 'in:masculin,feminin'],
            'perception.age'                          => ['required', 'in:<35,>35'],

            'perception.response_data'                 => [
                //"required",
                'array'
            ],
            'perception.response_data.*.questionId'      => ['required', 'distinct',
                new HashValidatorRule(new QuestionDePerceptionDeGouvernance())
	     ],

            'perception.response_data.*.optionDeReponseId'   => ['required', new HashValidatorRule(new OptionDeReponseGouvernance())],
	    'perception.commentaire'                => ['required', 'string'],
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

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Vérifier le token
        $this->organisation = $this->evaluation_de_gouvernance
            ->organisations(null, request()->input('token'))
            ->first();
        if (!$this->organisation) {
            $validator->errors()->add('token', 'Token inconnu.');
        } else {
            $this->merge(['organisationId' => $this->organisation->id]);
        }

        // Vérifier formulaire
        $formulaireId = request()->input('formulaireDeGouvernanceId');
        $formulaire = $this->evaluation_de_gouvernance
            ->formulaires_de_perception_de_gouvernance()
            ->where('formulaireDePerceptionId', $formulaireId)
            ->first();

        if (!$formulaire) {
            $validator->errors()->add('formulaireDeGouvernanceId', 'Formulaire de gouvernance de perception inconnu');
        } else {
            $this->formulaireCache = $formulaire;
            if ($this->evaluation_de_gouvernance
                ->soumissionsDePerception
                ->where('organisation', $this->organisation->id)
                ->where('formulaireDePerceptionId', $formulaireId)
                ->isNotEmpty()
            ) {
                $validator->errors()->add('formulaireDeGouvernanceId', 'La soumission a déjà été validée.');
            }
        }

        // Vérifier response_data
        $responseData = request()->input('perception.response_data', []);
        if (count($responseData) < $this->getCountOfQuestionsOfAFormular()) {
            $validator->errors()->add('perception.response_data', 'Veuillez repondre a toutes les questions du formulaire.');
        }

        // Liste de toutes les questions du formulaire
        $allQuestionIds = $formulaire->questions_de_gouvernance->pluck('id')->toArray();

        // Liste des questions envoyées par le client
        $answeredQuestionIds = collect($this->input('perception.response_data', []))
            ->pluck('questionId')
            ->toArray();

        // Détecter les questions manquantes
        $missingQuestions = array_diff($allQuestionIds, $answeredQuestionIds);

        if (!empty($missingQuestions)) {
            $validator->errors()->add(
                'perception.response_data',
                'Toutes les questions doivent être répondues. Questions manquantes : ' . implode(', ', $missingQuestions)
            );

            foreach ($missingQuestions as $questionId) {
    $question = QuestionDePerceptionDeGouvernance::findByKey($questionId);
    $questionText = $question ? $question->question_operationnelle->nom : "Question inconnue";

    // On ajoute l'erreur sans utiliser l'ID comme indice, mais avec un nouvel indice fictif
    $validator->errors()->add(
        "perception.response_data.missing.$questionId.optionDeReponseId",
        "La question « {$questionText} » n'a pas été répondue."
    );

            }
        }

        foreach ($responseData as $i => $resp) {
            // Vérifier questionId
            if ($this->formulaireCache) {
                $question = QuestionDePerceptionDeGouvernance::where("formulaireDePerceptionId", $this->formulaireCache->id)
                    ->findByKey($resp['questionId'] ?? null);

                if (!$question) {
                    $validator->errors()->add("perception.response_data.$i.questionId", "Cette question opérationnelle n'existe pas.");
                }
            }

            // Vérifier optionDeReponseId
            if ($this->formulaireCache && ! $this->formulaireCache
                ->options_de_reponse()
                ->where('optionId', $resp['optionDeReponseId'] ?? null)
                ->exists()
            ) {
                $validator->errors()->add("perception.response_data.$i.optionDeReponseId", 'The selected option is invalid for the given formulaire.');
            }
        }
    });
}
}
