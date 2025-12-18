<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class Permissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creer les roles hardcodé, les permissions et les liées';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dossier = './help/permissions/';

        $roles = [
            'Administrateur',
            'Unitee de gestion',
            'Organisation',
            'DDC'  /* ,
              'Bailleur',
              "MOD",
              "Mission de controle",
              "ONG",
              "AGENCE",
              "Entreprise executant",
              "Entreprise ete institution",
              "Comptable",
              "Expert suivi évaluation",
              "Gouvernement" */
        ];

        $roles_slugs = [
            'administrateur',
            'unitee-de-gestion',
            'organisation',
            'ddc'  /* ,
              'bailleur',
              "mod",
              "mission-de-controle",
              "ong",
              "agence",
              "entreprise-executant",
              "institution",
              "comptable",
              "expert-suivi-evaluation",
              "gouvernement" */
        ];

        $actions = [
            'Creer',
            'Modifier',
            'Supprimer',
            'Voir'
        ];
        $modules = [
            'un utilisateur',
            'une organisation',
            'un ddc',
            'un decaissement',
            'un programme',
            'un projet',
            'une activite',
            'une tache',
            'un indicateur',
            'une unitee de gestion',
            'un plan de decaissement',
            'un suivi financier',
            'un site',
            'un role',
            'un suivi',
            'un rappel',
            'un rapport',
            'une configuration alerte',
            'une categorie',
            'un suivi indicateur',
            'un fichier',
            'une unitee de mesure',
            'un audit',
            'un fond',
            'un outcome',
            'un output',
            'une cle de valeur indicateur',
            'une option de reponse',
            'une source de verification',
            'une categorie indicateur',
            'un indicateur de gouvernance',
            'une question operationnelle',
            'un type de gouvernance',
            'un principe de gouvernance',
            'un critere de gouvernance',
            'un formulaire de gouvernance',
            'une evaluation de gouvernance',
            'une soumission',
            'une fiche de synthese',
            'un profil de gouvernance',
            'une recommandation',
            'une action a mener',
            'une enquete individuelle',
            'un formulaire individuel',
            'une configuration alerte',
            /* 'un bailleur',
            'un mod',
            'une entreprise executante',
            'une mission de controle',
            'une composante',
            'un indicateur mod',
            'une ong',
            'une agence',
            'unee institution',
            'un gouvernement',
            'un ano',
            'une reponse ano',
            'un pap',
            'une activite environnementale',
            'une activite environnementale mod',
            'une checklist',
            'une passation',
            'une revision',
            'un suivi environnementale',
            'un suivi indicateur mod',
            'un formulaire',
            'une maitrise oeuvre'*/
        ];

        $autres = [
            'voir ptab',
            'faire revision ptab',
            'voir revision ptab',
            'voir le plan de decaissement du ptab',
            'voir une permission',
            'attribuer une permission',
            'retirer une permission',
            'voir le point financier des activites',
            'faire un backup',
            'voir un historique',
            'alerte tache',
            'alerte activite',
            'alerte suivi financier',
            'alerte suivi indicateur',
            'modifier une frequence de sauvegarde',
            'voir statistique activite',
            'prolonger un projet',
            'validation',
            'voir details projet',
            'prolonger une tache',
            'prolonger une activite',
            'exporter un suivi indicateur',
            'exporter un decaissement',
            'exporter un plan decaissement',
            'exporter un suivi financier',
            'exporter un suivi ptab',
            'exporter un suivi ptab revise',
            'importer un suivi financier',
            'voir une statistique activite',
            'voir suivi kobo',
            'voir formulaire kobo',
            'voir formulaire factuel',
            'voir details soumission',
            'valider un suivi indicateur',
            'valider une soumission',
            'valider une action a mener',
            'signaler une action a mener est realise',
            'voir plan action',
            'voir statistique evolution des profiles de gouvernance au fil du temps',
            'ajouter nombre de participant',
            'envoyer une invitation',
            'envoyer un rappel soumission',
            'alerte validation action a mener',
            'voir resultats evaluation',
            'alerte action a mener',
            'alerte action a mener realise',
            'alerte evaluation de gouvernance',
            'alerte resultats evaluation',
            'alerte creer plan action',
            'alerte definition nombre de participant',
            'exporter resultats evaluation',
            'exporter fiche synthese',
            'exporter plan action',
            'filtrer indicateur',
            'ajouter une cle de valeur indicateur',
            'supprimer une cle de valeur indicateur',
            'voir cadre de rendement',
            'voir reponses enquete individuelle',
            'supprimer une reponse enquete individuelle',
            /*'voir ppm',
            'alerte creer rapport entreprise',
            'alerte creer rapport missionDeControle',
            'alerte creer rapport chefEnvironnement',
            'voir formulaire mod',
            'voir formulaire mission de controle',
            'voir formulaire entreprise executant',
            'exporter un suivi ppm',
            'exporter un suivi ppm revise',
            'exporter un pap',
            'importer un pap'*/
        ];

        $ddcs = [
            'voir-un-utilisateur',
            'creer-un-utilisateur',
            'modifier-un-utilisateur',
            'voir-un-role',
            'creer-un-role',
            'modifier-un-role',
            'voir-un-decaissement',
            'voir-un-projet',
            'voir-une-composante',
            'voir-une-activite',
            'voir-une-tache',
            'voir-un-indicateur',
            //
            'voir-formulaire-factuel',
            'voir-details-soumission',
            'voir-plan-action',
            'voir-statistique-evolution-des-profiles-de-gouvernance-au-fil-du-temps',
            'voir-resultats-evaluation',
            'exporter-resultats-evaluation',
            'exporter-fiche-synthese',
            'exporter-plan-action',
            'filtrer-indicateur',
            'voir-cadre-de-rendement'
        ];

        $organisations = [
            'voir-un-utilisateur',
            'creer-un-utilisateur',
            'modifier-un-utilisateur',
            'supprimer-un-utilisateur',
            'voir-un-role',
            'creer-un-role',
            'modifier-un-role',
            'supprimer-un-role',
            'voir-un-decaissement',
            'voir-un-projet',
            'voir-un-outcome',
            'creer-un-outcome',
            'modifier-un-outcome',
            'supprimer-un-outcome',
            'voir-un-output',
            'creer-un-output',
            'modifier-un-output',
            'supprimer-un-output',
            'voir-une-activite',
            'creer-une-activite',
            'modifier-une-activite',
            'supprimer-une-activite',
            'voir-un-plan-de-decaissement',
            'creer-un-plan-de-decaissement',
            'modifier-un-plan-de-decaissement',
            'supprimer-un-plan-de-decaissement',
            'voir-une-tache',
            'creer-une-tache',
            'modifier-une-tache',
            'supprimer-une-tache',
            'voir-un-indicateur',
            'voir-une-unitee-de-mesure',
            'voir-un-site',
            'voir-une-categorie',
            'voir-un-suivi-financier',
            'creer-un-suivi-financier',
            'modifier-un-suivi-financier',
            'supprimer-un-suivi-financier',
            'voir-un-suivi-indicateur',
            'creer-un-suivi-indicateur',
            'modifier-un-suivi-indicateur',
            'supprimer-un-suivi-indicateur',
            'voir-un-suivi',
            'creer-un-suivi',
            'modifier-un-suivi',
            'supprimer-un-suivi',
            'voir-une-cle-de-valeur-indicateur',
            'voir-une-option-de-reponse',
            'voir-une-source-de-verification',
            'voir-une-categorie-indicateur',
            'voir-un-indicateur-de-gouvernance',
            'voir-un-type-de-gouvernance',
            'voir-un-principe-de-gouvernance',
            'voir-un-critere-de-gouvernance',
            'voir-une-question-operationnelle',
            'voir-un-fond',
            'voir-un-fichier',
            'supprimer-un-fichier',
            'creer-un-fichier',
            'voir-un-formulaire-de-gouvernance',
            'voir-une-evaluation-de-gouvernance',
            'voir-une-soumission',
            'creer-une-soumission',
            'voir-une-fiche-de-synthese',
            'voir-un-profil-de-gouvernance',
            'voir-une-recommandation',
            'creer-une-recommandation',
            'modifier-une-recommandation',
            'supprimer-une-recommandation',
            'voir-une-action-a-mener',
            'creer-une-action-a-mener',
            'modifier-une-action-a-mener',
            'supprimer-une-action-a-mener',
            'voir-une-enquete-individuelle',
            'creer-une-enquete-individuelle',
            'modifier-une-enquete-individuelle',
            'supprimer-une-enquete-individuelle',
            'voir-un-formulaire-individuel',
            'creer-un-formulaire-individuel',
            'modifier-un-formulaire-individuel',
            'supprimer-un-formulaire-individuel',
            'voir-ptab',
            'voir-le-plan-de-decaissement-du-ptab',
            'voir-une-permission',
            'attribuer-une-permission',
            'retirer-une-permission',
            'voir-le-point-financier-des-activites',
            'alerte-tache',
            'alerte-activite',
            'voir-statistique-activite',
            'prolonger-une-tache',
            'prolonger-une-activite',
            'validation',
            'voir-details-projet',
            'exporter-un-suivi-indicateur',
            'exporter-un-decaissement',
            'exporter-un-plan-decaissement',
            'exporter-un-suivi-financier',
            'exporter-un-suivi-ptab',
            'importer-un-suivi-financier',
            'voir-une-statistique-activite',
            'voir-formulaire-factuel',
            'voir-details-soumission',
            'valider-une-soumission',
            'signaler-une-action-a-mener-est-realise',
            'voir-plan-action',
            'voir-statistique-evolution-des-profiles-de-gouvernance-au-fil-du-temps',
            'ajouter-nombre-de-participant',
            'envoyer-une-invitation',
            'envoyer-un-rappel-soumission',
            'alerte-validation-action-a-mener',
            'alerte-action-a-mener',
            'voir-resultats-evaluation',
            'alerte-evaluation-de-gouvernance',
            'alerte-resultats-evaluation',
            'alerte-creer-plan-action',
            'alerte-definition-nombre-de-participant',
            'exporter-resultats-evaluation',
            'exporter-fiche-synthese',
            'exporter-plan-action',
            'filtrer-indicateur',
            'voir-cadre-de-rendement',
            'voir-reponses-enquete-individuelle',
            'supprimer-une-reponse-enquete-individuelle',
            'voir-un-rapport',
            'modifier-un-rapport',
            'supprimer-un-rapport',
            'creer-un-rapport'
        ];

        $administrateurs = [
            'voir-un-programme',
            'creer-un-programme',
            'modifier-un-programme',
            'supprimer-un-programme',
            'voir-un-utilisateur',
            'creer-un-utilisateur',
            'modifier-un-utilisateur',
            'supprimer-un-utilisateur',
            'voir-un-ddc',
            'creer-un-ddc',
            'modifier-un-ddc',
            'supprimer-un-ddc',
            'voir-une-organisation',
            'creer-une-organisation',
            'modifier-une-organisation',
            'supprimer-une-organisation',
            'voir-une-permission',
            'voir-un-role',
            'creer-un-role',
            'modifier-un-role',
            'supprimer-un-role',
            'voir-une-unitee-de-gestion',
            'modifier-une-unitee-de-gestion',
            'creer-une-unitee-de-gestion',
            'supprimer-une-unitee-de-gestion',
            'voir-un-historique',
            'faire-un-backup'
        ];

        $uniteeDeGestion = [
            'voir-un-programme',
            'voir-un-ddc',
            'voir-un-historique',
            'faire-un-backup',
            'voir-une-configuration-alerte',
            'creer-une-configuration-alerte',
            'modifier-une-configuration-alerte',
            'supprimer-une-configuration-alerte',
            'voir-une-organisation',
            'creer-une-organisation',
            'modifier-une-organisation',
            'supprimer-une-organisation',
            'voir-un-projet',
            'creer-un-projet',
            'modifier-un-projet',
            'supprimer-un-projet',
            'voir-un-outcome',
            'creer-un-outcome',
            'modifier-un-outcome',
            'supprimer-un-outcome',
            'voir-un-output',
            'creer-un-output',
            'modifier-un-output',
            'supprimer-un-output',
            'voir-une-activite',
            'creer-une-activite',
            'modifier-une-activite',
            'supprimer-une-activite',
            'voir-un-decaissement',
            'creer-un-decaissement',
            'modifier-un-decaissement',
            'supprimer-un-decaissement',
            'voir-un-plan-de-decaissement',
            'creer-un-plan-de-decaissement',
            'modifier-un-plan-de-decaissement',
            'supprimer-un-plan-de-decaissement',
            'voir-une-tache',
            'creer-une-tache',
            'modifier-une-tache',
            'supprimer-une-tache',
            'voir-une-categorie',
            'creer-une-categorie',
            'modifier-une-categorie',
            'supprimer-une-categorie',
            'voir-un-indicateur',
            'creer-un-indicateur',
            'modifier-un-indicateur',
            'supprimer-un-indicateur',
            'voir-un-suivi-financier',
            'creer-un-suivi-financier',
            'modifier-un-suivi-financier',
            'supprimer-un-suivi-financier',
            'voir-un-suivi-indicateur',
            'creer-un-suivi-indicateur',
            'modifier-un-suivi-indicateur',
            'supprimer-un-suivi-indicateur',
            'valider-un-suivi-indicateur',
            'voir-un-suivi',
            'creer-un-suivi',
            'modifier-un-suivi',
            'supprimer-un-suivi',
            'voir-une-cle-de-valeur-indicateur',
            'creer-une-cle-de-valeur-indicateur',
            'modifier-une-cle-de-valeur-indicateur',
            'supprimer-une-cle-de-valeur-indicateur',
            'voir-une-option-de-reponse',
            'creer-une-option-de-reponse',
            'modifier-une-option-de-reponse',
            'supprimer-une-option-de-reponse',
            'voir-une-source-de-verification',
            'creer-une-source-de-verification',
            'modifier-une-source-de-verification',
            'supprimer-une-source-de-verification',
            'voir-une-categorie-indicateur',
            'creer-une-categorie-indicateur',
            'modifier-une-categorie-indicateur',
            'supprimer-une-categorie-indicateur',
            'voir-un-indicateur-de-gouvernance',
            'creer-un-indicateur-de-gouvernance',
            'modifier-un-indicateur-de-gouvernance',
            'supprimer-un-indicateur-de-gouvernance',
            'voir-un-type-de-gouvernance',
            'creer-un-type-de-gouvernance',
            'modifier-un-type-de-gouvernance',
            'supprimer-un-type-de-gouvernance',
            'voir-un-principe-de-gouvernance',
            'creer-un-principe-de-gouvernance',
            'modifier-un-principe-de-gouvernance',
            'supprimer-un-principe-de-gouvernance',
            'voir-un-critere-de-gouvernance',
            'creer-un-critere-de-gouvernance',
            'modifier-un-critere-de-gouvernance',
            'supprimer-un-critere-de-gouvernance',
            'voir-une-question-operationnelle',
            'creer-une-question-operationnelle',
            'modifier-une-question-operationnelle',
            'supprimer-une-question-operationnelle',
            'voir-un-fond',
            'creer-un-fond',
            'modifier-un-fond',
            'supprimer-un-fond',
            'voir-un-site',
            'creer-un-site',
            'modifier-un-site',
            'supprimer-un-site',
            'voir-un-role',
            'creer-un-role',
            'modifier-un-role',
            'supprimer-un-role',
            'voir-un-utilisateur',
            'creer-un-utilisateur',
            'modifier-un-utilisateur',
            'supprimer-un-utilisateur',
            'voir-un-fichier',
            'modifier-un-fichier',
            'supprimer-un-fichier',
            'creer-un-fichier',
            'voir-un-audit',
            'modifier-un-audit',
            'supprimer-un-audit',
            'creer-un-audit',
            'voir-un-rappel',
            'modifier-un-rappel',
            'supprimer-un-rappel',
            'creer-un-rappel',
            'voir-un-rapport',
            'modifier-un-rapport',
            'supprimer-un-rapport',
            'creer-un-rapport',
            'voir-un-formulaire-de-gouvernance',
            'creer-un-formulaire-de-gouvernance',
            'modifier-un-formulaire-de-gouvernance',
            'supprimer-un-formulaire-de-gouvernance',
            'voir-une-evaluation-de-gouvernance',
            'creer-une-evaluation-de-gouvernance',
            'modifier-une-evaluation-de-gouvernance',
            'supprimer-une-evaluation-de-gouvernance',
            'voir-une-recommandation',
            'creer-une-recommandation',
            'modifier-une-recommandation',
            'supprimer-une-recommandation',
            'voir-une-action-a-mener',
            'creer-une-action-a-mener',
            'modifier-une-action-a-mener',
            'supprimer-une-action-a-mener',
            'voir-une-enquete-individuelle',
            'creer-une-enquete-individuelle',
            'modifier-une-enquete-individuelle',
            'supprimer-une-enquete-individuelle',
            'voir-un-formulaire-individuel',
            'creer-un-formulaire-individuel',
            'modifier-un-formulaire-individuel',
            'supprimer-un-formulaire-individuel',
            'voir-une-soumission',
            'voir-details-soumission',
            'valider-une-soumission',
            'voir-une-fiche-de-synthese',
            'voir-un-profil-de-gouvernance',
            'voir-ptab',
            'faire-revision-ptab',
            'voir-revision-ptab',
            'voir-le-plan-de-decaissement-du-ptab',
            'voir-une-permission',
            'attribuer-une-permission',
            'retirer-une-permission',
            'voir-le-point-financier-des-activites',
            'alerte-tache',
            'alerte-activite',
            'voir-statistique-activite',
            'validation',
            'exporter-un-plan-decaissement',
            'valider-une-action-a-mener',
            'voir-plan-action',
            'voir-statistique-evolution-des-profiles-de-gouvernance-au-fil-du-temps',
            'ajouter-nombre-de-participant',
            'envoyer-une-invitation',
            'envoyer-un-rappel-soumission',
            'alerte-validation-action-a-mener',
            'alerte-evaluation-de-gouvernance',
            'alerte-resultats-evaluation',
            'alerte-creer-plan-action',
            'exporter-resultats-evaluation',
            'exporter-fiche-synthese',
            'filtrer-indicateur',
            'alerte-suivi-financier',
            'alerte-suivi-indicateur',
            'modifier-une-frequence-de-sauvegarde',
            'prolonger-un-projet',
            'validation',
            'voir-details-projet',
            'prolonger-une-tache',
            'prolonger-une-activite',
            'exporter-un-suivi-indicateur',
            'exporter-un-decaissement',
            'exporter-un-plan-decaissement',
            'exporter-un-suivi-financier',
            'exporter-un-suivi-ptab',
            'exporter-un-suivi-ptab-revise',
            'importer-un-suivi-financier',
            'voir-une-statistique-activite',
            'voir-suivi-kobo',
            'voir-formulaire-kobo',
            'voir-formulaire-factuel',
            'valider-un-suivi-indicateur',
            'signaler-une-action-a-mener-est-realise',
            'voir-resultats-evaluation',
            'alerte-action-a-mener',
            'alerte-action-a-mener-realise',
            'alerte-evaluation-de-gouvernance',
            'alerte-definition-nombre-de-participant',
            'exporter-plan-action',
            'ajouter-une-cle-de-valeur-indicateur',
            'voir-cadre-de-rendement',
            'voir-reponses-enquete-individuelle',
            'supprimer-une-reponse-enquete-individuelle'
        ];

        /*
         * $bailleurs = [
         *     'voir-un-utilisateur',
         *     'creer-un-utilisateur',
         *     'modifier-un-utilisateur',
         *     'voir-un-role',
         *     'creer-un-role',
         *     'modifier-un-role',
         *     'creer-un-bailleur',
         *     'voir-un-decaissement',
         *     'voir-un-mod',
         *     'voir-une-entreprise-executante',
         *     'voir-une-mission-de-controle',
         *     'voir-un-projet',
         *     'voir-une-composante',
         *     'voir-une-activite',
         *     'voir-une-tache',
         *     'voir-un-indicateur',
         *     'voir-un-ano',
         *     'voir-une-reponse-ano',
         *     'creer-une-reponse-ano',
         *     'modifier-une-reponse-ano',
         *     'voir-un-plan-de-decaissement',
         *     'voir-un-suivi-financier',
         *     'voir-un-pap',
         *     'voir-une-activite-environnementale',
         *     'voir-un-site',
         *     'voir-une-checklist',
         *     'voir-une-passation',
         *     'voir-une-revision',
         *     'voir-une-categorie',
         *     'voir-un-suivi-environnementale',
         *     'voir-un-suivi-indicateur',
         *     'voir-un-suivi-financier',
         *     'creer-un-rappel',
         *     'voir-un-fichier',
         *     'voir-ptab',
         *     'creer-un-fichier',
         *     'voir-details-projet',
         *     'voir-une-permission',
         *     'exporter-un-suivi-indicateur',
         *     'exporter-un-decaissement',
         *     'exporter-un-plan-decaissement',
         *     'exporter-un-suivi-financier',
         *     'exporter-un-suivi-ppm',
         *     'exporter-un-suivi-ptab',
         *     'exporter-un-suivi-ppm-revise',
         *     'exporter-un-suivi-ptab-revise',
         *     'exporter-un-pap'
         * ];
         *
         * $gouvernements = [
         *     'voir-un-utilisateur',
         *     'creer-un-utilisateur',
         *     'modifier-un-utilisateur',
         *     'voir-un-role',
         *     'creer-un-role',
         *     'modifier-un-role',
         *     'voir-un-gouvernement',
         *     'modifier-un-gouvernement',
         *     'creer-un-gouvernement',
         *     'voir-un-decaissement',
         *     'voir-un-mod',
         *     'voir-une-entreprise-executante',
         *     'voir-une-mission-de-controle',
         *     'voir-un-projet',
         *     'voir-une-composante',
         *     'voir-une-activite',
         *     'voir-une-tache',
         *     'voir-un-indicateur',
         *     'voir-un-ano',
         *     'voir-une-reponse-ano',
         *     'voir-un-plan-de-decaissement',
         *     'voir-un-suivi-financier',
         *     'voir-un-pap',
         *     'voir-une-activite-environnementale',
         *     'voir-un-site',
         *     'voir-une-checklist',
         *     'voir-une-passation',
         *     'voir-une-revision',
         *     'voir-une-categorie',
         *     'voir-un-suivi-environnementale',
         *     'voir-un-suivi-indicateur',
         *     'voir-un-suivi-financier',
         *     'voir-une-permission',
         *     'voir-ptab',
         *     'creer-un-rappel',
         *     'voir-un-fichier',
         *     'creer-un-fichier',
         *     'voir-details-projet',
         *     'exporter-un-suivi-indicateur',
         *     'exporter-un-decaissement',
         *     'exporter-un-plan-decaissement',
         *     'exporter-un-suivi-financier',
         *     'exporter-un-suivi-ppm',
         *     'exporter-un-suivi-ptab',
         *     'exporter-un-suivi-ppm-revise',
         *     'exporter-un-suivi-ptab-revise',
         *     'exporter-un-pap'
         * ];
         *
         * $instituts = [
         *     'voir-un-utilisateur',
         *     'creer-un-utilisateur',
         *     'modifier-un-utilisateur',
         *     'voir-un-role',
         *     'creer-un-role',
         *     'modifier-un-role',
         *     'voir-une-institution',
         *     'modifier-une-institution',
         *     'creer-une-institution',
         *     'voir-un-decaissement',
         *     'voir-un-mod',
         *     'voir-une-entreprise-executante',
         *     'voir-une-mission-de-controle',
         *     'voir-un-projet',
         *     'voir-une-composante',
         *     'voir-une-activite',
         *     'voir-une-tache',
         *     'voir-un-indicateur',
         *     'voir-un-ano',
         *     'voir-une-reponse-ano',
         *     'voir-un-plan-de-decaissement',
         *     'voir-un-suivi-financier',
         *     'voir-un-pap',
         *     'voir-une-activite-environnementale',
         *     'voir-un-site',
         *     'voir-une-checklist',
         *     'voir-une-passation',
         *     'voir-une-revision',
         *     'voir-une-categorie',
         *     'voir-un-suivi-environnementale',
         *     'voir-un-suivi-indicateur',
         *     'voir-un-suivi-financier',
         *     'voir-ptab',
         *     'creer-un-rappel',
         *     'voir-un-fichier',
         *     'creer-un-fichier',
         *     'voir-details-projet',
         *     'voir-une-permission',
         *     'exporter-un-suivi-indicateur',
         *     'exporter-un-decaissement',
         *     'exporter-un-plan-decaissement',
         *     'exporter-un-suivi-financier',
         *     'exporter-un-suivi-ppm',
         *     'exporter-un-suivi-ptab',
         *     'exporter-un-suivi-ppm-revise',
         *     'exporter-un-suivi-ptab-revise',
         *     'exporter-un-pap'
         * ];
         *
         * $mods = [
         *     'voir-un-utilisateur',
         *     'creer-un-utilisateur',
         *     'modifier-un-utilisateur',
         *     'voir-un-role',
         *     'creer-un-role',
         *     'modifier-un-role',
         *     'voir-une-institution',
         *     'voir-un-decaissement',
         *     'voir-un-mod',
         *     'voir-une-entreprise-executante',
         *     'voir-une-mission-de-controle',
         *     'voir-un-projet',
         *     'voir-une-composante',
         *     'voir-une-activite',
         *     'voir-une-tache',
         *     'voir-un-indicateur',
         *     'voir-un-ano',
         *     'voir-une-reponse-ano',
         *     'voir-un-plan-de-decaissement',
         *     'voir-un-suivi-financier',
         *     'voir-un-pap',
         *     'voir-une-activite-environnementale',
         *     'voir-un-site',
         *     'voir-une-checklist',
         *     'voir-une-passation',
         *     'voir-une-revision',
         *     'voir-une-categorie',
         *     'voir-un-suivi-environnementale',
         *     'voir-un-suivi-indicateur',
         *     'voir-un-suivi-financier',
         *     'voir-ptab',
         *     'creer-un-rappel',
         *     'voir-un-fichier',
         *     'creer-un-fichier',
         *     'voir-details-projet',
         *     'voir-une-permission',
         *     'exporter-un-suivi-indicateur',
         *     'exporter-un-decaissement',
         *     'exporter-un-plan-decaissement',
         *     'exporter-un-suivi-financier',
         *     'exporter-un-suivi-ppm',
         *     'exporter-un-suivi-ptab',
         *     'exporter-un-suivi-ppm-revise',
         *     'exporter-un-suivi-ptab-revise',
         *     'exporter-un-pap'
         * ];
         *
         * $missionDeControles = [
         *     'voir-un-utilisateur',
         *     'creer-un-utilisateur',
         *     'modifier-un-utilisateur',
         *     'voir-un-role',
         *     'creer-un-role',
         *     'modifier-un-role',
         *     'voir-un-formulaire',
         *     'creer-un-formulaire',
         *     'modifier-un-formulaire',
         *     'voir-une-mission-de-controle',
         *     'modifier-une-mission-de-controle',
         *     'creer-une-mission-de-controle',
         *     'creer-un-suivi-environnementale',
         *     'modifier-un-suivi-environnementale',
         *     'voir-un-suivi-environnementale',
         *     'supprimer-un-suivi-environnementale',
         *     'voir-un-decaissement',
         *     'voir-une-entreprise-executante',
         *     'voir-un-projet',
         *     'voir-une-composante',
         *     'voir-une-activite',
         *     'voir-une-tache',
         *     'voir-un-indicateur',
         *     'voir-une-activite-environnementale',
         *     'voir-un-site',
         *     'voir-une-checklist',
         *     'voir-une-passation',
         *     'voir-une-revision',
         *     'voir-une-categorie',
         *     'voir-un-suivi-environnementale',
         *     'voir-ptab',
         *     'voir-formulaire-mission-de-controle',
         *     'creer-un-rappel',
         *     'voir-un-fichier',
         *     'creer-un-fichier',
         *     'voir-details-projet',
         *     'voir-une-permission',
         *     'exporter-un-suivi-indicateur',
         *     'exporter-un-suivi-ppm',
         *     'exporter-un-suivi-ptab',
         *     'exporter-un-suivi-ppm-revise',
         *     'exporter-un-suivi-ptab-revise'
         * ];
         *
         * $entreprises = [
         *     'voir-un-utilisateur',
         *     'creer-un-utilisateur',
         *     'modifier-un-utilisateur',
         *     'voir-un-role',
         *     'creer-un-role',
         *     'modifier-un-role',
         *     'voir-un-formulaire',
         *     'creer-un-formulaire',
         *     'modifier-un-formulaire',
         *     'voir-une-entreprise-executante',
         *     'modifier-une-entreprise-executante',
         *     'creer-une-entreprise-executante',
         *     'creer-un-suivi-environnementale',
         *     'modifier-un-suivi-environnementale',
         *     'voir-un-suivi-environnementale',
         *     'supprimer-un-suivi-environnementale',
         *     'voir-un-projet',
         *     'voir-une-composante',
         *     'voir-une-activite',
         *     'voir-une-tache',
         *     'voir-une-activite-environnementale',
         *     'voir-un-site',
         *     'voir-une-checklist',
         *     'voir-une-passation',
         *     'voir-une-revision',
         *     'voir-une-categorie',
         *     'voir-un-suivi-environnementale',
         *     'voir-une-permission',
         *     'creer-un-rappel',
         *     'voir-un-fichier',
         *     'voir-ptab',
         *     'voir-formulaire-entreprise-executant',
         *     'creer-un-fichier',
         *     'voir-details-projet',
         *     'exporter-un-suivi-indicateur',
         *     'exporter-un-suivi-ppm',
         *     'exporter-un-suivi-ppm-revise',
         *     'exporter-un-suivi-ptab-revise'
         * ];
         *
         * $ongs = [
         *     'voir-un-utilisateur',
         *     'creer-un-utilisateur',
         *     'modifier-un-utilisateur',
         *     'voir-un-role',
         *     'creer-un-role',
         *     'modifier-un-role',
         *     'voir-un-formulaire',
         *     'creer-un-formulaire',
         *     'modifier-un-formulaire',
         *     'voir-une-ong',
         *     'modifier-une-ong',
         *     'creer-une-ong',
         *     'voir-un-projet',
         *     'voir-une-composante',
         *     'voir-une-activite',
         *     'voir-une-tache',
         *     'voir-une-activite-environnementale',
         *     'voir-une-revision',
         *     'voir-une-categorie',
         *     'voir-un-suivi-environnementale',
         *     'voir-une-permission',
         *     'creer-un-rappel',
         *     'voir-un-fichier',
         *     'creer-un-fichier',
         *     'voir-details-projet',
         *     'exporter-un-suivi-indicateur'
         * ];
         */

        /** Creation des permissions */
        dump('Creation des permissions.....');

        file_put_contents($dossier . 'permissions.txt', '');

        foreach ($actions as $action) {
            foreach ($modules as $module) {
                $nom = $action . ' ' . $module;

                $slug = str_replace(' ', '-', strtolower($nom));

                $permissions = Permission::where('slug', $slug)->get();

                if ($module === 'un projet') {
                    dump(" {$action} - {$module}" . json_encode($permissions));
                }

                if (!count($permissions)) {
                    Permission::create([
                        'nom' => $nom,
                        'slug' => $slug,
                    ]);
                } else {
                    if (count($permissions) > 1) {
                        for ($i = 1; $i < count($permissions); $i++) {
                            Permission::destroy($permissions[$i]['id']);
                        }
                    }
                }

                file_put_contents($dossier . 'permissions.txt', $slug . "\n", FILE_APPEND);
            }
        }

        foreach ($autres as $autre) {
            $permissions = Permission::where('slug', str_replace(' ', '-', $autre))->get();

            if (!count($permissions)) {
                Permission::create([
                    'nom' => $autre,
                    'slug' => str_replace(' ', '-', $autre),
                ]);
            } else {
                if (count($permissions) > 1) {
                    for ($i = 1; $i < count($permissions); $i++) {
                        dump($permissions[$i]['id']);
                        Permission::destroy($permissions[$i]['id']);
                    }
                }
            }

            file_put_contents($dossier . 'permissions.txt', $autre . "\n", FILE_APPEND);
        }

        dump('Fin creation des permissions');

        /** Creation des roles hardcodé */
        dump('Creation des roles.....' . count($roles));

        foreach ($roles as $key => $indice) {
            $roles = Role::where('slug', $roles_slugs[$key])->get();

            file_put_contents($dossier . $roles_slugs[$key] . '.txt', '');

            dump($dossier . $roles_slugs[$key] . '.txt');

            if (!count($roles)) {
                $role = Role::create([
                    'nom' => $indice,
                    'slug' => $roles_slugs[$key],
                    'description' => $indice
                ]);
            } else {
                if (count($roles) > 1) {
                    for ($i = 1; $i < count($roles); $i++) {
                        Role::destroy($roles[$i]['id']);
                    }
                }
            }

            $role = Role::where('slug', $roles_slugs[$key])->first();

            /* if($role->slug == 'bailleur')
            {
                $ids = $role->permissions->pluck('id');
                $role->permissions()->detach($ids);

                foreach($bailleurs as $bailleur)
                {

                    //if($role->permissions->where('slug', $bailleur)->first() == null)
                    {
                        $permission = Permission::where('slug', $bailleur)->first();

                        $role->permissions()->attach($permission->id);
                    }
                }
            }

            else if($role->slug == 'mission-de-controle')
            {
                $ids = $role->permissions->pluck('id');
                $role->permissions()->detach($ids);

                foreach($missionDeControles as $mission)
                {
                    //if(!$role->permissions->where('slug', $mission)->first())
                    {
                        $permission = Permission::where('slug', $mission)->first();

                        $role->permissions()->attach($permission->id);
                    }
                }
            }*/

            // A ne pas decommenter
            /* else if($role->slug == 'organisation')
            {
                $ids = $role->permissions->pluck('id');
                $role->permissions()->detach($ids);

                foreach($missionDeControles as $mission)
                {
                    //if(!$role->permissions->where('slug', $mission)->first())
                    {
                        $permission = Permission::where('slug', $mission)->first();

                        $role->permissions()->attach($permission->id);
                    }
                }
            } */

            /*
             * else if($role->slug == 'mod')
             * {
             *     $ids = $role->permissions->pluck('id');
             *     $role->permissions()->detach($ids);
             *
             *     foreach($mods as $mod)
             *     {
             *         //if(!$role->permissions->where('slug', $mod)->first())
             *         {
             *             $permission = Permission::where('slug', $mod)->first();
             *
             *             $role->permissions()->attach($permission->id);
             *         }
             *     }
             * }
             *
             * else if($role->slug == 'gouvernement')
             * {
             *     $ids = $role->permissions->pluck('id');
             *     $role->permissions()->detach($ids);
             *
             *     foreach($gouvernements as $gouvernement)
             *     {
             *         //if(!$role->permissions->where('slug', $gouvernement)->first())
             *         {
             *             $permission = Permission::where('slug', $gouvernement)->first();
             *
             *             $role->permissions()->attach($permission->id);
             *         }
             *     }
             * }
             *
             * else if($role->slug == 'ong')
             * {
             *     $ids = $role->permissions->pluck('id');
             *     $role->permissions()->detach($ids);
             *
             *     foreach($ongs as $ong)
             *     {
             *         //if(!$role->permissions->where('slug', $ong)->first())
             *         {
             *             $permission = Permission::where('slug', $ong)->first();
             *
             *             $role->permissions()->attach($permission->id);
             *         }
             *     }
             * }
             *
             * else if($role->slug == 'institut')
             * {
             *     $ids = $role->permissions->pluck('id');
             *     $role->permissions()->detach($ids);
             *
             *     foreach($instituts as $institut)
             *     {
             *         //if(!$role->permissions->where('slug', $institut)->first())
             *         {
             *             $permission = Permission::where('slug', $institut)->first();
             *
             *             $role->permissions()->attach($permission->id);
             *         }
             *     }
             * }
             *
             * else if($role->slug == 'entreprise-executant')
             * {
             *     $ids = $role->permissions->pluck('id');
             *     $role->permissions()->detach($ids);
             *
             *     foreach($entreprises as $entreprise)
             *     {
             *         //if(!$role->permissions->where('slug', $entreprise)->first())
             *         {
             *             $permission = Permission::where('slug', $entreprise)->first();
             *
             *             $role->permissions()->attach($permission->id);
             *         }
             *     }
             * }
             * else if($role->slug == 'unitee-de-gestion')
             * {
             *     $ids = $role->permissions->pluck('id');
             *     $role->permissions()->detach($ids);
             *
             *     $permissions = Permission::all();
             *
             *     $controle = 1;
             *
             *     foreach($permissions as $permission)
             *     {
             *         foreach($uniteeDeGestion as $unitee)
             *         {
             *
             *             if($unitee == $permission->slug)
             *             {
             *                 $controle = 0;
             *                 break;
             *             }
             *         }
             *         if($controle)
             *         {
             *             //if(!$role->permissions->where('slug', $permission->slug)->first())
             *             {
             *                     $role->permissions()->attach($permission->id);
             *
             *             }
             *         }
             *
             *         $controle = 1;
             *
             *     }
             * }
             */

            if ($role->slug == 'ddc') {
                $ids = $role->permissions->pluck('id');
                $role->permissions()->detach($ids);

                foreach ($ddcs as $ddc) {
                    // if(!$role->permissions->where('slug', $mod)->first())
                    {
                        $permission = Permission::where('slug', $ddc)->first();

                        if ($permission) {
                            $role->permissions()->attach($permission->id);
                        }
                    }
                }
            } else if ($role->slug == 'unitee-de-gestion') {
                $ids = $role->permissions->pluck('id');
                $role->permissions()->detach($ids);

                foreach ($uniteeDeGestion as $unitee_de_gestion) {
                    // if(!$role->permissions->where('slug', $administrateur)->first())
                    {
                        $permission = Permission::where('slug', $unitee_de_gestion)->first();

                        if ($permission) {
                            // dump($permission);
                            $role->permissions()->attach($permission->id);
                        } else {
                            dd($unitee_de_gestion);
                        }
                    }
                }
            } else if ($role->slug == 'administrateur') {
                $ids = $role->permissions->pluck('id');
                $role->permissions()->detach($ids);

                foreach ($administrateurs as $administrateur) {
                    // if(!$role->permissions->where('slug', $administrateur)->first())
                    {
                        $permission = Permission::where('slug', $administrateur)->first();

                        $role->permissions()->attach($permission->id);
                    }
                }
            } else if ($role->slug == 'organisation') {
                $ids = $role->permissions->pluck('id');
                $role->permissions()->detach($ids);

                foreach ($organisations as $organisation) {
                    // if(!$role->permissions->where('slug', $organisation)->first())
                    {
                        $permission = Permission::where('slug', $organisation)->first();

                        if ($permission) {
                            $role->permissions()->attach($permission->id);
                        }
                    }
                }
            }

            foreach ($role->permissions as $permission) {
                file_put_contents($dossier . $role->slug . '.txt', $permission->slug . "\n", FILE_APPEND);
            }

            dump($indice);
        }

        dump('Consulter le dossier help/permissions');
    }
}
