<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Site;

class SiteSeeder extends Seeder
{
    public function run()
    {

    

        // ALIBORI (6 communes)
        Site::updateOrCreate([
            'nom' => 'Site de Banikoara',
            'longitude' => '2.4333',
            'latitude' => '11.3000',
            'commune' => 'BANIKOARA',
            'departement' => 'ALIBORI',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Gogounou',
            'longitude' => '2.8333',
            'latitude' => '10.8333',
            'commune' => 'GOGOUNOU',
            'departement' => 'ALIBORI',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Kandi',
            'longitude' => '2.9386',
            'latitude' => '11.1342',
            'commune' => 'KANDI',
            'departement' => 'ALIBORI',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Karimama',
            'longitude' => '3.3833',
            'latitude' => '12.0667',
            'commune' => 'KARIMAMA',
            'departement' => 'ALIBORI',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Malanville',
            'longitude' => '3.3833',
            'latitude' => '11.8667',
            'commune' => 'MALANVILLE',
            'departement' => 'ALIBORI',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Ségbana',
            'longitude' => '3.2333',
            'latitude' => '10.9333',
            'commune' => 'SEGBANA',
            'departement' => 'ALIBORI',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        // ATACORA (9 communes)
        Site::updateOrCreate([
            'nom' => 'Site de Boukoumbé',
            'longitude' => '1.1064',
            'latitude' => '10.1833',
            'commune' => 'BOUKOUMBE',
            'departement' => 'ATACORA',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Cobly',
            'longitude' => '1.0167',
            'latitude' => '10.1167',
            'commune' => 'COBLY',
            'departement' => 'ATACORA',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Kérou',
            'longitude' => '2.0167',
            'latitude' => '10.8167',
            'commune' => 'KEROU',
            'departement' => 'ATACORA',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Kouandé',
            'longitude' => '1.6833',
            'latitude' => '10.3333',
            'commune' => 'KOUANDE',
            'departement' => 'ATACORA',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Matéri',
            'longitude' => '1.2667',
            'latitude' => '10.6167',
            'commune' => 'MATERI',
            'departement' => 'ATACORA',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Natitingou',
            'longitude' => '1.3761',
            'latitude' => '10.3042',
            'commune' => 'NATITINGOU',
            'departement' => 'ATACORA',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Péhunco',
            'longitude' => '1.7500',
            'latitude' => '10.1167',
            'commune' => 'PEHUNCO',
            'departement' => 'ATACORA',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Tanguiéta',
            'longitude' => '1.2667',
            'latitude' => '10.6167',
            'commune' => 'TANGUIETA',
            'departement' => 'ATACORA',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Toucountouna',
            'longitude' => '1.1667',
            'latitude' => '10.8000',
            'commune' => 'TOUCOUNTOUNA',
            'departement' => 'ATACORA',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        // ATLANTIQUE (8 communes)
        Site::updateOrCreate([
            'nom' => 'Site d\'Abomey-Calavi',
            'longitude' => '2.3567',
            'latitude' => '6.4489',
            'commune' => 'ABOMEY-CALAVI',
            'departement' => 'ATLANTIQUE',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site d\'Allada',
            'longitude' => '2.1519',
            'latitude' => '6.6653',
            'commune' => 'ALLADA',
            'departement' => 'ATLANTIQUE',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Kpomassè',
            'longitude' => '2.1167',
            'latitude' => '6.3833',
            'commune' => 'KPOMASSE',
            'departement' => 'ATLANTIQUE',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site d\'Ouidah',
            'longitude' => '2.0853',
            'latitude' => '6.3619',
            'commune' => 'OUIDAH',
            'departement' => 'ATLANTIQUE',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Sô-Ava',
            'longitude' => '2.4333',
            'latitude' => '6.4667',
            'commune' => 'SO-AVA',
            'departement' => 'ATLANTIQUE',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Toffo',
            'longitude' => '2.0833',
            'latitude' => '6.8500',
            'commune' => 'TOFFO',
            'departement' => 'ATLANTIQUE',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Tori-Bossito',
            'longitude' => '1.9500',
            'latitude' => '6.7833',
            'commune' => 'TORI-BOSSITO',
            'departement' => 'ATLANTIQUE',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Zè',
            'longitude' => '2.3000',
            'latitude' => '6.8833',
            'commune' => 'ZE',
            'departement' => 'ATLANTIQUE',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        // BORGOU (8 communes)
        Site::updateOrCreate([
            'nom' => 'Site de Bembèrèkè',
            'longitude' => '2.6667',
            'latitude' => '10.2167',
            'commune' => 'BEMBEREKE',
            'departement' => 'BORGOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Kalalé',
            'longitude' => '3.3833',
            'latitude' => '10.2833',
            'commune' => 'KALALE',
            'departement' => 'BORGOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de N\'Dali',
            'longitude' => '2.7167',
            'latitude' => '9.8667',
            'commune' => 'N\'DALI',
            'departement' => 'BORGOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Nikki',
            'longitude' => '3.2167',
            'latitude' => '9.9333',
            'commune' => 'NIKKI',
            'departement' => 'BORGOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Parakou',
            'longitude' => '2.6308',
            'latitude' => '9.3372',
            'commune' => 'PARAKOU',
            'departement' => 'BORGOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Pèrèrè',
            'longitude' => '2.8500',
            'latitude' => '9.6833',
            'commune' => 'PERERE',
            'departement' => 'BORGOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Sinendé',
            'longitude' => '2.4167',
            'latitude' => '9.6500',
            'commune' => 'SINENDE',
            'departement' => 'BORGOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Tchaourou',
            'longitude' => '2.5833',
            'latitude' => '8.8833',
            'commune' => 'TCHAOUROU',
            'departement' => 'BORGOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        // COLLINES (6 communes)
        Site::updateOrCreate([
            'nom' => 'Site de Bantè',
            'longitude' => '1.8667',
            'latitude' => '8.4000',
            'commune' => 'BANTE',
            'departement' => 'COLLINES',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Dassa-Zoumè',
            'longitude' => '2.1833',
            'latitude' => '7.7500',
            'commune' => 'DASSA-ZOUME',
            'departement' => 'COLLINES',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Glazoué',
            'longitude' => '2.2333',
            'latitude' => '7.9833',
            'commune' => 'GLAZOUE',
            'departement' => 'COLLINES',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site d\'Ouèssè',
            'longitude' => '2.4167',
            'latitude' => '8.5167',
            'commune' => 'OUESSE',
            'departement' => 'COLLINES',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Savalou',
            'longitude' => '1.9750',
            'latitude' => '7.9286',
            'commune' => 'SAVALOU',
            'departement' => 'COLLINES',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Savè',
            'longitude' => '2.4833',
            'latitude' => '8.0333',
            'commune' => 'SAVE',
            'departement' => 'COLLINES',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        // COUFFO (6 communes)
        Site::updateOrCreate([
            'nom' => 'Site d\'Aplahoué',
            'longitude' => '1.6833',
            'latitude' => '6.9333',
            'commune' => 'APLAHOUE',
            'departement' => 'COUFFO',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Djakotomey',
            'longitude' => '1.6667',
            'latitude' => '7.1167',
            'commune' => 'DJAKOTOMEY',
            'departement' => 'COUFFO',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Dogbo',
            'longitude' => '1.7833',
            'latitude' => '6.8000',
            'commune' => 'DOGBO',
            'departement' => 'COUFFO',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Klouékanmè',
            'longitude' => '1.8500',
            'latitude' => '7.0167',
            'commune' => 'KLOUEKKANME',
            'departement' => 'COUFFO',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Lalo',
            'longitude' => '1.9000',
            'latitude' => '6.9000',
            'commune' => 'LALO',
            'departement' => 'COUFFO',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Toviklin',
            'longitude' => '1.8667',
            'latitude' => '6.5833',
            'commune' => 'TOVIKLIN',
            'departement' => 'COUFFO',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        // DONGA (4 communes)
        Site::updateOrCreate([
            'nom' => 'Site de Bassila',
            'longitude' => '1.6667',
            'latitude' => '9.0167',
            'commune' => 'BASSILA',
            'departement' => 'DONGA',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Copargo',
            'longitude' => '1.3833',
            'latitude' => '9.6167',
            'commune' => 'COPARGO',
            'departement' => 'DONGA',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Djougou',
            'longitude' => '1.6667',
            'latitude' => '9.7000',
            'commune' => 'DJOUGOU',
            'departement' => 'DONGA',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site d\'Ouaké',
            'longitude' => '1.3833',
            'latitude' => '9.5000',
            'commune' => 'OUAKE',
            'departement' => 'DONGA',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        // LITTORAL (1 commune)
        Site::updateOrCreate([
            'nom' => 'Site de Cotonou',
            'longitude' => '2.4281',
            'latitude' => '6.3703',
            'commune' => 'COTONOU',
            'departement' => 'LITTORAL',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        // MONO (6 communes)
        Site::updateOrCreate([
            'nom' => 'Site d\'Athiémé',
            'longitude' => '1.6500',
            'latitude' => '6.5667',
            'commune' => 'ATHIEME',
            'departement' => 'MONO',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Bopa',
            'longitude' => '1.9667',
            'latitude' => '6.4667',
            'commune' => 'BOPA',
            'departement' => 'MONO',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Comè',
            'longitude' => '1.8833',
            'latitude' => '6.4000',
            'commune' => 'COME',
            'departement' => 'MONO',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Grand-Popo',
            'longitude' => '1.8167',
            'latitude' => '6.2833',
            'commune' => 'GRAND-POPO',
            'departement' => 'MONO',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Houéyogbé',
            'longitude' => '1.7000',
            'latitude' => '6.4833',
            'commune' => 'HOUEYOGBE',
            'departement' => 'MONO',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Lokossa',
            'longitude' => '1.7167',
            'latitude' => '6.6386',
            'commune' => 'LOKOSSA',
            'departement' => 'MONO',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        // OUEME (9 communes)
        Site::updateOrCreate([
            'nom' => 'Site d\'Adjarra',
            'longitude' => '2.6667',
            'latitude' => '6.4833',
            'commune' => 'ADJARRA',
            'departement' => 'OUEME',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site d\'Adjohoun',
            'longitude' => '2.4833',
            'latitude' => '6.7333',
            'commune' => 'ADJOHOUN',
            'departement' => 'OUEME',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site d\'Aguégués',
            'longitude' => '2.4167',
            'latitude' => '6.5833',
            'commune' => 'AGUEGUES',
            'departement' => 'OUEME',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site d\'Akpro-Missérété',
            'longitude' => '2.5833',
            'latitude' => '6.5833',
            'commune' => 'AKPRO-MISSERETE',
            'departement' => 'OUEME',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site d\'Avrankou',
            'longitude' => '2.6167',
            'latitude' => '6.6833',
            'commune' => 'AVRANKOU',
            'departement' => 'OUEME',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Bonou',
            'longitude' => '2.3833',
            'latitude' => '6.9000',
            'commune' => 'BONOU',
            'departement' => 'OUEME',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Dangbo',
            'longitude' => '2.5333',
            'latitude' => '6.5833',
            'commune' => 'DANGBO',
            'departement' => 'OUEME',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site d\'Ouinhi',
            'longitude' => '2.5167',
            'latitude' => '7.0833',
            'commune' => 'OUINHI',
            'departement' => 'OUEME',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Porto-Novo',
            'longitude' => '2.6036',
            'latitude' => '6.4969',
            'commune' => 'PORTO-NOVO',
            'departement' => 'OUEME',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        // PLATEAU (5 communes)
        Site::updateOrCreate([
            'nom' => 'Site d\'Adja-Ouèrè',
            'longitude' => '2.8333',
            'latitude' => '7.0167',
            'commune' => 'ADJA-OUERE',
            'departement' => 'PLATEAU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site d\'Ifangni',
            'longitude' => '2.6833',
            'latitude' => '6.6333',
            'commune' => 'IFANGNI',
            'departement' => 'PLATEAU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Kétou',
            'longitude' => '2.6036',
            'latitude' => '7.3633',
            'commune' => 'KETOU',
            'departement' => 'PLATEAU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Pobè',
            'longitude' => '2.6667',
            'latitude' => '6.9833',
            'commune' => 'POBE',
            'departement' => 'PLATEAU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Sakété',
            'longitude' => '2.6500',
            'latitude' => '6.7333',
            'commune' => 'SAKETE',
            'departement' => 'PLATEAU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        // ZOU (9 communes)
        Site::updateOrCreate([
            'nom' => 'Site d\'Abomey',
            'longitude' => '1.9914',
            'latitude' => '7.1817',
            'commune' => 'ABOMEY',
            'departement' => 'ZOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site d\'Agbangnizoun',
            'longitude' => '2.0333',
            'latitude' => '7.1167',
            'commune' => 'AGBANGNIZOUN',
            'departement' => 'ZOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Bohicon',
            'longitude' => '2.0653',
            'latitude' => '7.1772',
            'commune' => 'BOHICON',
            'departement' => 'ZOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Cové',
            'longitude' => '2.2833',
            'latitude' => '7.2167',
            'commune' => 'COVE',
            'departement' => 'ZOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Djidja',
            'longitude' => '1.9333',
            'latitude' => '7.3333',
            'commune' => 'DJIDJA',
            'departement' => 'ZOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site d\'Ouinhi',
            'longitude' => '2.5167',
            'latitude' => '7.0833',
            'commune' => 'OUINHI',
            'departement' => 'ZOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Za-Kpota',
            'longitude' => '1.8833',
            'latitude' => '7.0833',
            'commune' => 'ZA-KPOTA',
            'departement' => 'ZOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Zagnanado',
            'longitude' => '2.3500',
            'latitude' => '7.2167',
            'commune' => 'ZAGNANADO',
            'departement' => 'ZOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
        Site::updateOrCreate([
            'nom' => 'Site de Zogbodomè',
            'longitude' => '2.1000',
            'latitude' => '7.0833',
            'commune' => 'ZOGBODOME',
            'departement' => 'ZOU',
            'pays' => 'Bénin',
            'programmeId' => 1,
        ]);
        
  

    }
}
?>
