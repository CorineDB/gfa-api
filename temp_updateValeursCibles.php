<?php

/**
 * Modifie les valeurs cibles d'un indicateur
 * Gère les indicateurs agrégés et non agrégés avec leurs clés de valeurs
 *
 * @param mixed $indicateur ID ou instance de l'indicateur
 * @param array $attributs Données des valeurs cibles à modifier
 * @return JsonResponse
 */
public function updateValeursCibles($indicateur, array $attributs): JsonResponse
{
    DB::beginTransaction();

    try {
        // Récupération de l'indicateur
        if (is_string($indicateur)) {
            $indicateur = $this->repository->findById($indicateur);
        }

        if (!$indicateur) {
            throw new Exception("Indicateur inconnu", 404);
        }

        // Vérification que l'utilisateur a les droits de modification
        $programme = Auth::user()->programme;

        if ($indicateur->programmeId !== $programme->id) {
            throw new Exception("Vous n'avez pas les droits pour modifier cet indicateur", 403);
        }

        // Validation des données d'entrée
        if (!isset($attributs['anneesCible']) || !is_array($attributs['anneesCible'])) {
            throw new Exception("Les années cibles doivent être fournies sous forme de tableau", 422);
        }

        // Traitement de chaque année cible
        foreach ($attributs['anneesCible'] as $anneeCible) {

            // Validation des données de l'année
            if (!isset($anneeCible['annee'])) {
                throw new Exception("L'année doit être spécifiée pour chaque valeur cible", 422);
            }

            if (!isset($anneeCible['valeurCible'])) {
                throw new Exception("La valeur cible doit être spécifiée pour l'année {$anneeCible['annee']}", 422);
            }

            // Validation de l'année dans la période du programme
            $annee = (int)$anneeCible['annee'];
            $anneeDebut = Carbon::parse($programme->debut)->year;
            $anneeFin = Carbon::parse($programme->fin)->year;

            if ($annee < $anneeDebut || $annee > $anneeFin) {
                throw new Exception("L'année {$annee} doit être comprise entre {$anneeDebut} et {$anneeFin}", 422);
            }

            // Recherche ou création de la valeur cible pour cette année
            $valeurCibleIndicateur = $this->valeurCibleIndicateurRepository
                ->newInstance()
                ->where("cibleable_id", $indicateur->id)
                ->where("cibleable_type", get_class($indicateur))
                ->where("annee", $annee)
                ->first();

            // Si la valeur cible n'existe pas, on la crée
            if (!$valeurCibleIndicateur) {
                $valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->create([
                    "annee" => $annee,
                    "cibleable_id" => $indicateur->id,
                    "cibleable_type" => get_class($indicateur),
                    "programmeId" => $programme->id,
                    "valeurCible" => [] // Sera mis à jour ci-dessous
                ]);
            }

            // Gestion selon le type d'indicateur (agrégé ou simple)
            $valeurCible = [];

            if ($indicateur->agreger) {
                // Indicateur agrégé - les valeurs sont un tableau avec des clés
                if (!is_array($anneeCible["valeurCible"])) {
                    throw new Exception("Pour un indicateur agrégé, les valeurs cibles doivent être un tableau avec les clés correspondantes pour l'année {$annee}", 422);
                }

                // Validation que toutes les clés de l'indicateur ont une valeur
                $indicateurKeys = $indicateur->valueKeys->pluck('id')->toArray();
                $valeursKeys = collect($anneeCible["valeurCible"])->pluck('keyId')->toArray();

                $missingKeys = array_diff($indicateurKeys, $valeursKeys);
                if (!empty($missingKeys)) {
                    throw new Exception("Les clés d'indicateur suivantes sont manquantes dans les valeurs cibles pour l'année {$annee}: " . implode(', ', $missingKeys), 422);
                }

                // Suppression des anciennes valeurs pour cette année
                $valeurCibleIndicateur->valeursCible()->delete();

                // Création des nouvelles valeurs
                foreach ($anneeCible["valeurCible"] as $data) {
                    if (!isset($data['keyId']) || !isset($data['value'])) {
                        throw new Exception("Chaque valeur cible doit contenir 'keyId' et 'value' pour l'année {$annee}", 422);
                    }

                    // Vérification que la clé existe dans l'indicateur
                    $valueKey = $indicateur->valueKeys()->where("indicateur_value_keys.id", $data['keyId'])->first();

                    if (!$valueKey) {
                        throw new Exception("La clé {$data['keyId']} n'est pas associée à cet indicateur", 422);
                    }

                    // Validation que la valeur est numérique si l'unité de mesure l'exige
                    if ($valueKey->pivot->type !== 'text' && !is_numeric($data['value'])) {
                        throw new Exception("La valeur pour la clé '{$valueKey->key}' doit être numérique pour l'année {$annee}", 422);
                    }

                    // Création de la valeur cible
                    $valeur = $valeurCibleIndicateur->valeursCible()->create([
                        "value" => $data["value"],
                        "indicateurValueKeyMapId" => $valueKey->pivot->id,
                        "programmeId" => $programme->id
                    ]);

                    $valeurCible["{$valueKey->key}"] = $valeur->value;
                }

            } else {
                // Indicateur simple - une seule valeur
                if (is_array($anneeCible["valeurCible"])) {
                    throw new Exception("Pour un indicateur simple, la valeur cible doit être une valeur unique pour l'année {$annee}", 422);
                }

                // Validation que la valeur est numérique si nécessaire
                $valueKey = $indicateur->valueKey();
                if (!$valueKey) {
                    throw new Exception("Aucune clé de valeur trouvée pour cet indicateur", 500);
                }

                if ($valueKey->pivot->type !== 'text' && !is_numeric($anneeCible["valeurCible"])) {
                    throw new Exception("La valeur cible doit être numérique pour l'année {$annee}", 422);
                }

                // Suppression de l'ancienne valeur
                $valeurCibleIndicateur->valeursCible()->delete();

                // Création de la nouvelle valeur
                $valeur = $valeurCibleIndicateur->valeursCible()->create([
                    "value" => $anneeCible["valeurCible"],
                    "indicateurValueKeyMapId" => $valueKey->pivot->id,
                    "programmeId" => $programme->id
                ]);

                $valeurCible["{$valueKey->key}"] = $valeur->value;
            }

            // Mise à jour de la valeur cible consolidée
            $valeurCibleIndicateur->valeurCible = $valeurCible;
            $valeurCibleIndicateur->save();
        }

        // Rafraîchissement de l'indicateur pour obtenir les nouvelles données
        $indicateur->refresh();

        // Logging de l'activité
        $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
        $message = Str::ucfirst($acteur) . " a modifié les valeurs cibles de l'indicateur " . $indicateur->nom;

        // LogActivity::addToLog("Modification valeurs cibles", $message, get_class($indicateur), $indicateur->id);

        DB::commit();

        // Nettoyage du cache
        Cache::forget('indicateurs');
        Cache::forget('indicateurs-' . $indicateur->id);

        return response()->json([
            'statut' => 'success',
            'message' => 'Valeurs cibles mises à jour avec succès',
            'data' => new IndicateursResource($indicateur),
            'statutCode' => Response::HTTP_OK
        ], Response::HTTP_OK);

    } catch (\Throwable $th) {
        DB::rollBack();

        return response()->json([
            'statut' => 'error',
            'message' => $th->getMessage(),
            'errors' => [],
            'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

/**
 * Modifie une valeur cible spécifique pour une année donnée
 *
 * @param mixed $indicateur ID ou instance de l'indicateur
 * @param int $annee Année de la valeur cible
 * @param array $valeurCible Nouvelle valeur cible
 * @return JsonResponse
 */
public function updateValeurCibleAnnee($indicateur, int $annee, array $valeurCible): JsonResponse
{
    return $this->updateValeursCibles($indicateur, [
        'anneesCible' => [
            [
                'annee' => $annee,
                'valeurCible' => $valeurCible
            ]
        ]
    ]);
}

/**
 * Supprime une valeur cible pour une année donnée
 *
 * @param mixed $indicateur ID ou instance de l'indicateur
 * @param int $annee Année de la valeur cible à supprimer
 * @return JsonResponse
 */
public function deleteValeurCibleAnnee($indicateur, int $annee): JsonResponse
{
    DB::beginTransaction();

    try {
        // Récupération de l'indicateur
        if (is_string($indicateur)) {
            $indicateur = $this->repository->findById($indicateur);
        }

        if (!$indicateur) {
            throw new Exception("Indicateur inconnu", 404);
        }

        // Vérification des droits
        $programme = Auth::user()->programme;
        if ($indicateur->programmeId !== $programme->id) {
            throw new Exception("Vous n'avez pas les droits pour modifier cet indicateur", 403);
        }

        // Recherche de la valeur cible
        $valeurCibleIndicateur = $this->valeurCibleIndicateurRepository
            ->newInstance()
            ->where("cibleable_id", $indicateur->id)
            ->where("cibleable_type", get_class($indicateur))
            ->where("annee", $annee)
            ->first();

        if (!$valeurCibleIndicateur) {
            throw new Exception("Aucune valeur cible trouvée pour l'année {$annee}", 404);
        }

        // Vérification qu'il n'y a pas de suivis associés
        if ($valeurCibleIndicateur->suivisIndicateur()->count() > 0) {
            throw new Exception("Impossible de supprimer cette valeur cible car des suivis y sont associés", 422);
        }

        // Suppression des valeurs détaillées et de la valeur cible
        $valeurCibleIndicateur->valeursCible()->delete();
        $valeurCibleIndicateur->delete();

        // Logging
        $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
        $message = Str::ucfirst($acteur) . " a supprimé la valeur cible de l'année {$annee} pour l'indicateur " . $indicateur->nom;

        // LogActivity::addToLog("Suppression valeur cible", $message, get_class($indicateur), $indicateur->id);

        DB::commit();

        // Nettoyage du cache
        Cache::forget('indicateurs');
        Cache::forget('indicateurs-' . $indicateur->id);

        return response()->json([
            'statut' => 'success',
            'message' => "Valeur cible de l'année {$annee} supprimée avec succès",
            'statutCode' => Response::HTTP_OK
        ], Response::HTTP_OK);

    } catch (\Throwable $th) {
        DB::rollBack();

        return response()->json([
            'statut' => 'error',
            'message' => $th->getMessage(),
            'errors' => [],
            'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
