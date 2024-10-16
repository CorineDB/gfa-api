<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface PrincipeDeGouvernanceServiceInterface
* @package Core\Services\Interfaces
*/
interface PrincipeDeGouvernanceServiceInterface
{


    /**
     * Liste des criteres de gouvernance d'un principe
     * 
     */
    public function criteres($principeDeGouvernanceId, array $attributs = ['*'], array $relations = []): JsonResponse;

    /**
     * Liste des indicateurs de gouvernance d'un principe
     * 
     */
    public function indicateurs($principeDeGouvernanceId, array $attributs = ['*'], array $relations = []): JsonResponse;

    /**
     * Charger le formulaire de l'outil de perception du programme associé à l'utilisateur connecté
     * @param array $attributs listes des attributs a recuperer
     * @param array $relations listes des relations a charger
     * @return JsonResponse
     */
    public function formulaire_factuel(array $attributs = ['*'], array $relations = []): JsonResponse;

    /**
     * Charger le formulaire de l'outil de perception du programme associé à l'utilisateur connecté
     * 
     * @param array $attributs Liste des attributs à récupérer
     * @param array $relations Liste des relations à charger
     * @return JsonResponse
     */
    public function formulaire_de_perception(array $attributs = ['*'], array $relations = []): JsonResponse;

}
