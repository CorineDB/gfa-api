<?php

namespace App\Observers;

use App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance;
use App\Models\UniteeDeGestion;

class UniteeDeGestionObserver
{
    /**
     * Handle the UniteeDeGestion "created" event.
     *
     * @param  \App\Models\UniteeDeGestion  $uniteeDeGestion
     * @return void
     */
    public function created(UniteeDeGestion $uniteeDeGestion)
    {
        // On vérifie qu'un programme est bien lié
        if (empty($uniteeDeGestion->programmeId)) {
            return;
        }

        $programmeId = $uniteeDeGestion->programmeId;

        // 1. Définition des options FACTUELLES
        $optionsFactuelles = [
            ['slug' => 'oui', 'libelle' => 'Oui'],
            ['slug' => 'non', 'libelle' => 'Non'],
            ['slug' => 'partiellement', 'libelle' => 'Partiellement'],
        ];

        foreach ($optionsFactuelles as $option) {
            OptionDeReponseGouvernance::firstOrCreate(
                [
                    'libelle'     => $option['libelle'],
                    'slug'        => $option['slug'],
                    'type'        => 'factuel',
                    'programmeId' => $programmeId,
                ]
            );
        }

        // 2. Définition des options de PERCEPTION
        $optionsPerception = [
            ['slug' => 'ne-peux-repondre', 'libelle' => 'Ne peux répondre'],
            ['slug' => 'pas-du-tout', 'libelle' => 'Pas du tout'],
            ['slug' => 'faiblement', 'libelle' => 'Faiblement'],
            ['slug' => 'moyennement', 'libelle' => 'Moyennement'],
            ['slug' => 'dans-une-grande-mesure', 'libelle' => 'Dans une grande mesure'],
            ['slug' => 'totalement', 'libelle' => 'Totalement'],
        ];

        foreach ($optionsPerception as $option) {
            OptionDeReponseGouvernance::firstOrCreate(
                [
                    'libelle'     => $option['libelle'],
                    'slug'        => $option['slug'],
                    'type'        => 'perception',
                    'programmeId' => $programmeId,
                ]
            );
        }
    }

    /**
     * Handle the UniteeDeGestion "updated" event.
     *
     * @param  \App\Models\UniteeDeGestion  $uniteeDeGestion
     * @return void
     */
    public function updated(UniteeDeGestion $uniteeDeGestion)
    {
        //
    }

    /**
     * Handle the UniteeDeGestion "deleted" event.
     *
     * @param  \App\Models\UniteeDeGestion  $uniteeDeGestion
     * @return void
     */
    public function deleted(UniteeDeGestion $uniteeDeGestion)
    {
        //
    }

    /**
     * Handle the UniteeDeGestion "restored" event.
     *
     * @param  \App\Models\UniteeDeGestion  $uniteeDeGestion
     * @return void
     */
    public function restored(UniteeDeGestion $uniteeDeGestion)
    {
        //
    }

    /**
     * Handle the UniteeDeGestion "force deleted" event.
     *
     * @param  \App\Models\UniteeDeGestion  $uniteeDeGestion
     * @return void
     */
    public function forceDeleted(UniteeDeGestion $uniteeDeGestion)
    {
        //
    }
}
