<?php

namespace App\Models;

use App\Models\enquetes_de_gouvernance\CritereDeGouvernanceFactuel;
use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance as EnqueteDeGouvernance;
use App\Models\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernance;
use App\Models\enquetes_de_gouvernance\FormulaireFactuelDeGouvernance;
use App\Models\enquetes_de_gouvernance\IndicateurDeGouvernanceFactuel;
use App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance;
use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernanceFactuel;
use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception;
use App\Models\enquetes_de_gouvernance\QuestionOperationnelle;
use App\Models\enquetes_de_gouvernance\SoumissionDePerception;
use App\Models\enquetes_de_gouvernance\SoumissionFactuel;
use App\Models\enquetes_de_gouvernance\SourceDeVerification as EnqSourceDeVerification;
use App\Models\enquetes_de_gouvernance\TypeDeGouvernanceFactuel;
use App\Models\EvaluationDeGouvernance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;
use Exception;

class Programme extends Model
{
    use HasSecureIds, SoftDeletes, HasFactory;

    protected $table = 'programmes';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'nom',
        'code',
        'budgetNational',
        'description',
        'debut',
        'fin',
        'objectifGlobaux',
        'organismeDeTutelle'
    ];

    protected $cast = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $hidden = ['updated_at', 'deleted_at'];

    protected $relationships = [
        'indicateurs_values_keys',
        'indicateurs',
        'indicateurs_valeurs',
        'fonds',
        'recommandations',
        'actions_a_mener',
        'evaluations_de_gouvernance',
        'formulaires_de_gouvernance',
        'soumissions',
        'indicateurs_de_gouvernance',
        'criteres_de_gouvernance',
        'principes_de_gouvernance',
        'survey_forms',
        'surveys',
        'organisations',
        'suivis_indicateurs',
        'suivis',
        'sites',
        'projets',
        'suiviFinanciers',
        'types_de_gouvernance',
        'options_de_reponse',
        'sources_de_verification',
        'unitees_de_mesure',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($programme) {
            // Validation 1: Vérifier si le programme a des suivis financiers
            $suiviFinancierExists = \App\Models\SuiviFinancier::where('programmeId', $programme->id)
                ->exists();

            if ($suiviFinancierExists) {
                throw new \Exception(
                    "Impossible de supprimer ce programme car il a déjà fait l'objet d'un suivi financier",
                    403
                );
            }

            // Validation 2: Vérifier si le programme a des suivis physiques
            $suiviExists = \App\Models\Suivi::where('programmeId', $programme->id)
                ->exists();

            if ($suiviExists) {
                throw new \Exception(
                    "Impossible de supprimer ce programme car il a déjà fait l'objet d'un suivi physique (TEP)",
                    403
                );
            }

            foreach ($programme->relationships as $relationship) {
                if ($programme->{$relationship}()->exists() && $programme->{$relationship}->count() > 0) {
                    // Prevent deletion by throwing an exception
                    throw new Exception('Impossible de supprimer cet élément, car des ' . str_replace('_', ' ', $relationship) . " sont associées au programme. Veuillez d'abord supprimer ou dissocier ces éléments avant de réessayer.");
                }
            }

            DB::beginTransaction();
            try {
                $programme->ptabScopes()->delete();

                $programme->codes()->delete();

                $programme->sinistres()->delete();

                $programme->formulaires()->delete();

                $programme->uniteeDeGestion()->delete();

                $programme->userUniteeDeGestion()->delete();

                $programme->indicateurs()->delete();

                $programme->objectifSpecifiques()->delete();

                $programme->resultats()->delete();

                $programme->sites()->delete();

                $programme->projets()->delete();

                $programme->eActivites()->delete();

                $programme->eActiviteMods()->delete();

                $programme->suiviFinanciers()->delete();

                $programme->users->each(function ($user) {
                    if ($user) {
                        $user->update(['statut' => -1]);
                    }
                });

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class, 'programmeId');
    }

    /**
     * Liste des scopes d'un programme
     */
    public function ptabScopes()
    {
        return $this->hasMany(PtabScope::class, 'programmeId');
    }

    public function uniteeDeGestion()
    {
        return $this->hasOne(User::class, 'programmeId')->where('type', 'unitee-de-gestion');
    }

    public function userUniteeDeGestion()
    {
        return $this->hasOne(User::class, 'programmeId')->where('profilable_type', 'App\Models\UniteeDeGestion');
    }

    public function missionDeControle()
    {
        return $this->hasOne(User::class, 'programmeId')->where('type', 'mission-de-controle');
    }

    public function userMissionDeControle()
    {
        return $this->hasOne(User::class, 'programmeId')->where('profilable_type', 'App\Models\MissionDeControle');
    }

    public function gouvernement()
    {
        return $this->hasOne(User::class, 'programmeId')->where('type', 'gouvernement');
    }

    public function entreprisesExecutante()
    {
        return $this->hasMany(User::class, 'programmeId')->where('type', 'entreprise-executant');
    }

    public function institutions()
    {
        return $this->hasMany(User::class, 'programmeId')->where('type', 'institution');
    }

    /**
     * Charger la liste des organisations d'un oogramme
     */
    public function organisations()
    {
        return $this->hasMany(User::class, 'programmeId')->where('type', 'organisation')->whereHas('profilable');
    }

    public function getEntreprises()
    {
        $users = $this->entreprisesExecutante;

        $entreprises = [];

        foreach ($users as $user) {
            $entreprise = EntrepriseExecutant::find($user->profilable_id);
            array_push($entreprises, $entreprise);
        }

        return $entreprises;
    }

    public function getOrganisations()
    {
        $users = $this->organisations;

        $organisations = [];

        foreach ($users as $user) {
            $organisation = Organisation::find($user->profilable_id);
            array_push($organisations, $organisation);
        }

        return $organisations;
    }

    public function mods()
    {
        return $this->hasMany(User::class, 'programmeId')->where('type', 'mod');
    }

    public function bailleurs()
    {
        return $this->hasMany(User::class, 'programmeId')->where('type', 'bailleur')->orderBy('nom', 'asc');
    }

    public function getBailleurs()
    {
        $users = $this->bailleurs;

        $bailleurs = [];

        foreach ($users as $user) {
            $bailleur = Bailleur::find($user->profilable_id);
            array_push($bailleurs, $bailleur);
        }

        return $bailleurs;
    }

    public function codes()
    {
        return $this->hasMany(Code::class, 'programmeId');
    }

    public function sinistres()
    {
        return $this->hasMany(Sinistre::class, 'programmeId');
    }

    public function formulaires()
    {
        return $this->hasMany(Formulaire::class, 'programmeId');
    }

    public function codeBailleur($programmeId)
    {
        return intval(optional(optional($this->codes->where('programmeId', $programmeId))->last())->codePta) ?? 0;
    }

    public function uniteDeGestion()
    {
        return $this->hasOne(UniteeDeGestion::class, 'programmeId');
    }

    public function roles()
    {
        return $this->hasMany(Role::class, 'programmeId');
    }

    public function indicateurs()
    {
        return $this->hasMany(Indicateur::class, 'programmeId');
        return $this->morphMany(CadreLogiqueIndicateur::class, 'indicatable');
    }

    public function suivis_indicateurs()
    {
        return $this->hasMany(SuiviIndicateur::class, 'programmeId');
    }

    public function suivis()
    {
        return $this->hasMany(Suivi::class, 'programmeId');
    }

    public function objectifSpecifiques()
    {
        return $this->morphMany(ObjectifSpecifique::class, 'objectifable');
    }

    public function objectifGlobauxes()
    {
        return $this->morphMany(ObjectifGlobaux::class, 'objectifable');
    }

    public function resultats()
    {
        return $this->morphMany(Resultat::class, 'resultable');
    }

    /**
     * Get all intervention categories through projets.
     */
    public function categories()
    {
        return $this->hasMany(Categorie::class, 'programmeId');
    }

    /**
     * Get all intervention categories through projets.
     */
    public function cadre_de_mesure_rendement()
    {
        return $this->hasMany(Categorie::class, 'programmeId')->whereNull('categorieId')->with(['categories' => function ($query) {
            $query->orderBy('indice', 'asc')->with(['categories' => function ($query) {
                $query->orderBy('indice', 'asc')->with(['indicateurs' => function ($query) {
                    $query
                        ->orderBy('indice', 'asc')
                        ->with(['valeursCible', 'ug_responsable', 'organisations_responsable', 'sites'])
                        ->when(
                            auth()->check() &&
                                (auth()->user()->type == 'organisation' || (auth()->user()->profilable_id != 0 && auth()->user()->profilable_type == Organisation::class)), function ($query) {
                                // ->when((auth()->user()->type == 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function($query) {
                                // Filter by organisation responsible using both 'responsableable_type' and 'responsableable_id'
                                $query->whereHas('organisations_responsable', function ($query) {
                                    $query->where('responsableable_type', get_class(auth()->user()->profilable));
                                    $query->where('responsableable_id', auth()->user()->profilable->id);
                                });
                            }
                        );
                }]);
            }, 'indicateurs' => function ($query) {
                $query
                    ->orderBy('indice', 'asc')
                    ->with(['valeursCible', 'ug_responsable', 'organisations_responsable', 'sites'])
                    ->when(
                        auth()->check() &&
                            (auth()->user()->type == 'organisation' || (auth()->user()->profilable_id != 0 && auth()->user()->profilable_type == Organisation::class)), function ($query) {
                            // ->when((auth()->user()->type == 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function($query) {
                            // Filter by organisation responsible using both 'responsableable_type' and 'responsableable_id'
                            $query->whereHas('organisations_responsable', function ($query) {
                                $query->where('responsableable_type', get_class(auth()->user()->profilable));
                                $query->where('responsableable_id', auth()->user()->profilable->id);
                            });
                        }
                    );
            }]);
        }, 'indicateurs' => function ($query) {
            $query
                ->orderBy('indice', 'asc')
                ->with(['valeursCible', 'ug_responsable', 'organisations_responsable', 'sites'])
                ->when(
                    auth()->check() &&
                        (auth()->user()->type == 'organisation' || (auth()->user()->profilable_id != 0 && auth()->user()->profilable_type == Organisation::class)), function ($query) {
                        // ->when((auth()->user()->type == 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function($query) {
                        // Filter by organisation responsible using both 'responsableable_type' and 'responsableable_id'
                        $query->whereHas('organisations_responsable', function ($query) {
                            $query->where('responsableable_type', get_class(auth()->user()->profilable));
                            $query->where('responsableable_id', auth()->user()->profilable->id);
                        });
                    }
                );
        }]);
        return $this->hasMany(Categorie::class, 'programmeId')->whereNull('categorieId')->with(['categories' => function ($query) {
            $query->orderBy('indice', 'asc')->loadCategories();
        }]);
    }

    public function mesure_rendement_projet($projetId)
    {
        return $this->hasMany(Categorie::class, 'programmeId')->whereNull('categorieId')->with(['categories' => function ($query) use ($projetId) {
            $query->orderBy('indice', 'asc')->with(['categories' => function ($query) use ($projetId) {
                $query->orderBy('indice', 'asc')->with(['indicateurs' => function ($query) use ($projetId) {
                    $query
                        ->orderBy('indice', 'asc')
                        ->with(['valeursCible', 'ug_responsable', 'organisations_responsable', 'sites'])
                        ->when(
                            auth()->check() &&
                                (auth()->user()->type == 'organisation' || (auth()->user()->profilable_id != 0 && auth()->user()->profilable_type == Organisation::class && auth()->user()->profilable->projet->id == $projetId)), function ($query) use ($projetId) {
                                // ->when((auth()->user()->type == 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function($query) {
                                // Filter by organisation responsible using both 'responsableable_type' and 'responsableable_id'
                                $query->whereHas('organisations_responsable', function ($query) use ($projetId) {
                                    $query
                                        ->where('responsableable_type', get_class(auth()->user()->profilable))
                                        ->where('responsableable_id', auth()->user()->profilable->id)
                                        ->whereHas('projet', function ($query) use ($projetId) {
                                            $query->where('id', $projetId);
                                        });
                                });
                            }
                        );
                }]);
            }, 'indicateurs' => function ($query) use ($projetId) {
                $query
                    ->orderBy('indice', 'asc')
                    ->with(['valeursCible', 'ug_responsable', 'organisations_responsable', 'sites'])
                    ->when(
                        auth()->check() &&
                            (auth()->user()->type == 'organisation' || (auth()->user()->profilable_id != 0 && auth()->user()->profilable_type == Organisation::class && auth()->user()->profilable->projet->id == $projetId)), function ($query) use ($projetId) {
                            // ->when((auth()->user()->type == 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function($query) {
                            // Filter by organisation responsible using both 'responsableable_type' and 'responsableable_id'
                            $query->whereHas('organisations_responsable', function ($query) use ($projetId) {
                                $query
                                    ->where('responsableable_type', get_class(auth()->user()->profilable))
                                    ->where('responsableable_id', auth()->user()->profilable->id)
                                    ->whereHas('projet', function ($query) use ($projetId) {
                                        $query->where('id', $projetId);
                                    });
                            });
                        }
                    );
            }]);
        }, 'indicateurs' => function ($query) use ($projetId) {
            $query
                ->orderBy('indice', 'asc')
                ->with(['valeursCible', 'ug_responsable', 'organisations_responsable', 'sites'])
                ->when(
                    auth()->check() &&
                        (auth()->user()->type == 'organisation' || (auth()->user()->profilable_id != 0 && auth()->user()->profilable_type == Organisation::class && auth()->user()->profilable->projet->id == $projetId)), function ($query) use ($projetId) {
                        // ->when((auth()->user()->type == 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function($query) {
                        // Filter by organisation responsible using both 'responsableable_type' and 'responsableable_id'
                        $query->whereHas('organisations_responsable', function ($query) use ($projetId) {
                            $query
                                ->where('responsableable_type', get_class(auth()->user()->profilable))
                                ->where('responsableable_id', auth()->user()->profilable->id)
                                ->whereHas('projet', function ($query) use ($projetId) {
                                    $query->where('id', $projetId);
                                });
                        });
                    }
                );
        }]);
    }

    public function scopeLoadCategories($query)
    {
        return $query->with(['categories' => function ($query) {
            $query->orderBy('indice', 'asc')->loadCategories();
        }]);
        return $query->with(['categories' => function ($query) {
            $query->orderBy('indice', 'asc')->with(['indicateurs' => function ($query) {
                $query->with(['valeursCible', 'ug_responsable', 'organisations_responsable', 'sites']);
            }]);
        }]);
    }

    /**
     * Get all intervention sites through projets.
     */
    public function sites()
    {
        return $this->hasMany(Site::class, 'programmeId');
        return $this->hasManyThrough(
            Site::class,  // The target model
            Projet::class,  // The intermediate model
            'programmeId',  // Foreign key on projets table
            'id',  // Foreign key on sites table (via morph relation)
            'id',  // Local key on programmes table
            'id'  // Local key on projets table
        );

        // Get the related sites through the projets of the programme
        return Site::whereHas('projets', function ($query) {
            $query->whereIn('projets.id', $this->projets->pluck('id'));
        })->get();
    }

    public function oldSites()
    {
        return $this->hasMany(BailleurSite::class, 'programmeId');
    }

    public function site()
    {
        return $this->hasMany(BailleurSite::class, 'programmeId');
    }

    public function projets()
    {
        return $this->hasMany(Projet::class, 'programmeId');  /* ->orderBy('nom', 'asc') */
    }

    public function archiveProjets()
    {
        return $this->hasMany(ArchiveProjet::class, 'programmeId');
    }

    public function composantes()
    {
        $projets = $this->projets;
        $composante = [];

        if (count($projets)) {
            foreach ($projets as $projet) {
                $composantes = $projet->composantes;
                if (count($composantes)) {
                    foreach ($composantes as $c) {
                        array_push($composante, $c);
                    }
                }
            }
        }

        return $composante;
    }

    public function sousComposantes()
    {
        $composantes = $this->composantes();
        $sc = [];

        if (count($composantes)) {
            foreach ($composantes as $composante) {
                $sousComposantes = $composante->sousComposantes;

                if (count($sousComposantes)) {
                    foreach ($sousComposantes as $c) {
                        array_push($sc, $c);
                    }
                }
            }
        }

        return $sc;
    }

    public function activites()
    {
        $sousComposantes = $this->sousComposantes();
        $activites = [];

        if (count($sousComposantes)) {
            foreach ($sousComposantes as $sc) {
                $activite = $sc->activites;

                if (count($activite)) {
                    foreach ($activite as $a) {
                        array_push($activites, $a);
                    }
                }
            }
        }

        return $activites;
    }

    public function taches()
    {
        $activites = $this->activites();
        $taches = [];

        if (count($activites)) {
            foreach ($activites as $activite) {
                $tache = $activite->taches;

                if (count($tache)) {
                    foreach ($tache as $t) {
                        array_push($taches, $t);
                    }
                }
            }
        }

        return $taches;
    }

    public function decaissements()
    {
        $projets = $this->projets;
        $decaissements = [];

        if (count($projets)) {
            foreach ($projets as $projet) {
                $decaissement = $projet->decaissements;

                if (count($decaissement)) {
                    foreach ($decaissement as $t) {
                        array_push($decaissements, $t);
                    }
                }
            }
        }

        return $decaissements;
    }

    public function eActivites()
    {
        return $this->hasMany(EActivite::class, 'programmeId');
    }

    public function eActiviteMods()
    {
        return $this->hasMany(EActiviteMod::class, 'programmeId');
    }

    public function maitriseOeuvres()
    {
        return $this->hasMany(MaitriseOeuvre::class, 'programmeId');
    }

    public function suiviFinanciers()
    {
        return $this->hasMany(SuiviFinancier::class, 'programmeId')->when(
            auth()->check() &&
                (auth()->user()->type == 'organisation' || (auth()->user()->profilable_id != 0 && auth()->user()->profilable_type == Organisation::class)),
            function ($query) {
                $query->whereHas('activite', function ($query) {
                    $query->whereHas('composante', function ($query) {
                        $query->whereHas('projet', function ($query) {
                            $user = auth()->user();
                            if ($user->profilable) {
                                $query
                                    ->where('projetable_id', $user->profilable->id)
                                    ->where('projetable_type', Organisation::class);
                            }
                        });
                    });
                });
            }
        );

        /* ->whereHas('activite', function ($query) {
            $query->whereHas('composante', function ($query) {
                $query->whereHas('projet', function ($query) {
                    $user = auth()->user();
                    if ($user) {
                        $query->where("projetable_id", $user->profilable_id)
                              ->where("projetable_type", Organisation::class);
                    }*/
        /* if ($user->profilable) {
            $query->where("projetable_id", $user->profilable->id)
                  ->where("projetable_type", Organisation::class);
        } */

        /*
         * });
         *     });
         * });
         */
        /* ->when(
            auth()->check() &&
            (auth()->user()->type == 'organisation' || (auth()->user()->profilable && get_class(auth()->user()->profilable) == Organisation::class)),
            function ($query) {
                $query->whereHas('activite', function ($query) {
                    $query->whereHas('composante', function ($query) {
                        $query->whereHas('projet', function ($query) {
                            $user = auth()->user();
                            if ($user->profilable) {
                                $query->where("projetable_id", $user->profilable->id)
                                      ->where("projetable_type", Organisation::class);
                            }
                        });
                    });
                });
            }
        ); */

        return $this->hasMany(SuiviFinancier::class, 'programmeId')->when((auth()->user()->type == 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function ($query) {
            $query->whereHas('activite', function ($query) {
                $query->whereHas('projet', function ($query) {
                    $query->where('projetable_id', auth()->user()->profilable->id)->where('projetable_type', Organisation::class);
                });
            });
        });
    }

    public function archiveSuiviFinanciers()
    {
        return $this->hasMany(ArchiveSuiviFinancier::class, 'programmeId');
    }

    public function ppm()
    {
        $projets = $this->projets;

        $ppm = collect();

        foreach ($projets as $projet) {
            $ppm1 = collect($projet->ppm());
            $ppm = $ppm->merge($ppm1);
        }

        return $ppm;
    }

    public function indicateurs_cadre_logique()
    {
        return $this->morphMany(IndicateurCadreLogique::class, 'indicatable');
    }

    public function types_de_gouvernance()
    {
        return $this->hasMany(TypeDeGouvernance::class, 'programmeId');
    }

    public function options_de_reponse()
    {
        return $this->hasMany(OptionDeReponse::class, 'programmeId');
    }

    public function sources_de_verification()
    {
        return $this->hasMany(SourceDeVerification::class, 'programmeId');
    }

    public function enquete_sources_de_verification()
    {
        return $this->hasMany(EnqSourceDeVerification::class, 'programmeId');
    }

    public function formulaires_de_gouvernance()
    {
        return $this->hasMany(FormulaireDeGouvernance::class, 'programmeId');
    }

    public function evaluations_de_gouvernance()
    {
        return $this->hasMany(EvaluationDeGouvernance::class, 'programmeId');
    }

    public function enquetes_de_gouvernance()
    {
        return $this->hasMany(EnqueteDeGouvernance::class, 'programmeId');
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function evaluations_de_gouvernance_organisations(?int $organisationId = null)
    {
        return $this
            ->evaluations_de_gouvernance()
            ->when($organisationId, function ($query) use ($organisationId) {
                $query->with(['organisations' => function ($query) use ($organisationId) {
                    if ($organisationId) {
                        $query->where('organisations.id', $organisationId);
                    }
                }]);
            })
            ->get()
            ->flatMap(function ($evaluation) {
                return $evaluation->organisations;
            })
            ->unique('id');  // Ensure only distinct organisations by their ID
        return DB::table('evaluation_organisations')
            ->join('evaluations_de_gouvernance', 'evaluations_de_gouvernance.id', '=', 'evaluation_organisations.evaluationDeGouvernanceId')
            ->join('organisations', 'organisations.id', '=', 'evaluation_organisations.organisationId')
            ->where('evaluations_de_gouvernance.programmeId', $this->id)
            ->when($organisationId != null, function ($query) use ($organisationId) {
                $query->where('organisations.id', $organisationId)->distinct();
            });
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function stats_evaluations_de_gouvernance_organisations(?int $organisationId = null)
    {
        return $this
            ->enquetes_de_gouvernance()
            ->when($organisationId, function ($query) use ($organisationId) {
                $query->with(['organisations' => function ($query) use ($organisationId) {
                    if ($organisationId) {
                        $query->where('organisations.id', $organisationId);
                    }
                }]);
            })
            ->get()
            ->flatMap(function ($evaluation) {
                return $evaluation->organisations;
            })
            ->unique('id');  // Ensure only distinct organisations by their ID
        return DB::table('evaluation_organisations')
            ->join('evaluations_de_gouvernance', 'evaluations_de_gouvernance.id', '=', 'evaluation_organisations.evaluationDeGouvernanceId')
            ->join('organisations', 'organisations.id', '=', 'evaluation_organisations.organisationId')
            ->where('evaluations_de_gouvernance.programmeId', $this->id)
            ->when($organisationId != null, function ($query) use ($organisationId) {
                $query->where('organisations.id', $organisationId)->distinct();
            });
    }

    public function soumissions()
    {
        return $this->hasMany(Soumission::class, 'programmeId');
    }

    public function optionsDeReponse()
    {
        return $this->hasMany(OptionDeReponse::class, 'programmeId');
    }

    public function survey_forms()
    {
        return $this
            ->hasMany(SurveyForm::class, 'programmeId')
            ->when(
                auth()->check() &&
                    (auth()->user()->type == 'organisation' || (auth()->user()->profilable_id != 0 && auth()->user()->profilable_type == Organisation::class)), function ($query) {
                    // ->when(auth()->user()->type === 'organisation' || get_class(auth()->user()->profilable) == Organisation::class, function($query) {
                    $query->where('created_by_id', auth()->user()->profilable->id)->where('created_by_type', Organisation::class);
                }
            );
    }

    public function surveys()
    {
        return $this
            ->hasMany(Survey::class, 'programmeId')
            ->when(
                auth()->check() &&
                    (auth()->user()->type == 'organisation' || (auth()->user()->profilable_id != 0 && auth()->user()->profilable_type == Organisation::class)), function ($query) {
                    // ->when(auth()->user()->type === 'organisation' || get_class(auth()->user()->profilable) == Organisation::class, function($query) {
                    $query->where('created_by_id', auth()->user()->profilable->id)->where('created_by_type', Organisation::class);
                }
            );
    }

    public function enquetesDeCollecte()
    {
        return $this->hasMany(Enquete::class, 'programmeId');
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function indicateurs_de_gouvernance()
    {
        return $this->hasMany(IndicateurDeGouvernance::class, 'programmeId');
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function principes_de_gouvernance()
    {
        return $this->hasMany(PrincipeDeGouvernance::class, 'programmeId');
        return $this->hasManyThrough(
            PrincipeDeGouvernance::class,  // Final Model
            TypeDeGouvernance::class,  // Intermediate Model
            'programmeId',  // Foreign key on the types_de_gouvernance table
            'typeDeGouvernanceId',  // Foreign key on the principes_de_gouvernance table
            'id',  // Local key on the principes_de_gouvernance table
            'id'  // Local key on the types_de_gouvernance table
        );
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function unitees_de_mesure()
    {
        return $this->hasMany(Unitee::class, 'programmeId');
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function indicateurs_values_keys()
    {
        return $this->hasMany(IndicateurValueKey::class, 'programmeId');
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function indicateurs_valeurs()
    {
        return $this->hasMany(IndicateurValeur::class, 'programmeId');
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function criteres_de_gouvernance()
    {
        return $this->hasMany(CritereDeGouvernance::class, 'programmeId');
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function indicatieurs_de_gouvernance()
    {
        return $this->hasMany(IndicateurDeGouvernance::class, 'programmeId');
    }

    public function fiches_de_synthese()
    {
        return $this->hasMany(FicheDeSynthese::class, 'programmeId');
    }

    public function profiles(?int $evaluationDeGouvernanceId = null, ?int $organisationId = null, ?int $evaluationOrganisationId = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Start with the base relationship
        $profiles = $this->hasMany(ProfileDeGouvernance::class, 'programmeId');

        if ($organisationId) {
            $profiles = $profiles->where('organisationId', $organisationId);
        }

        if ($evaluationDeGouvernanceId) {
            $profiles = $profiles->where('evaluationDeGouvernanceId', $evaluationDeGouvernanceId);
        }

        if ($evaluationOrganisationId) {
            $profiles = $profiles->where('evaluationOrganisationId', $evaluationOrganisationId);
        }

        // Get the results and apply grouping on the collection level
        return $profiles;
    }

    public function actions_a_mener()
    {
        return $this->hasMany(ActionAMener::class, 'programmeId');
    }

    public function recommandations()
    {
        return $this->hasMany(Recommandation::class, 'programmeId');
    }

    public function fonds()
    {
        return $this->hasMany(Fond::class, 'programmeId');
    }

    /**
     * Charger la liste des indicateurs factuel
     */
    public function indicateurs_de_gouvernance_factuel()
    {
        return $this->hasMany(IndicateurDeGouvernanceFactuel::class, 'programmeId');
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function questions_operationnelle()
    {
        return $this->hasMany(QuestionOperationnelle::class, 'programmeId');
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function options_de_reponse_gouvernance()
    {
        return $this->hasMany(OptionDeReponseGouvernance::class, 'programmeId');
    }

    /**
     * Charger la liste des options de gouvernance factuel
     */
    public function options_de_reponse_factuel_gouvernance()
    {
        return $this->hasMany(OptionDeReponseGouvernance::class, 'programmeId')->where('type', 'factuel');
    }

    /**
     * Charger la liste des options de gouvernance de perception
     */
    public function options_de_reponse_de_perception_gouvernance()
    {
        return $this->hasMany(OptionDeReponseGouvernance::class, 'programmeId')->where('type', 'perception');
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function criteres_de_gouvernance_factuel()
    {
        return $this->hasMany(CritereDeGouvernanceFactuel::class, 'programmeId');
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function principes_de_gouvernance_perception()
    {
        return $this->hasMany(PrincipeDeGouvernancePerception::class, 'programmeId');
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function principes_de_gouvernance_factuel()
    {
        return $this->hasMany(PrincipeDeGouvernanceFactuel::class, 'programmeId');
    }

    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function types_de_gouvernance_factuel()
    {
        return $this->hasMany(TypeDeGouvernanceFactuel::class, 'programmeId');
    }

    /**
     * Charger la liste des formulaires factuel de gouvernance du programme
     */
    public function formulaires_factuel_de_gouvernance()
    {
        return $this->hasMany(FormulaireFactuelDeGouvernance::class, 'programmeId');
    }

    /**
     * Charger la liste des formulaires de perception de gouvernance du programme
     */
    public function formulaires_de_perception_gouvernance()
    {
        return $this->hasMany(FormulaireDePerceptionDeGouvernance::class, 'programmeId');
    }

    public function soumissions_factuel()
    {
        return $this->hasMany(SoumissionFactuel::class, 'programmeId');
    }

    public function soumissions_de_perception()
    {
        return $this->hasMany(SoumissionDePerception::class, 'programmeId');
    }
}
