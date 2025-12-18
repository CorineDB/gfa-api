<?php

namespace Database\Seeders\GFA;

use App\Models\Programme;
use App\Models\Unitee;
use Illuminate\Database\Seeder;

class UniteeDeMesureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $programme = Programme::first();

        if ($programme) {
            $units = [
                // Quantitative
                ['nom' => 'Nombre', 'type' => 'nombre'],
                ['nom' => 'Personne', 'type' => 'personne'],
                ['nom' => 'Ménage', 'type' => 'menage'],
                // Qualitative / Relative
                ['nom' => 'Pourcentage (%)', 'type' => '%'],
                ['nom' => 'Ratio', 'type' => 'ratio'],
                ['nom' => 'Score (0-10)', 'type' => '0-10'],
                ['nom' => 'Indice', 'type' => 'Indice'],
                // Financial
                ['nom' => 'FCFA', 'type' => 'FCFA'],
                ['nom' => 'Euro (€)', 'type' => '€'],
                ['nom' => 'Dollar ($)', 'type' => '$'],
                // Physical
                ['nom' => 'Kilogramme (kg)', 'type' => 'kg'],
                ['nom' => 'Tonne (t)', 'type' => 't'],
                ['nom' => 'Mètre (m)', 'type' => 'm'],
                ['nom' => 'Kilomètre (km)', 'type' => 'km'],
                ['nom' => 'Hectare (ha)', 'type' => 'ha'],
                ['nom' => 'Mètre Carré (m²)', 'type' => 'm²'],
                ['nom' => 'Mètre Cube (m³)', 'type' => 'm³'],
                ['nom' => 'Litre (L)', 'type' => 'L'],
                // Temporal
                ['nom' => 'Jour', 'type' => 'Jour'],
                ['nom' => 'Mois', 'type' => 'Mois'],
                ['nom' => 'Année', 'type' => 'Année'],
                ['nom' => 'Heure', 'type' => 'Heure'],
                // Other
                ['nom' => 'Forfait', 'type' => 'Forfait'],
                ['nom' => 'Oui/Non', 'type' => 'Oui/Non'],
            ];

            foreach ($units as $unit) {
                Unitee::updateOrCreate(
                    [
                        'type' => $unit['type'],
                        'programmeId' => $programme->id
                    ],
                    [
                        'nom' => $unit['nom']
                    ]
                );
            }
        }
    }
}
