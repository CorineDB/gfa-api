<?php

namespace App\Observers;

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
        $options = [
            'programmeId' => $uniteeDeGestion->programmeId,
            'factuel' => [
                // options de reponse factuel de gouvernance
                ['slug' => 'oui', 'libelle' => 'Oui', 'valeur' => 1, 'soumissionConfiguration' => ['preuveIsRequired' => true, 'descriptionIsRequired' => false, 'sourceDeVerificationIsRequired' => true]],
                ['slug' => 'partiellement', 'libelle' => 'Partiellement', 'valeur' => 0.5, 'preuveIsRequired' => false, 'descriptionIsRequired' => true, 'sourceDeVerificationIsRequired' => false],
                ['slug' => 'non', 'libelle' => 'Non', 'valeur' => 0, 'preuveIsRequired' => false, 'descriptionIsRequired' => false, 'sourceDeVerificationIsRequired' => false],
            ],
            'perception' => [
                // options de reponse perception de gouvernance
                ['slug' => 'ne-peux-repondre', 'libelle' => 'Ne peux répondre', 'valeur' => 0],
                ['slug' => 'pas-du-tout', 'libelle' => 'Pas du tout', 'valeur' => 0],
                ['slug' => 'faiblement', 'libelle' => 'Faiblement', 'valeur' => 0.25],
                ['slug' => 'moyennement', 'libelle' => 'Moyennement', 'valeur' => 0.5],
                ['slug' => 'dans-une-grande-mesure', 'libelle' => 'Dans une grande mesure', 'valeur' => 0.75],
                ['slug' => 'totalement', 'libelle' => 'Totalement', 'valeur' => 1],
            ],
        ];

        /* foreach ($options['perception'] as $option) {
            $uniteeDeGestion->programme->options_de_reponse_de_perception_gouvernance()->create([
                'libelle' => $option['intitule'],
                'type' => 'perception',
            ]);
        }

        foreach ($options['factuel'] as $option) {
            $uniteeDeGestion->programme->options_de_reponse_factuel_gouvernance()->create([
                'libelle' => $option['intitule'],
                'type' => 'factuel',
            ]);
        } */
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
