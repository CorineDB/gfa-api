
<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance as EvaluationGouvernance;
use App\Repositories\enquetes_de_gouvernance\FicheDeSyntheseRepository as FichesDeSyntheseRepository;
use App\Models\ProfileDeGouvernance;
use App\Models\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernance;
use App\Models\enquetes_de_gouvernance\FormulaireFactuelDeGouvernance;
use App\Http\Resources\gouvernance\FicheDeSyntheseEvaluationFactuelleResource;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => ['cors', 'json.response'], 'as' => 'api.'/* , 'namespace' => 'App\Http\Controllers' */], function () {

    Route::get('test-email/{email}', function ($email) {
        try {
            Log::info("Test email sent to: {$email}");/*

            Mail::to($email)->send(new ConfirmationDeCompteEmail([
                'view' => "emails.auth.confirmation_de_compte",
                'subject' => "Confirmation de compte",
                'content' => [
                    "greeting" => "Bienvenu Mr/Mme " . $this->user->nom,
                    "introduction" => "Voici votre lien d'activation de votre compte",
                    "lien" => "https://example.com/activation"
                ]
            ])); */

            Mail::raw("Bonjour TESTER, voici votre lien d'activation : https://example.com/activation", function ($message) use ($email) {
                $message->to($email)
                    ->subject('Activation de votre compte');
            });

            Log::info("Test email sent to: {$email}");
            return response()->json(['message' => 'Email sent successfully to ' . $email]);
        } catch (\Exception $e) {
            Log::error("Email sending failed: $email" . $e->getMessage());
            return response()->json(['error' => 'Failed to send email.'], 500);
        }
    });


    Route::get('confirmation-de-compte/{email}', [AuthController::class, 'confirmationDeCompte'])->name('confirmationDeCompte');

    Route::get('activation-de-compte/{token}', [AuthController::class, 'activationDeCompte'])->name('activationDeCompte');

    Route::post('suiviKobo', 'SuiviIndicateurController@suiviKobo');


    Route::get('reinitialisation-de-mot-de-passe/{email}', [AuthController::class, 'verificationEmailReinitialisationMotDePasse'])->name('verificationEmailReinitialisationMotDePasse');

    Route::get('verification-de-compte/{token}', [AuthController::class, 'verificationDeCompte'])->name('verificationDeCompte');

    Route::post('reinitialisation-de-mot-de-passe', [AuthController::class, 'reinitialisationDeMotDePasse'])->name('reinitialisationDeMotDePasse');

    Route::post('authentification', [AuthController::class, 'authentification'])->name('auth.authentification'); // Route d'authentification

    Route::post('organisation-authentification', [AuthController::class, 'organisationAuthentification'])->name('auth.org-authentification'); // Route d'authentification
    Route::post('admin-authentification', [AuthController::class, 'adminAuthentification'])->name('auth.admin-authentification'); // Route d'authentification

    Route::group(['middleware' => [/*'deconnexion', */'auth:sanctum'/*, 'cookie'*/]], function () {

        Route::group(['prefix' => 'authentification', 'as' => 'auth.'], function () {

            Route::controller('AuthController')->group(function () {

                Route::post('/deconnexion', 'deconnexion')->name('deconnexion'); // Route de déconnexion

                Route::get('/utilisateur-connecte', 'utilisateurConnecte')->name('utilisateurConnecte');

                Route::get('/refresh-token', 'refresh_token')->name('refreshToken');

                Route::post('reinitialisation-de-mot-de-passe', 'modificationDeMotDePasse')->name('modificationDeMotDePasse');

                Route::get('/{id}/debloquer', 'debloquer')->name('debloquer');
            });
        });

        Route::group(['prefix' =>  'utilisateurs', 'as' => 'utilisateurs.'], function () {

            Route::controller('UserController')->group(function () {

                Route::post('/creation-de-compte-bailleur', 'creationDeCompteBailleur')->name('creationDeCompteBailleur')->middleware('permission:creer-un-bailleur');
                Route::put('/mis-à-jour-de-compte-bailleur/{id}', 'miseAJourDeCompteBailleur')->name('miseAJourDeCompteBailleur')->middleware('permission:modifier-un-bailleur');
                Route::get('/bailleurs', 'bailleurs')->name('bailleurs')->middleware('permission:voir-un-bailleur');
                Route::post('/logo', 'createLogo')->name('createLogo')->middleware('permission:modifier-un-bailleur');
                Route::post('/photo', 'createPhoto')->name('createPhoto')->middleware('permission:modifier-un-utilisateur');
                Route::get('/getNotifications', 'getNotifications')->name('getNotifications');
                Route::put('/readNotifications', 'readNotifications')->name('readNotifications');
                Route::get('/deleteNotifications/{id}', 'deleteNotifications')->name('deleteNotifications');
                Route::get('/deleteAllNotifications', 'deleteAllNotifications')->name('deleteAllNotifications');
                Route::get('/fichiers', 'fichiers')->name('fichiers')->middleware('permission:voir-un-fichier');
                Route::put('/{id}', 'update')->name('update');
            });
        });/* s */

        Route::group(['prefix' =>  'utilisateur', 'as' => 'utilisateur.'], function () {

            Route::controller('UserController')->group(function () {
                Route::get('/getNotifications', 'getNotifications')->name('getNotifications');
                Route::post('/readNotifications', 'readNotifications')->name('readNotifications');
            });
        });

        Route::apiResource('programmes', 'ProgrammeController')->names('programmes');

        Route::group(['prefix' =>  'programmes', 'as' => 'programmes.'], function () {

            Route::controller('ProgrammeController')->group(function () {

                Route::get('{id}/projets', 'projets')->name('projets')->middleware('permission:voir-un-projet');

                Route::get('{id}/bailleurs', 'bailleurs')->name('bailleurs')->middleware('permission:voir-un-bailleur');

                Route::get('{id}/mods', 'mods')->name('mods')->middleware('permission:voir-un-mod');

                Route::get('{id}/mods/passations', 'modPassations')->name('modPassations')->middleware('permission:voir-une-passation');

                Route::get('{id}/missionDeControles/passations', 'missionDeControlePassations')->name('missionDeControlePassations')->middleware('permission:voir-une-passation');

                Route::get('{id}/entreprise-executants', 'entreprisesExecutante')->name('entreprise-executants')->middleware('permission:voir-une-entreprise-executante');

                Route::get('{id}/structures', 'structures')->name('structures')->middleware('permission:voir-un-utilisateur');

                Route::get('{id}/users', 'users')->name('users')->middleware('permission:voir-un-utilisateur');

                Route::get('entrepriseUsers/{id}', 'entrepriseUsers')->name('entrepriseUsers')->middleware('permission:voir-un-utilisateur');

                Route::get('/composantes/{id}', 'composantes')->name('composantes')->middleware('permission:voir-une-composante');

                Route::get('/sousComposantes/{id}', 'sousComposantes')->name('sousComposantes')->middleware('permission:voir-une-composante');

                Route::get('{id}/activites', 'activites')->name('activites')->middleware('permission:voir-une-activite');

                Route::get('{id}/eActivites', 'eActivites')->name('eActivites')->middleware('permission:voir-une-activite-environnementale');

                Route::get('{id}/maitriseOeuvres', 'maitriseOeuvres')->name('maitriseOeuvres')->middleware('permission:voir-une-maitrise-oeuvre');

                Route::get('taches/{id}', 'taches')->name('taches')->middleware('permission:voir-une-tache');

                Route::get('{id}/decaissements', 'decaissements')->name('decaissements')->middleware('permission:voir-un-decaissement');

                Route::get('{id}/sinistres', 'sinistres')->name('sinistres')->middleware('permission:voir-un-pap');

                Route::get('{id}/sites', 'sites')->name('sites')->middleware('permission:voir-un-site');

                Route::get('{id}/scopes', 'scopes')->name('scopes')->middleware('permission:voir-une-revision');

                Route::get('{id}/suiviFinanciers', 'suiviFinanciers')->name('suiviFinanciers')->middleware('permission:voir-un-suivi-financier');
            });
        });

        Route::get('programme/kobo', 'ProgrammeController@kobo')->middleware('permission:voir-formulaire-kobo');

        Route::put('programme/kobo', 'ProgrammeController@koboUpdate')->middleware('permission:voir-formulaire-kobo');

        Route::post('programme/kobo', 'ProgrammeController@koboSuivie')->middleware('permission:voir-suivi-kobo');

        Route::post('programme/koboPreview', 'ProgrammeController@koboPreview')->middleware('permission:voir-suivi-kobo');

        Route::post('programme/rapport', 'ProgrammeController@rapport');

        Route::get('programme/rapports', 'ProgrammeController@rapports');

        Route::put('programme/rapport/{id}', 'ProgrammeController@updateRapport');

        Route::delete('programme/rapport/{id}', 'ProgrammeController@deleteRapport');

        Route::get('programme/rapports', 'ProgrammeController@rapports');

        Route::get('programme/emailRapports', 'ProgrammeController@emailRapports');

        Route::post('programme/rapport/sendMail', 'ProgrammeController@rapportSendMail');

        Route::get('programme/dashboard', 'ProgrammeController@dashboard')->name('dashboard')->middleware('permission:voir-un-projet');

        Route::apiResource('decaissements', 'DecaissementController')->names('decaissements');

        Route::group(['prefix' =>  'decaissements', 'as' => 'decaissements.'], function () {

            Route::controller('DecaissementController')->group(function () {

                Route::post('filtres', 'filtre')->name('filtres')->middleware('permission:voir-un-decaissement');
            });
        });


        Route::apiResource('utilisateurs', 'UserController')->names('utilisateurs');

        Route::group(['prefix' =>  'utilisateurs', 'as' => 'utilisateurs.'], function () {
            Route::controller('UserController')->group(function () {
                Route::get('/{id}/permissions', 'permissions')->name('permissions');
            });
        });

        Route::apiResource('activites', 'ActiviteController')->names('activites');

        Route::group(['prefix' =>  'activites', 'as' => 'activites.'], function () {

            Route::controller('ActiviteController')->group(function () {

                //Route::post('{activite}/prolonger', 'prolonger')->name('prolonger')->middleware('permission:prolonger-une-activite');

                Route::post('/{id}/changeStatut', 'changeStatut')->name('changeStatut')/*->middleware('permission:voir-un-suivi')*/;

                Route::get('/{id}/taches', 'taches')->name('taches')->middleware('permission:voir-une-tache');

                Route::get('/{id}/suivis', 'suivis')->name('suivis')->middleware('permission:voir-un-suivi');

                Route::post('{id}/ajouterDuree', 'ajouterDuree')->name('ajouterDuree')->middleware('permission:modifier-une-activite');

                Route::post('/ppm', 'ppm')->name('ppm')->middleware('permission:voir-une-activite');

                Route::post('/modifierDuree/{dureeId}', 'modifierDuree')->name('modifierDuree')->middleware('permission:modifier-une-activite');

                Route::post('{id}/deplacer', 'deplacer')->name('deplacer')->middleware('permission:modifier-une-activite');

                Route::post('/filtre', 'filtre')->name('filtre');

                Route::get('{id}/plansDeDecaissement', 'plansDeDecaissement')->name('plansDeDecaissement')->middleware('permission:voir-un-plan-de-decaissement');
            });
        });

        Route::group(['prefix' =>  'eActivites', 'as' => 'eActivites.'], function () {

            Route::controller('EActiviteController')->group(function () {

                Route::get('/checkLists/{id}', 'checkLists')->name('checkLists');

                Route::post('/ajouterDuree/{id}', 'ajouterDuree')->name('ajouterDuree');

                Route::post('/modifierDuree/{dureeId}', 'modifierDuree')->name('modifierDuree');
            });
        });

        Route::apiResource('gouvernements', 'GouvernementController')->names('gouvernements');


        Route::apiResource('bailleurs', 'BailleurController', ['except' => ['index', 'update']])->names('bailleurs')->middleware(['role:super-admin|administrateur|unitee-de-gestion']);

        Route::apiResource('bailleurs', 'BailleurController', ['only' => ['index']])->names('bailleurs');

        Route::group(['prefix' =>  'bailleur', 'middleware' =>  ['role:bailleur'], 'as' => 'bailleur.'], function () {

            Route::controller('BailleurController')->group(function () {

                Route::get('/anos', 'anos')->name('anos');

                Route::post('{id}/update', 'update')->name('update');

                Route::get('/entreprisesExecutantes', 'entreprisesExecutant')->name('entreprisesExecutantes');

                Route::get('/indicateurs', 'indicateurs')->name('indicateurs');

                Route::get('/suiviIndicateurs', 'suiviIndicateurs')->name('suiviIndicateurs');
            });
        });

        Route::group(['prefix' =>  'bailleur', 'as' => 'bailleur.'], function () {

            Route::controller('BailleurController')->group(function () {

                Route::post('/{id}/update', 'update')->name('update');
            });
        });


        Route::apiResource('unitee-de-gestions', 'UniteeDeGestionController')->names('unitee-de-gestions')->middleware(['role:super-admin|administrateur|unitee-de-gestion']);

        Route::group(['prefix' =>  'unitee-de-gestions', 'middleware' =>  ['role:unitee-de-gestion'], 'as' => 'unitee-de-gestions.'], function () {

            Route::apiResource('{id}/membres', 'MembreUniteeGestionController')->names('membres');
        });

        Route::apiResource('mission-de-controles', 'MissionDeControleController', ['except' => ['index']])->names('mission-de-controles')->middleware(['role:unitee-de-gestion|administrateur|mod']);

        Route::apiResource('mission-de-controles', 'MissionDeControleController', ['only' => ['index']])->names('mission-de-controles')->middleware(['role:unitee-de-gestion|administrateur|bailleur|mod']);

        Route::group(['prefix' =>  'mission-de-controles', 'middleware' =>  ['role:mission-de-controle|unitee-de-gestion'], 'as' => 'mission-de-controles.'], function () {

            Route::apiResource('{id}/membres', 'MembreMissionDeControleController')->names('membres');
        });


        Route::apiResource('institutions', 'InstitutionController', ['except' => ['index']])->names('institutions')->middleware(['role:unitee-de-gestion']);

        Route::apiResource('institutions', 'InstitutionController', ['only' => ['index']])->names('institutions');


        Route::apiResource('ongs', 'ONGController', ['except' => ['index']])->names('ongs')->middleware(['role:unitee-de-gestion']);

        Route::apiResource('ongs', 'ONGController', ['only' => ['index']])->names('ongs');


        Route::apiResource('unitees-de-mesure', 'UniteeMesureController', ['except' => ['index']])->names('unitees_de_mesure')->middleware(['role:unitee-de-gestion']);

        Route::apiResource('unitees-de-mesure', 'UniteeMesureController', ['only' => ['index']])->names('unitees_de_mesure');


        Route::apiResource('agences-de-communication', 'AgenceController', ['except' => ['index']])->names('agences-de-communication')->middleware(['role:unitee-de-gestion']);

        Route::apiResource('agences-de-communication', 'AgenceController', ['only' => ['index']])->names('agences-de-communication');


        Route::apiResource('entreprise-executants', 'EntrepriseExecutantController', ['except' => ['index']])->names('entreprise-executants')->middleware(['role:unitee-de-gestion|mod']);

        Route::apiResource('entreprise-executants', 'EntrepriseExecutantController', ['only' => ['index']])->names('entreprise-executants');

        Route::group(['prefix' =>  'entreprise-executants', 'as' => 'entreprise-executants.'], function () {

            Route::controller('EntrepriseExecutantController')->group(function () {

                Route::get('{id}/eActivites', 'eActivites')->name('eActivites')->middleware('permission:voir-une-activite-environnementale');
            });
        });



        Route::apiResource('mods', 'ModController', ['except' => ['index']])->names('mods')->middleware(['role:unitee-de-gestion']);
        Route::apiResource('mods', 'ModController', ['only' => ['index']])->names('mods')->middleware(['role:unitee-de-gestion|bailleur']);
        Route::apiResource('agences-de-communication', 'AgenceController')->names('agences-de-communication')->middleware(['role:unitee-de-gestion']);

        Route::apiResource('entreprise-executants', 'EntrepriseExecutantController')->names('entreprise-executants')->middleware(['role:unitee-de-gestion|mod']);


        Route::apiResource('categories', 'CategorieController', ['except' => ['index']])->names('categories')->middleware(['role:unitee-de-gestion']);

        Route::apiResource('categories', 'CategorieController', ['only' => ['index']])->names('categories');


        Route::apiResource('commentaires', 'CommentaireController')->names('commentaires');

        Route::apiResource('composantes', 'ComposanteController')->names('composantes');

        Route::group(['prefix' =>  'composantes', 'as' => 'composantes.'], function () {

            Route::controller('ComposanteController')->group(function () {

                Route::get('{id?}/sousComposantes', 'sousComposantes')->name('sousComposantes')->middleware('permission:voir-une-composante');

                Route::get('{id}/activites', 'activites')->name('activites')->middleware('permission:voir-une-activite');

                //Route::get('{id}/filtreActivites', 'filtreActivites')->name('filtreActivites');

                Route::get('/{id}/suivis', 'suivis')->name('suivis')->middleware('permission:voir-un-suivi');

                Route::post('{id}/deplacer', 'deplacer')->name('deplacer')->middleware('permission:modifier-une-composante');
            });
        });

        // Route::apiResource('collecteurs-bassins', 'SiteController')->names('collecteurs_bassins');

        Route::apiResource('fichiers', 'FichierController')->names('fichiers');

        Route::apiResource('reponseAnos', 'ReponseAnoController')->names('reponse-anos');

        Route::apiResource('historiques', 'HistoriqueController', ['only' => ['index']])->names('historiques');

        Route::apiResource('indicateurs', 'IndicateurController', ['except' => ['index']])->names('indicateurs');

        Route::group(['prefix' =>  'indicateurs', 'as' => 'indicateurs.'], function () {

            Route::controller('IndicateurController')->group(function () {

                Route::get('{id}/checkSuivi/{year}', 'checkSuivi')->name('checkSuivi')->middleware('permission:voir-un-suivi-indicateur');

                Route::get('{id}/suivis', 'suivis')->name('suivis')->middleware('permission:modifier-un-suivi-indicateur');

                Route::post('filtres', 'filtre')->name('filtre')->middleware('permission:modifier-un-suivi-indicateur');
            });
        });

        Route::group(['prefix' =>  'indicateurMods', 'as' => 'indicateurMods.'], function () {

            Route::controller('IndicateurModController')->group(function () {

                Route::get('{id}/checkSuivi/{year}', 'checkSuivi')->name('checkSuivi')->middleware('permission:voir-un-suivi-indicateur-mod');

                Route::get('{id}/suivis', 'suivis')->name('suivis')->middleware('permission:modifier-un-suivi-indicateur-mod');

                Route::post('filtres', 'filtre')->name('filtre')->middleware('permission:modifier-un-suivi-indicateur-mod');
            });
        });

        Route::apiResource('indicateurs', 'IndicateurController', ['only' => ['index']])->names('indicateurs');

        Route::apiResource('indicateurs_cadre_logique', 'IndicateurCadreLogiqueController', ['except' => ['index']])->names('indicateurs_cadre_logique')->middleware(['role:unitee-de-gestion']);

        Route::apiResource('indicateurs_cadre_logique', 'IndicateurCadreLogiqueController', ['only' => ['index']])->names('indicateurs_cadre_logique');



        //Route::apiResource('indicateur-mods', 'IndicateurModController', ['except' => ['index']])->names('indicateur-mods')->middleware(['role:unitee-de-gestion|mod']);

        //Route::apiResource('indicateur-mods', 'IndicateurModController', ['only' => ['index']])->names('indicateur-mods');

        Route::apiResource('indicateur-mods', 'IndicateurModController')->names('indicateur-mods');


        Route::apiResource('sites', 'SiteController', ['except' => ['index']])->names('sites')->middleware(['role:unitee-de-gestion']);

        Route::apiResource('sites', 'SiteController', ['only' => ['index']])->names('sites');

        Route::apiResource('checkLists', 'CheckListController')->names('checkLists');

        Route::apiResource('checks-list-ongs-agences', 'CheckListComController')->names('checks-list-ongs-agences')->middleware(['role:ong|agence']);

        Route::apiResource('suivi-checks-list-ongs-agences', 'SuiviCheckListComController')->names('suivi-checks-list-ongs-agences')->middleware(['role:ong|agence']);

        Route::apiResource('eSuivis', 'ESuiviController')->names('eSuivis');

        Route::group(['prefix' =>  'eSuivis', 'as' => 'eSuivis.'], function () {

            Route::controller('ESuiviController')->group(function () {

                Route::post('dates', 'dates')->name('dates')->middleware('permission:voir-un-suivi-environnementale');

                Route::post('formulaires', 'formulaires')->name('formulaires')->middleware('permission:voir-un-formulaire');

                Route::post('graphes', 'graphes')->name('graphes')->middleware('permission:voir-un-formulaire');
            });
        });


        Route::apiResource('passations', 'PassationController')->names('passations');

        Route::apiResource('suivi-financier-mods', 'SuiviFinancierMODController', ['except' => ['index']])->names('suivi-financier-mods')->middleware(['role:unitee-de-gestion|mod']);

        Route::apiResource('suivi-financier-mods', 'SuiviFinancierMODController', ['only' => ['index']])->names('suivi-financier-mods');

        Route::apiResource('maitrise-oeuvres', 'MaitriseOeuvreController')->names('maitrise-oeuvres');

        Route::apiResource('permissions', 'PermissionController', ['only' => ['index']])->names('permissions')->middleware(['permission:voir-une-permission']);

        Route::apiResource('planDecaissements', 'PlanDecaissementController')->names('plan-decaissements');

        Route::apiResource('projets', 'ProjetController', ['except' => ['update']])->names('projets');

        Route::group(['prefix' =>  'projets', 'as' => 'projets.'], function () {

            Route::controller('ProjetController')->group(function () {

                Route::post('{id}/update', 'update')->name('update');

                Route::post('{projet}/prolonger', 'prolonger')->name('prolonger')->middleware('permission:prolonger-un-projet');

                Route::get('{id}/composantes', 'composantes')->name('composantes')->middleware('permission:voir-une-composante');

                Route::get('{id}/decaissements', 'decaissements')->name('decaissements')->middleware('permission:voir-un-decaissement');

                Route::post('{id}/tef', 'tef')->name('tef');

                Route::get('{id}/statistiques', 'statistiques')->name('statistiques')->middleware('permission:voir-un-projet');

                Route::get('{id}/cadreLogique', 'cadreLogique')->name('cadreLogique')->middleware('permission:voir-un-projet');
            });
        });

        Route::apiResource('ptabScopes', 'PtabRevisionController', ['only' => ['index']])->names('ptab-scopes');

        Route::group(['prefix' =>  'ptas', 'as' => 'ptas.'], function () {
            Route::controller('PtaController')->group(function () {

                Route::post('/generer', 'generer')->name('generer')->middleware('permission:voir-ptab');

                Route::post('/filtre', 'filtre')->name('filtre')->middleware('permission:voir-ptab');
            });

            Route::controller('PtabRevisionController')->group(function () {

                Route::post('/getOldPtaReviser', 'getOldPtaReviser')->name('getOldPtaReviser')->middleware('permission:voir-revision-ptab');

                Route::post('/getPtabReviser', 'getPtabReviser')->name('getPtabReviser')->middleware('permission:voir-revision-ptab');

                Route::post('/reviserPtab', 'reviserPtab')->name('reviserPtab')->middleware('permission:faire-revision-ptab');

                Route::get('/listVersionPtab', 'getListVersionPtab')->name('getListVersionPtab')->middleware('permission:voir-revision-ptab');
            });
        });

        Route::apiResource('eActivites', 'EActiviteController')->names('eActivites');

        Route::apiResource('eActiviteMods', 'EActiviteModController')->names('eActiviteMods');

        Route::apiResource('eSuiviActiviteMods', 'ESuiviActiviteModController')->names('eSuiviActiviteMods');

        Route::apiResource('roles', 'RoleController')->names('roles');

        Route::apiResource('suivis', 'SuiviController')->names('suivis');

        Route::group(['prefix' =>  'suivis', 'as' => 'suivis.'], function () {
            Route::controller('SuiviController')->group(function () {

                Route::post('filterByModule', 'getSuivis')->name('getSuivis')->middleware('permission:voir-un-suivi');

                Route::post('suivisV2', 'suivisV2')->name('suivisV2')->middleware('permission:voir-un-suivi');
            });
        });

        Route::post('suivisV2', 'SuiviController@suivisV2');

        Route::apiResource('suiviFinanciers', 'SuiviFinancierController')->names('suivi-financiers');

        Route::group(['prefix' =>  'suiviFinanciers', 'as' => 'suiviFinanciers'], function () {
            Route::controller('SuiviFinancierController')->group(function () {

                Route::post('importation', 'importation')->name('importation')->middleware('permission:creer-un-suivi-financier');

                Route::post('filtres', 'filtre')->name('filtres')->middleware('permission:voir-un-suivi-financier');
                Route::post('trismestreASsuivre', 'trismestreASsuivre')->name('trismestreASsuivre')->middleware('permission:voir-un-suivi-financier');
            });
        });

        Route::apiResource('suivisIndicateurs', 'SuiviIndicateurController')->names('suivi-indicateurs');

        Route::apiResource('suivi-indicateurs', 'SuiviIndicateurController')->names('suivi-indicateurs');

        Route::apiResource('audits', 'AuditController')->names('audits');

        Route::controller('AuditController')->group(function () {

            Route::post('audits/{id}', 'update')->name('update');
        });

        Route::controller('SuiviIndicateurController')->group(function () {

            Route::post('suivi-indicateurs/filter', 'filtre')->name('filtre');

            Route::post('suivi-indicateurs/dateSuivie', 'dateSuivie')->name('dateSuivie');
        });

        Route::apiResource('suivi-indicateurs-mods', 'SuiviIndicateurMODController')->names('suivi-indicateurs-mods');

        Route::controller('SuiviIndicateurMODController')->group(function () {

            Route::post('suivi-indicateurs-mods/filter', 'filtre')->name('filtre');

            Route::post('suivi-indicateurs-mods/dateSuivie', 'dateSuivie')->name('dateSuivie');
        });

        Route::apiResource('taches', 'TacheController')->parameters([
            'tach' => 'tache'
        ])->names('taches');

        Route::group(['prefix' =>  'taches', 'as' => 'taches.'], function () {
            Route::controller('TacheController')->group(function () {

                Route::post('{tache}/prolonger', 'prolonger')->name('prolonger')->middleware('permission:prolonger-une-tache');

                Route::get('/{id}/suivis', 'suivis')->name('suivis')->middleware('permission:voir-un-suivi');

                Route::get('/{id}/changeStatut', 'changeStatut')->name('changeStatut')/*->middleware('permission:voir-un-suivi')*/;

                Route::post('suivisV2', 'suivisV2')->name('suivisV2')->middleware('permission:voir-un-suivi');

                Route::post('{id}/ajouterDuree', 'ajouterDuree')->name('ajouterDuree')->middleware('permission:modifier-une-tache');

                Route::post('/modifierDuree/{dureeId}', 'modifierDuree')->name('modifierDuree')->middleware('permission:modifier-une-tahe');

                Route::post('/deplacer/{id}', 'deplacer')->name('deplacer')->middleware('permission:modifier-une-tache');
            });
        });

        Route::apiResource('anos', 'AnoController')->names('anos');

        Route::group(['prefix' =>  'ano', 'as' => 'ano.'], function () {

            Route::controller('AnoController')->group(function () {

                Route::get('rappel', 'rappel')->name('rappel')->middleware('permission:voir-un-ano');

                Route::get('{id}/reponses', 'reponses')->name('reponses')->middleware('permission:voir-un-ano');
            });
        });

        Route::apiResource('formulaires', 'FormulaireController')->names('formulaires');

        Route::group(['prefix' =>  'formulaire', 'as' => 'formulaires.'], function () {

            Route::controller('FormulaireController')->group(function () {

                Route::post('getSuivi', 'getSuivi')->name('getSuivi')->middleware('permission:voir-un-suivi-environnementale');

                Route::post('getSuiviGeneral', 'getSuiviGeneral')->name('getSuiviGeneral');

                Route::get('generals', 'allGeneral')->name('generals')->middleware('permission:voir-un-formulaire');
            });
        });

        Route::apiResource('sinistres', 'SinistreController')->names('sinistres');

        Route::group(['prefix' =>  'sinistres', 'as' => 'sinistres'], function () {
            Route::controller('SinistreController')->group(function () {

                Route::post('importation', 'importation')->name('importation')->middleware('permission:creer-un-pap');
            });
        });

        Route::apiResource('proprietes', 'ProprieteController')->names('proprietes');

        Route::apiResource('nouvelleProprietes', 'NouvelleProprieteController')->names('nouvelleProprietes');

        Route::apiResource('payes', 'PayeController')->names('payes');

        Route::apiResource('typeAnos', 'TypeAnoController')->names('typeAnos');

        Route::group(['prefix' =>  'tables', 'as' => 'tables.'], function () {
            Route::controller('TableController')->group(function () {

                Route::post('/tauxDecaissement', 'tauxDecaissement')->name('tauxDecaissement')->middleware('permission:voir-un-decaissement');
            });
        });

        Route::apiResource('objectifSpecifiques', 'ObjectifSpecifiqueController')->names('objectifSpecifiques');

        Route::apiResource('objectifGlobaux', 'ObjectifGlobauxController')->names('objectifGlobaux');

        Route::apiResource('resultats', 'ResultatController')->names('resultats');

        Route::apiResource('rappels', 'RappelController')->names('rappels');

        Route::apiResource('alerteConfig', 'AlerteConfigController', ['only' => ['index', 'update']])->names('alerteConfig');

        Route::group(['prefix' =>  'backups', 'as' => 'backups.'], function () {
            Route::controller('BackupController')->group(function () {

                Route::get('/lancer', 'lancer')->name('lancer');
                Route::get('/listes', 'listes')->name('listes');
            });
        });

        Route::apiResource('reponses', 'ReponseController')->names('reponses');

        Route::group(['prefix' =>  'gfa', 'as' => 'gfa.'], function () {

            Route::apiResource('permissions', 'PermissionController', ['only' => ['index']])->names('permissions')->middleware(['permission:voir-une-permission']);

            Route::apiResource('roles', 'RoleController')->names('roles');

            Route::apiResource('utilisateurs', 'UserController')->names('utilisateurs');

            Route::group(['prefix' =>  'utilisateurs', 'as' => 'utilisateurs.'], function () {

                Route::controller('UserController')->group(function () {

                    Route::post('/creation-de-compte-bailleur', 'creationDeCompteBailleur')->name('creationDeCompteBailleur')->middleware('permission:creer-un-bailleur');
                    Route::put('/mis-à-jour-de-compte-bailleur/{id}', 'miseAJourDeCompteBailleur')->name('miseAJourDeCompteBailleur')->middleware('permission:modifier-un-bailleur');
                    Route::get('/bailleurs', 'bailleurs')->name('bailleurs')->middleware('permission:voir-un-bailleur');
                    Route::post('/logo', 'createLogo')->name('createLogo')->middleware('permission:modifier-un-bailleur');
                    Route::post('/photo', 'createPhoto')->name('createPhoto')->middleware('permission:modifier-un-utilisateur');
                    Route::get('/getNotifications', 'getNotifications')->name('getNotifications');
                    Route::put('/readNotifications', 'readNotifications')->name('readNotifications');
                    Route::get('/deleteNotifications/{id}', 'deleteNotifications')->name('deleteNotifications');
                    Route::get('/deleteAllNotifications', 'deleteAllNotifications')->name('deleteAllNotifications');
                    Route::get('/fichiers', 'fichiers')->name('fichiers')->middleware('permission:voir-un-fichier');
                    Route::put('/{id}', 'update')->name('update');
                    Route::post('/updatePassword', 'updatePassword')->name('updatePassword');
                    Route::get('/{id}/permissions', 'permissions')->name('permissions');
                });
            });

            Route::apiResource('programmes', 'ProgrammeController')->names('programmes')/* ->middleware(['role:administrateur,super-admin']) */;

            Route::group(['prefix' =>  'programmes', 'as' => 'programmes.'], function () {

                Route::controller('ProgrammeController')->group(function () {

                    Route::get('{id}/projets', 'projets')->name('projets')->middleware('permission:voir-un-projet');

                    Route::get('{id}/sites', 'sites')->name('sites')->middleware('permission:voir-un-site');

                    Route::get('{id}/categories', 'categories')->name('categories')->middleware('permission:voir-une-categorie');

                    Route::get('{id}/cadre-de-mesure-rendement', 'cadre_de_mesure_rendement')->name('cadre-de-mesure-rendement')->middleware('permission:voir-cadre-de-rendement');

                    Route::get('evolution-des-scores-au-fil-du-temps/{organisationId}', 'scoresAuFilDuTemps')->name('evolution-des-scores-au-fil-du-temps')->middleware('permission:voir-statistique-evolution-des-profiles-de-gouvernance-au-fil-du-temps');
                    Route::get('evaluations-organisations/{id?}', 'evaluations_organisations')->name('evaluations-organisations')->middleware('permission:voir-une-organisation');
                    Route::get('evaluations-organisations-stats/{id?}', 'stats_evaluations_de_gouvernance_organisations')->name('evaluations-organisations')->middleware('permission:voir-une-organisation');


                });
            });

            Route::apiResource('unitees-de-mesure', 'UniteeMesureController', ['except' => ['index']])->names('unitees_de_mesure')->middleware(['role:unitee-de-gestion']);

            Route::apiResource('unitees-de-mesure', 'UniteeMesureController', ['only' => ['index']])->names('unitees_de_mesure');

            Route::group(['prefix' =>  'ptas', 'as' => 'ptas.'], function () {
                Route::controller('PtaController')->group(function () {

                    Route::post('/generer', 'generer')->name('generer')->middleware('permission:voir-ptab');

                    Route::post('/filtre', 'filtre')->name('filtre')->middleware('permission:voir-ptab');
                });

                Route::controller('PtabRevisionController')->group(function () {

                    Route::post('/getOldPtaReviser', 'getOldPtaReviser')->name('getOldPtaReviser')->middleware('permission:voir-revision-ptab');

                    Route::post('/getPtabReviser', 'getPtabReviser')->name('getPtabReviser')->middleware('permission:voir-revision-ptab');

                    Route::post('/reviserPtab', 'reviserPtab')->name('reviserPtab')->middleware('permission:faire-revision-ptab');

                    Route::get('/listVersionPtab', 'getListVersionPtab')->name('getListVersionPtab')->middleware('permission:voir-revision-ptab');
                });
            });

            Route::apiResource('organisations', 'OrganisationController', ['except' => ['index']])->names('organisations')->middleware(['role:unitee-de-gestion']);

            Route::apiResource('organisations', 'OrganisationController', ['only' => ['index']])->names('organisation')->middleware('permission:voir-une-organisation');

            Route::apiResource('planDecaissements', 'PlanDecaissementController')->names('plan-decaissements');

            Route::apiResource('projets', 'ProjetController', ['except' => ['index', 'show', 'update']])->names('projets')->middleware(['role:unitee-de-gestion']);

            Route::apiResource('projets', 'ProjetController', ['only' => ['index', 'show']])->names('projet')->middleware('permission:voir-un-projet');

            Route::group(['prefix' =>  'projets', 'as' => 'projets.'], function () {

                Route::controller('ProjetController')->group(function () {

                    Route::put('{id}/update', 'update')->name('update')->middleware('permission:modifier-un-projet');

                    Route::post('{projet}/prolonger', 'prolonger')->name('prolonger')->middleware('permission:prolonger-un-projet');

                    Route::get('{id}/composantes', 'composantes')->name('composantes')->middleware('permission:voir-un-outcome');

                    Route::post('{id}/tef', 'tef')->name('tef');

                    Route::get('{id}/statistiques', 'statistiques')->name('statistiques')->middleware('permission:voir-un-projet');

                    Route::get('{id}/mesure-rendement', 'mesure_rendement')->name('mesure-rendement')->middleware('permission:voir-cadre-de-rendement');

                });
            });

            Route::apiResource('composantes', 'ComposanteController', ['except' => ['index']])->names('composantes');

            Route::apiResource('composantes', 'ComposanteController', ['only' => ['index']])->names('composante')->middleware('permission:voir-un-outcome');

            Route::group(['prefix' =>  'composantes', 'as' => 'composantes.'], function () {

                Route::controller('ComposanteController')->group(function () {

                    Route::get('{id?}/sousComposantes', 'sousComposantes')->name('sousComposantes')->middleware('permission:voir-un-output');

                    Route::get('{id}/activites', 'activites')->name('activites')->middleware('permission:voir-une-activite');

                    //Route::get('{id}/filtreActivites', 'filtreActivites')->name('filtreActivites');

                    Route::get('/{id}/suivis', 'suivis')->name('suivis')->middleware('permission:voir-un-suivi');

                    Route::post('{id}/deplacer', 'deplacer')->name('deplacer')->middleware('permission:modifier-un-outcome');
                });
            });

            Route::apiResource('activites', 'ActiviteController')->names('activites');

            Route::group(['prefix' =>  'activites', 'as' => 'activites.'], function () {

                Route::controller('ActiviteController')->group(function () {

                    //Route::post('{activite}/prolonger', 'prolonger')->name('prolonger')->middleware('permission:prolonger-une-activite');

                    Route::post('/{id}/changeStatut', 'changeStatut')->name('changeStatut')/*->middleware('permission:voir-un-suivi')*/;

                    Route::get('/{id}/taches', 'taches')->name('taches')->middleware('permission:voir-une-tache');

                    Route::get('/{id}/suivis', 'suivis')->name('suivis')->middleware('permission:voir-un-suivi');
                    Route::post('/{id}/suivis-financier', 'suivis_financier')->name('suivis_financier')->middleware('permission:voir-un-suivi-financier');


                    Route::post('{id}/ajouterDuree', 'ajouterDuree')->name('ajouterDuree')->middleware('permission:modifier-une-activite');

                    Route::post('{id}/modifierDuree/{dureeId}', 'modifierDuree')->name('modifierDuree')->middleware('permission:modifier-une-activite');

                    Route::post('{id}/deplacer', 'deplacer')->name('deplacer')->middleware('permission:modifier-une-tache');

                    Route::get('{id}/plansDeDecaissement', 'plansDeDecaissement')->name('plansDeDecaissement')->middleware('permission:voir-un-plan-de-decaissement');
                });
            });

            Route::apiResource('taches', 'TacheController')->parameters([
                'tach' => 'tache'
            ])->names('taches');

            Route::group(['prefix' =>  'taches', 'as' => 'taches.'], function () {
                Route::controller('TacheController')->group(function () {

                    Route::post('{tache}/prolonger', 'prolonger')->name('prolonger')->middleware('permission:prolonger-une-tache');

                    Route::get('/{id}/suivis', 'suivis')->name('suivis')->middleware('permission:voir-un-suivi');

                    Route::get('/{id}/changeStatut', 'changeStatut')->name('changeStatut')->middleware('permission:modifier-un-suivi');

                    Route::post('/{id}/suivisV2', 'suivisV2')->name('suivisV2')->middleware('permission:creer-un-suivi');

                    Route::post('{id}/ajouterDuree', 'ajouterDuree')->name('ajouterDuree')->middleware('permission:modifier-une-tache');

                    Route::post('{id}/modifierDuree/{dureeId}', 'modifierDuree')->name('modifierDuree')->middleware('permission:modifier-une-tache');

                    Route::post('{id}/deplacer', 'deplacer')->name('deplacer')->middleware('permission:modifier-une-tache');
                });
            });

            Route::apiResource('suivis', 'SuiviController')->names('suivis');

            Route::group(['prefix' =>  'suivis', 'as' => 'suivis.'], function () {
                Route::controller('SuiviController')->group(function () {

                    Route::post('filterByModule', 'getSuivis')->name('getSuivis')->middleware('permission:voir-un-suivi');

                    Route::post('suivisV2', 'suivisV2')->name('suivisV2')->middleware('permission:voir-un-suivi');
                });
            });

            Route::post('suivisV2', 'SuiviController@suivisV2');

            Route::apiResource('suiviFinanciers', 'SuiviFinancierController')->names('suivi-financiers');

            Route::group(['prefix' =>  'suiviFinanciers', 'as' => 'suiviFinanciers'], function () {
                Route::controller('SuiviFinancierController')->group(function () {

                    Route::post('importation', 'importation')->name('importation')->middleware('permission:importer-un-suivi-financier');

                    Route::post('filtres', 'filtre')->name('filtres')->middleware('permission:voir-un-suivi-financier');
                    Route::post('trismestreASsuivre', 'trismestreASsuivre')->name('trismestreASsuivre')->middleware('permission:voir-un-suivi-financier');
                });
            });

            Route::apiResource('indicateur-value-keys', 'IndicateurValueKeyController', ['except' => ['index']])->names('indicateur-value-keys')
                ->parameters([
                    'indicateur-value-keys' => 'indicateur_value_key',
                ])->middleware(['role:unitee-de-gestion']);

            Route::apiResource('indicateur-value-keys', 'IndicateurValueKeyController', ['only' => ['index']])->names('indicateur-value-keys')
                ->parameters([
                    'indicateur-value-keys' => 'indicateur_value_key',
                ]);

            Route::apiResource('indicateurs', 'IndicateurController', ['except' => ['index']])->names('indicateurs')/* ->middleware(['role:unitee-de-gestion']) */;

            Route::apiResource('indicateurs', 'IndicateurController', ['only' => ['index']])->names('indicateurs');


            Route::apiResource('sites', 'SiteController', ['except' => ['index']])->names('sites')->middleware(['role:unitee-de-gestion']);

            Route::apiResource('sites', 'SiteController', ['only' => ['index']])->names('sites');

            Route::group(['prefix' =>  'indicateurs', 'as' => 'indicateurs.'], function () {

                Route::controller('IndicateurController')->group(function () {

                    Route::get('{id}/checkSuivi/{year}', 'checkSuivi')->name('checkSuivi')->middleware('permission:voir-un-suivi-indicateur');

                    Route::get('{id}/suivis', 'suivis')->name('suivis')->middleware('permission:voir-un-suivi-indicateur');

                    Route::post('filtres', 'filtre')->name('filtre')->middleware('permission:voir-un-indicateur');

                    // Gestion des clés de valeurs
                    Route::post('{indicateur}/addValueKeys', 'addValueKeys')->name('addValueKeys')->middleware('permission:ajouter-une-cle-de-valeur-indicateur');
                    Route::post('{indicateur}/removeValueKeys', 'removeValueKeys')->name('removeValueKeys')->middleware('permission:supprimer-une-cle-de-valeur-indicateur');

                    // Gestion des structures responsables
                    Route::post('{indicateur}/addStrutureResponsable', 'addStrutureResponsable')->name('addStrutureResponsable')->middleware('permission:creer-un-indicateur');

                    // Gestion des années cibles
                    Route::post('{indicateur}/addAnneesCible', 'addAnneesCible')->name('addAnneesCible')->middleware('permission:creer-un-indicateur');

                    // Nouvelles fonctionnalités - Modification des valeurs cibles
                    Route::put('{indicateur}/valeurs-cibles', 'updateValeursCibles')->name('updateValeursCibles')->middleware('permission:modifier-un-indicateur');
                    Route::put('{indicateur}/valeurs-cibles/{annee}', 'updateValeurCibleAnnee')->name('updateValeurCibleAnnee')->middleware('permission:modifier-un-indicateur');
                    Route::delete('{indicateur}/valeurs-cibles/{annee}', 'deleteValeurCibleAnnee')->name('deleteValeurCibleAnnee')->middleware('permission:supprimer-un-indicateur');

                    // Modification de la valeur de base
                    Route::put('{indicateur}/valeur-de-base', 'updateValeurDeBase')->name('updateValeurDeBase')->middleware('permission:modifier-un-indicateur');

                    // Changement de type d'indicateur
                    Route::put('{indicateur}/change-type', 'changeIndicateurType')->name('changeIndicateurType')->middleware('permission:modifier-un-indicateur');

                    // Modification complète
                    Route::put('{indicateur}/complet', 'updateIndicateurComplet')->name('updateIndicateurComplet')->middleware('permission:modifier-un-indicateur');
                });
            });

            Route::apiResource('suivi-indicateurs', 'SuiviIndicateurController')->names('suivi-indicateurs')->parameters([
                'suivi-indicateurs' => 'suivi_indicateur',
            ]);

            Route::controller('SuiviIndicateurController')->group(function () {

                Route::post('suivi-indicateurs/{suivi_indicateur}/valider', 'valider')->name('valider-un-suivi-indicateur')->middleware('permission:valider-un-suivi-indicateur');

                Route::post('suivi-indicateurs/filter', 'filtre')->name('filtre')->middleware('permission:voir-un-suivi-indicateur');

                Route::post('suivi-indicateurs/dateSuivie', 'dateSuivie')->name('dateSuivie')->middleware('permission:voir-un-suivi-indicateur');
            });

            Route::apiResource('categories', 'CategorieController', ['except' => ['index']])->names('categories')->middleware(['role:unitee-de-gestion']);

            Route::apiResource('categories', 'CategorieController', ['only' => ['index']])->names('categories');

            Route::apiResource('commentaires', 'CommentaireController')->names('commentaires');

            Route::apiResource('types-de-gouvernance', 'TypeDeGouvernanceController')->names('types-de-gouvernance')
                ->parameters([
                    'types-de-gouvernance' => 'type_de_gouvernance',
                ]);

            Route::group(['prefix' =>  'types-de-gouvernance', 'as' => 'types-de-gouvernance.'], function () {

                Route::controller('TypeDeGouvernanceController')->group(function () {

                    Route::get('{type_de_gouvernance}/principes-de-gouvernance', 'principes')->name('principes')->middleware('permission:voir-un-principe-de-gouvernance');
                });
            });

            Route::apiResource('principes-de-gouvernance', 'PrincipeDeGouvernanceController')->names('principes-de-gouvernance')
                ->parameters([
                    'principes-de-gouvernance' => 'principe_de_gouvernance',
                ]);

            Route::group(['prefix' =>  'principes-de-gouvernance', 'as' => 'principes-de-gouvernance.'], function () {

                Route::controller('PrincipeDeGouvernanceController')->group(function () {

                    Route::get('{principe_de_gouvernance}/criteres-de-gouvernance', 'criteres')->name('criteres')->middleware('permission:voir-un-critere-de-gouvernance');

                    Route::get('{principe_de_gouvernance}/indicateurs-de-gouvernance', 'indicateurs')->name('indicateurs')->middleware('permission:voir-un-indicateur-de-gouvernance');
                });
            });

            Route::apiResource('criteres-de-gouvernance', 'CritereDeGouvernanceController')->names('criteres-de-gouvernance')
                ->parameters([
                    'criteres-de-gouvernance' => 'critere_de_gouvernance',
                ]);

            Route::group(['prefix' =>  'criteres-de-gouvernance', 'as' => 'criteres-de-gouvernance.'], function () {

                Route::controller('CritereDeGouvernanceController')->group(function () {

                    Route::get('{critere_de_gouvernance}/indicateurs-de-gouvernance', 'indicateurs')->name('indicateurs')/*->middleware('permission:voir-un-indicateur-de-gouvernance')*/;
                });
            });

            Route::apiResource('indicateurs-de-gouvernance', 'IndicateurDeGouvernanceController')->names('indicateurs-de-gouvernance')
                ->parameters([
                    'indicateurs-de-gouvernance' => 'indicateur_de_gouvernance',
                ]);

            Route::apiResource('questions-operationnelle', 'IndicateurDeGouvernanceController')->names('questions-operationnelle')
                ->parameters([
                    'questions-operationnelle' => 'indicateur_de_gouvernance',
                ]);

            Route::group(['prefix' =>  'indicateurs-de-gouvernance', 'as' => 'indicateurs-de-gouvernance.'], function () {

                Route::controller('IndicateurDeGouvernanceController')->group(function () {

                    Route::get('{indicateur_de_gouvernance}/observations', 'observations')->name('observations'); //->middleware('permission:faire-une-observation-indicateur-de-gouvernance');

                });
            });

            Route::apiResource('options-de-reponse', 'OptionDeReponseController')->names('options-de-reponse')
                ->parameters([
                    'options-de-reponse' => 'option_de_reponse',
                ]);

            /*Route::apiResource('sources-de-verification', 'SourceDeVerificationController')->names('sources-de-verification')
                ->parameters([
                    'sources-de-verification' => 'source_de_verification',
                ]);*/

            Route::apiResource('fonds', 'FondController')->names('fonds')
                ->parameters([
                    'fonds' => 'fond',
                ]);

            Route::apiResource('recommandations', 'RecommandationController')->names('recommandations');
            Route::apiResource('actions-a-mener', 'ActionAMenerController')->names('actions-a-mener')
                ->parameters([
                    'actions-a-mener' => 'action_a_mener',
                ]);

            Route::controller('ActionAMenerController')->group(function () {
                Route::post('actions-a-mener/{action_a_mener}/valider', 'valider')->name('valider-action-a-mener')->middleware('permission:valider-une-action-a-mener');
                Route::post('actions-a-mener/{action_a_mener}/notifier-action-a-mener-terminer', 'notifierActionAMenerEstTerminer')->name('signaler-action-a-mener')->middleware('permission:signaler-une-action-a-mener-est-realise');
            });

            Route::apiResource('formulaires-de-gouvernance', 'FormulaireDeGouvernanceController')->names('formulaires-de-gouvernance')
                ->parameters([
                    'formulaires-de-gouvernance' => 'formulaire_de_gouvernance',
                ]);

            Route::apiResource('evaluations-de-gouvernance', 'EvaluationDeGouvernanceController')->names('evaluations-de-gouvernance')
                ->parameters([
                    'evaluations-de-gouvernance' => 'evaluation_de_gouvernance',
                ]);

            Route::get('formulaire-factuel/{token}', 'EvaluationDeGouvernanceController@formulaire_factuel_de_gouvernance')->middleware('permission:voir-formulaire-factuel');

            Route::group(['prefix' =>  'evaluations-de-gouvernance', 'as' => 'evaluations-de-gouvernance.'], function () {

                Route::controller('EvaluationDeGouvernanceController')->group(function () {

                    Route::get('{evaluation_de_gouvernance}/organisations', 'organisations')->name('organisations')->middleware('permission:voir-une-organisation');

                    Route::get('{evaluation_de_gouvernance}/formulaires-de-gouvernance', 'formulaires_de_gouvernance')->name('formulaires_de_gouvernance')->middleware('permission:voir-un-formulaire-de-gouvernance');
                    Route::get('{evaluation_de_gouvernance}/formulaire-factuel', 'formulaire_factuel')->name('formulaire_factuel')->middleware('permission:voir-formulaire-factuel');
                    Route::get('{evaluation_de_gouvernance}/rappel-soumission', 'rappel_soumission')->name('rappel_soumission')->middleware('permission:envoyer-un-rappel-soumission');

                    Route::apiResource('{evaluation_de_gouvernance}/soumissions', 'SoumissionController', ['except' => ['update']])->names('soumissions');
                    Route::post('{evaluation_de_gouvernance}/validate-soumission', 'SoumissionController@validated')->name('validate-soumission')->middleware('permission:valider-une-soumission');

                    Route::get('{evaluation_de_gouvernance}/soumissions', 'soumissions')->name('soumissions')->middleware('permission:voir-une-soumission');

                    Route::get('{evaluation_de_gouvernance}/fiches-de-synthese', 'fiches_de_synthese')->name('fiches_de_synthese')->middleware('permission:voir-une-fiche-de-synthese');
                    Route::get('{evaluation_de_gouvernance}/resultats-syntheses', 'resultats_syntheses')->name('resultats_syntheses')->middleware('permission:voir-resultats-evaluation');
                    Route::get('{evaluation_de_gouvernance}/voir-resultats-avec-classement-organisations', 'voir_resultats_syntheses_avec_classement_des_organisations')->name('voir_resultats_syntheses_avec_classement_des_organisations')->middleware('permission:voir-resultats-evaluation');

                    Route::get('{evaluation_de_gouvernance}/fiches-de-synthese-with-organisations-classement', 'fiches_de_synthese_with_organisations_classement')->name('fiches_de_synthese_with_organisations_classement')->middleware('permission:voir-une-fiche-de-synthese');

                    Route::post('{evaluation_de_gouvernance}/envoi-mail-au-participants', 'envoi_mail_au_participants')->name('envoi_mail_au_participants')->middleware('permission:envoyer-une-invitation');

                    Route::get('{evaluation_de_gouvernance}/principes-de-gouvernance', 'principes')->name('principes-de-gouvernance')->middleware('permission:voir-une-evaluation-de-gouvernance');

                    Route::get('{evaluation_de_gouvernance}/recommandations', 'recommandations')->name('recommandations')->middleware('permission:voir-une-recommandation');
                    Route::get('{evaluation_de_gouvernance}/actions-a-mener', 'actions_a_mener')->name('actions-a-mener')->middleware('permission:voir-une-action-a-mener');

                    Route::get('{evaluation_de_gouvernance}/feuille-de-route', 'feuille_de_route')->name('feuille-de-route')->middleware('permission:voir-plan-action');
                    Route::post('{evaluation_de_gouvernance}/ajouterObjectifAttenduParPrincipe', 'ajouterObjectifAttenduParPrincipe')->name('feuille-de-route')->middleware('permission:creer-une-evaluation-de-gouvernance');
                });

                Route::post('{evaluation_de_gouvernance}/validate-soumission', 'SoumissionController@validated')->name('evaluation.validate-soumission')->middleware('permission:valider-une-soumission');
            });

            Route::apiResource('survey-forms', 'SurveyFormController')->names('survey-forms')
                ->parameters([
                    'survey-forms' => 'survey_form',
                ]);

            Route::apiResource('surveys', 'SurveyController')->names('surveys')
                ->parameters([
                    'surveys' => 'survey',
                ]);


            Route::apiResource('survey-reponses', 'SurveyReponseController', ['except' => ['update']])->names('survey-reponses')
                ->parameters([
                    'survey-reponses' => 'survey_reponse',
                ]);

            Route::group(['prefix' =>  'surveys', 'as' => 'surveys.'], function () {

                Route::controller('SurveyController')->group(function () {
                    Route::get('{survey}/survey-reponses', 'survey_reponses')->name('survey_reponses')->middleware('permission:voir-reponses-enquete-individuelle');
                    Route::get('{survey}/formulaire', 'formulaire')->name('formulaire')->middleware('permission:voir-un-formulaire-individuel');

                    Route::get('{token}/form/{participantId}', 'private_survey_form')->name('private_survey_form')->middleware('permission:voir-un-formulaire-individuel');
                });
            });
        });
    });

    Route::group(['prefix' =>  'gfa', 'as' => 'gfa.'], function () {
        Route::get('formulaire-de-perception/{participantId}/{token}', 'EvaluationDeGouvernanceController@formulaire_de_perception_de_gouvernance'); //->middleware('permission:faire-une-observation-indicateur-de-gouvernance');

        Route::group(['prefix' =>  'evaluations-de-gouvernance', 'as' => 'evaluations-de-gouvernance.'], function () {
            //Route::get('{evaluation_de_gouvernance}', 'EvaluationDeGouvernanceController@show'); //->middleware('permission:faire-une-observation-indicateur-de-gouvernance');
            Route::post('{evaluation_de_gouvernance}/perception-soumission', 'SoumissionController@storePerception')->name('evaluation.perception-soumission'); //->middleware('permission:faire-une-observation-indicateur-de-gouvernance');
            Route::post('{evaluation_de_gouvernance}/perception-soumission-validation', 'SoumissionController@perceptionSoumissionValidation'); //->name('evaluation.perception.soumission.validation')->middleware('permission:faire-une-observation-indicateur-de-gouvernance');
            //Route::get('{evaluation_de_gouvernance}?paricipant_id={participantId}&token={$token}', 'EvaluationDeGouvernanceController@formulaire_de_perception_de_gouvernance')->name('formulaire_de_perception_de_gouvernance'); //->middleware('permission:faire-une-observation-indicateur-de-gouvernance');

        });

        Route::group(['prefix' =>  'surveys', 'as' => 'surveys.'], function () {

            Route::get('/{token}/form/{participantId}', 'SurveyController@public_survey_form');

            Route::controller('SurveyReponseController')->group(function () {

                Route::post('reponses', 'survey_reponse')->name('survey_reponse');
            });
        });
    });

    /**
     * Enquete de gouvernance
     */
    Route::group(['prefix' =>  'gfa', 'as' => 'gfa.'], function () {
        Route::group(['prefix' =>  'enquete-de-gouvernance', 'as' => 'enquetes-de-gouvernance.', 'namespace' => 'enquetes_de_gouvernance'], function () {

            Route::group(['middleware' => ['auth:sanctum']], function () {


                Route::apiResource('types-de-gouvernance-factuel', 'TypeDeGouvernanceFactuelController')->names('types-de-gouvernance-factuel')
                    ->parameters([
                        'types-de-gouvernance-factuel' => 'type_de_gouvernance_factuel',
                    ]);

                Route::group(['prefix' =>  'types-de-gouvernance', 'as' => 'types-de-gouvernance.'], function () {

                    Route::controller('TypeDeGouvernanceFactuelController')->group(function () {

                        Route::get('{type_de_gouvernance_factuel}/principes-de-gouvernance', 'principes')->name('principes')->middleware('permission:voir-un-principe-de-gouvernance');
                    });
                });

                Route::apiResource('principes-de-gouvernance-factuel', 'PrincipeDeGouvernanceFactuelController')->names('principes-de-gouvernance-factuel')
                    ->parameters([
                        'principes-de-gouvernance-factuel' => 'principe_de_gouvernance_factuel',
                    ]);

                Route::apiResource('principes-de-gouvernance-de-perception', 'PrincipeDeGouvernancePerceptionController')->names('principes-de-gouvernance-de-perception')
                    ->parameters([
                        'principes-de-gouvernance-de-perception' => 'principe_de_perception',
                    ]);

                Route::apiResource('criteres-de-gouvernance-factuel', 'CritereDeGouvernanceFactuelController')->names('criteres-de-gouvernance-factuel')
                    ->parameters([
                        'criteres-de-gouvernance-factuel' => 'critere_de_gouvernance_factuel',
                    ]);

                Route::apiResource('indicateurs-de-gouvernance-factuel', 'IndicateurDeGouvernanceFactuelController')->names('indicateurs-de-gouvernance-factuel')
                    ->parameters([
                        'indicateurs-de-gouvernance-factuel' => 'indicateur_factuel',
                    ]);

                Route::apiResource('questions-operationnelle', 'QuestionOperationnelleController')->names('questions-operationnelle')
                    ->parameters([
                        'questions-operationnelle' => 'question_operationnelle',
                    ]);

                Route::apiResource('options-de-reponse-gouvernance', 'OptionDeReponseGouvernanceController')->names('options-de-reponse-gouvernance')
                    ->parameters([
                        'options-de-reponse-gouvernance' => 'option_de_reponse_gouvernance',
                    ]);

                Route::controller('OptionDeReponseGouvernanceController')->group(function () {

                    Route::get('options-de-reponse-gouvernance-factuel', 'options_factuel')->name('options-factuel')->middleware('permission:voir-une-option-de-reponse');
                    Route::get('options-de-reponse-gouvernance-de-perception', 'options_de_perception')->name('options-de-perception')->middleware('permission:voir-une-option-de-reponse');
                });


                Route::apiResource('sources-de-verification', 'SourceDeVerificationController')->names('sources-de-verification')
                    ->parameters([
                        'sources-de-verification' => 'source_de_verification',
                    ]);

                Route::apiResource('formulaires-de-perception-de-gouvernance', 'FormulaireDePerceptionDeGouvernanceController')->names('formulaires-de-perception-de-gouvernance')
                    ->parameters([
                        'formulaires-de-perception-de-gouvernance' => 'formulaire_de_perception',
                    ]);

                Route::apiResource('formulaires-factuel-de-gouvernance', 'FormulaireFactuelDeGouvernanceController')->names('formulaires-factuel-de-gouvernance')
                    ->parameters([
                        'formulaires-factuel-de-gouvernance' => 'formulaire_factuel',
                    ]);


                Route::apiResource('evaluations-de-gouvernance', 'EvaluationDeGouvernanceController')->names('evaluations-de-gouvernance')
                    ->parameters([
                        'evaluations-de-gouvernance' => 'evaluation_de_gouvernance',
                    ]);

                Route::get('evaluations-de-gouvernance-factuel/{token}', 'EvaluationDeGouvernanceController@formulaire_factuel_de_gouvernance')->middleware('permission:voir-formulaire-factuel');

                Route::delete('soumissions-factuel/{soumissionId}/preuves/{preuveId}', 'SoumissionFactuelController@deletePreuve')->name('delete-preuve')->middleware('permission:supprimer-une-preuve');

                Route::group(['prefix' =>  'evaluations-de-gouvernance', 'as' => 'evaluations-de-gouvernance.'], function () {

                    Route::apiResource('{evaluation_de_gouvernance}/soumissions-factuel', 'SoumissionFactuelController', ['except' => ['update']])->names('soumissions');
                    Route::post('{evaluation_de_gouvernance}/validate-soumission-factuel', 'SoumissionFactuelController@validated')->name('validate-soumission')->middleware('permission:valider-une-soumission');

                    Route::apiResource('{evaluation_de_gouvernance}/soumissions-de-perception', 'SoumissionDePerceptionController', ['only' => ['index', 'show']])->names('soumissions-de-perception');

                    Route::controller('EvaluationDeGouvernanceController')->group(function () {
                        Route::get('{evaluation_de_gouvernance}/organisations', 'organisations')->name('organisations')->middleware('permission:voir-une-organisation');

                        Route::get('{evaluation_de_gouvernance}/formulaires-de-gouvernance', 'formulaires_de_gouvernance')->name('formulaires_de_gouvernance')->middleware('permission:voir-un-formulaire-de-gouvernance');
                        Route::get('{evaluation_de_gouvernance}/formulaire-factuel', 'formulaire_factuel')->name('formulaire_factuel')->middleware('permission:voir-formulaire-factuel');
                        Route::get('{evaluation_de_gouvernance}/rappel-soumission', 'rappel_soumission')->name('rappel_soumission')->middleware('permission:envoyer-un-rappel-soumission');
                        Route::post('{evaluation_de_gouvernance}/envoi-mail-au-participants', 'envoi_mail_au_participants')->name('envoi_mail_au_participants')->middleware('permission:envoyer-une-invitation');

                        Route::get('{evaluation_de_gouvernance}/recommandations', 'recommandations')->name('recommandations')->middleware('permission:voir-une-recommandation');
                        Route::get('{evaluation_de_gouvernance}/actions-a-mener', 'actions_a_mener')->name('actions-a-mener')->middleware('permission:voir-une-action-a-mener');

                        Route::get('{evaluation_de_gouvernance}/feuille-de-route', 'feuille_de_route')->name('feuille-de-route')->middleware('permission:voir-plan-action');
                        Route::post('{evaluation_de_gouvernance}/ajouterObjectifAttenduParPrincipe', 'ajouterObjectifAttenduParPrincipe')->name('feuille-de-route')->middleware('permission:creer-une-evaluation-de-gouvernance');

                        Route::get('{evaluation_de_gouvernance}/soumissions', 'soumissions_enquete')->name('soumissions_enquete')->middleware('permission:voir-une-soumission');
                        //Route::get('{evaluation_de_gouvernance}/soumissions-factuel', 'soumissions_enquete_factuel')->name('soumissions_enquete_factuel')->middleware('permission:voir-une-soumission');
                        Route::get('{evaluation_de_gouvernance}/soumissions-perception', 'soumissions_enquete_de_perception')->name('soumissions_enquete_de_perception')->middleware('permission:voir-une-soumission');

                        Route::get('{evaluation_de_gouvernance}/fiches-de-synthese', 'fiches_de_synthese')->name('fiches_de_synthese')->middleware('permission:voir-une-fiche-de-synthese');
                        Route::get('{evaluation_de_gouvernance}/resultats-syntheses', 'resultats_syntheses')->name('resultats_syntheses')->middleware('permission:voir-resultats-evaluation');
                        Route::get('{evaluation_de_gouvernance}/voir-resultats-avec-classement-organisations', 'voir_resultats_syntheses_avec_classement_des_organisations')->name('voir_resultats_syntheses_avec_classement_des_organisations')->middleware('permission:voir-resultats-evaluation');

                        Route::get('{evaluation_de_gouvernance}/fiches-de-synthese-with-organisations-classement', 'fiches_de_synthese_with_organisations_classement')->name('fiches_de_synthese_with_organisations_classement');
                    });
                });

                Route::apiResource('recommandations', 'RecommandationController')->names('recommandations');
                Route::apiResource('actions-a-mener', 'ActionAMenerController')->names('actions-a-mener')
                    ->parameters([
                        'actions-a-mener' => 'action_a_mener',
                    ]);

                Route::controller('ActionAMenerController')->group(function () {
                    Route::post('actions-a-mener/{action_a_mener}/valider', 'valider')->name('valider-action-a-mener')->middleware('permission:valider-une-action-a-mener');
                    Route::post('actions-a-mener/{action_a_mener}/notifier-action-a-mener-terminer', 'notifierActionAMenerEstTerminer')->name('signaler-action-a-mener')->middleware('permission:signaler-une-action-a-mener-est-realise');
                });

                Route::get('result', function(){
                    return test_generation();
                });
            });

            Route::get('evaluations-de-gouvernance-perception/{participantId}/{token}', 'EvaluationDeGouvernanceController@formulaire_de_perception_de_gouvernance');

            Route::group(['prefix' =>  'evaluations-de-gouvernance-de-perception', 'as' => 'evaluations-de-gouvernance.'], function () {
                Route::apiResource('{evaluation_de_gouvernance}/soumissions', 'SoumissionDePerceptionController', ['only' => ['store']])->names('save-soumission-de-perception');
                Route::post('{evaluation_de_gouvernance}/validate-soumission', 'SoumissionDePerceptionController@validated')->name('validate-soumission-de-perception');
            });
        });
    });
});

$evaluationDeGouvernance;

function test_generation(){

    $results = [];
    $i = 0;
    EvaluationGouvernance::where("statut", 0)->get()->map(function ($evaluationDeGouvernance)  use (&$results, &$i) {
        /* $this->evaluationDeGouvernance = $evaluationDeGouvernance;
        $results[$i] = $this->generateResultForEnquete($evaluationDeGouvernance);
        $i++; */
        return generateResultForEnquete($evaluationDeGouvernance, $results);

    });

    return $results;
}

function generateResultForEnquete(EvaluationGouvernance $evaluationDeGouvernance, &$resultats)
{
    $i = 0;

    $evaluationDeGouvernance->organisations->map(function ($organisation) use ($evaluationDeGouvernance, &$resultats, &$i) {

        $results = [];

        $groups_soumissions['factuel'] = $organisation->sousmissions_enquete_factuel()->where("evaluationId", $evaluationDeGouvernance->id)->get();

        $groups_soumissions['perception'] = $organisation->sousmissions_enquete_de_perception()->where("evaluationId", $evaluationDeGouvernance->id)->get();

        $profile = null;
        $organisationId = $organisation->id;

        if (!$evaluationOrganisationId = $evaluationDeGouvernance->organisations()->wherePivot('organisationId', $organisationId)->first()->pivot) {
            return;
        }

        $evaluationOrganisationId = $evaluationOrganisationId->id;

        if ($evaluationDeGouvernance->formulaire_factuel_de_gouvernance()) {

            [$indice_factuel, $results, $synthese] = generateResultForFactuelEvaluation($evaluationDeGouvernance, $evaluationDeGouvernance->formulaire_factuel_de_gouvernance(), $organisationId);

            $resultats[$i . $organisation->user->nom]['factuel'] = $synthese;
            if ($fiche_de_synthese = $evaluationDeGouvernance->fiches_de_synthese($organisationId, 'factuel')->first()) {
                $fiche_de_synthese->update(['type' => 'factuel', 'indice_de_gouvernance' => $indice_factuel, 'resultats' => $results, 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernance_id' => $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->id, 'formulaireDeGouvernance_type' => get_class($evaluationDeGouvernance->formulaire_factuel_de_gouvernance()), 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
            } else {
                //dd(['type' => 'factuel', 'indice_de_gouvernance' => $indice_factuel, 'resultats' => $results, 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernance_id' => $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->id, 'formulaireDeGouvernance_type' => get_class($evaluationDeGouvernance->formulaire_factuel_de_gouvernance()), 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                app(FichesDeSyntheseRepository::class)->create(['type' => 'factuel', 'indice_de_gouvernance' => $indice_factuel, 'resultats' => $results, 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernance_id' => $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->id, 'formulaireDeGouvernance_type' => get_class($evaluationDeGouvernance->formulaire_factuel_de_gouvernance()), 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
            }

            if ($profile || ($profile = $evaluationDeGouvernance->profiles($organisationId, $evaluationOrganisationId)->first())) {

                // Convert $profile->resultat_synthetique to an associative array for easy updating
                $resultat_synthetique = collect($profile->resultat_synthetique)->keyBy('id');

                // Iterate over each item in $results to update or add to $resultat_synthetique
                foreach ($results as $result) {
                    $resultat_synthetique[$result['id']] = array_merge($resultat_synthetique->get($result['id'], []), $result);
                }

                // Convert back to a regular array if needed
                $updated_resultat_synthetique = $resultat_synthetique->values()->toArray();

                $profile->update(['resultat_synthetique' => $updated_resultat_synthetique]);
            } else {
                // Convert $results to an associative array for easy updating
                $resultat_synthetique = collect($results)->keyBy('id');

                // Iterate over each item in $results to update or add to $resultat_synthetique
                foreach ($results as $result) {
                    $resultat_synthetique[$result['id']] = array_merge($resultat_synthetique->get($result['id'], []), $result);
                }

                // Convert back to a regular array if needed
                $results = $resultat_synthetique->values()->toArray();

                $profile = ProfileDeGouvernance::create(['resultat_synthetique' => $results, 'evaluationOrganisationId' => $evaluationOrganisationId, 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
            }
        }
            if ($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()) {
                [$indice_de_perception, $results, $synthese] = generateResultForPerceptionEvaluation($evaluationDeGouvernance, $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance(), $organisationId);

                $resultats[$i . $organisation->user->nom]['perception'] = $synthese;
                if ($fiche_de_synthese = $evaluationDeGouvernance->fiches_de_synthese($organisationId, 'perception')->first()) {
                    $fiche_de_synthese->update(['type' => 'perception', 'indice_de_gouvernance' => $indice_de_perception, 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernance_id' => $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()->id, 'formulaireDeGouvernance_type' => get_class($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()), 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                } else {
                    app(FichesDeSyntheseRepository::class)->create(['type' => 'perception', 'indice_de_gouvernance' => $indice_de_perception, 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernance_id' => $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()->id, 'organisationId' => $organisationId, 'formulaireDeGouvernance_type' => get_class($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()), 'programmeId' => $evaluationDeGouvernance->programmeId]);
                }

                if ($profile || ($profile = $evaluationDeGouvernance->profiles($organisationId, $evaluationOrganisationId)->first())) {

                    // Convert $profile->resultat_synthetique to an associative array for easy updating
                    $resultat_synthetique = collect($profile->resultat_synthetique)->keyBy('id');

                    // Iterate over each item in $results to update or add to $resultat_synthetique
                    foreach ($results as $result) {
                        $resultat_synthetique[$result['id']] = array_merge($resultat_synthetique->get($result['id'], []), $result);
                    }

                    // Convert back to a regular array if needed
                    $updated_resultat_synthetique = $resultat_synthetique->values()->toArray();

                    $profile->update(['resultat_synthetique' => $updated_resultat_synthetique]);
                } else {

                    // Convert $results to an associative array for easy updating
                    $resultat_synthetique = collect($results)->keyBy('id');

                    // Iterate over each item in $results to update or add to $resultat_synthetique
                    foreach ($results as $result) {
                        $resultat_synthetique[$result['id']] = array_merge($resultat_synthetique->get($result['id'], []), $result);
                    }

                    // Convert back to a regular array if needed
                    $results = $resultat_synthetique->values()->toArray();

                    $profile = ProfileDeGouvernance::create(['resultat_synthetique' => $results, 'evaluationOrganisationId' => $evaluationOrganisationId, 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                }
            }

            if ($profile = $evaluationDeGouvernance->profiles($organisationId, $evaluationOrganisationId)->first()) {

                // Convert $profile->resultat_synthetique to an associative collection for easy updating
                $resultat_synthetique = collect($profile->resultat_synthetique)->keyBy('id');

                // Iterate over each item in $results to update or add to $resultat_synthetique
                foreach ($results as $result) {
                    // Check if the entry exists in $resultat_synthetique
                    if ($existing = $resultat_synthetique->get($result['id'])) {

                        // Calculate indice_synthetique by summing indice_factuel and indice_de_perception
                        $existing['indice_synthetique'] = geometricMean([($existing['indice_factuel'] ?? 0), ($existing['indice_de_perception'] ?? 0)]);

                        $resultat_synthetique[$result['id']] = array_merge($resultat_synthetique->get($result['id'], []), $existing);
                    }
                }

                // Convert back to a regular array if needed
                $updated_resultat_synthetique = $resultat_synthetique->values()->toArray();

                // Update the profile with the modified array
                $profile->update(['resultat_synthetique' => $updated_resultat_synthetique]);
                $resultats[$i . $organisation->user->nom]['resultat_synthetique'] = $updated_resultat_synthetique;
                $resultats[$i . $organisation->user->nom]['profile'] = $profile;
            }

        $i++;
    });

    return $resultats;
}

function generateResultForPerceptionEvaluation(EvaluationGouvernance $evaluation, FormulaireDePerceptionDeGouvernance $formulaireDeGouvernance, $organisationId)
{
    $options_de_reponse = $formulaireDeGouvernance->options_de_reponse;
    $principes_de_gouvernance = collect([]);

    $evaluationId = $evaluation->id;

    $results_categories_de_gouvernance = $formulaireDeGouvernance->categories_de_gouvernance()->with('questions_de_gouvernance.reponses')->get()->each(function ($categorie_de_gouvernance) use ($evaluationId, $organisationId, $options_de_reponse, &$principes_de_gouvernance) {
        $categorie_de_gouvernance->questions_de_gouvernance->load(['reponses' => function ($query) use ($evaluationId, $organisationId) {
            $query->whereHas("soumission", function ($query) use ($evaluationId, $organisationId) {
                $query->where('evaluationId', $evaluationId)->where('organisationId', $organisationId);
            });
        }])->each(function ($question_de_gouvernance) use ($organisationId, $options_de_reponse) {

            // Get the total number of responses for NBRE_R
            $nbre_r = $question_de_gouvernance->reponses/* ()->where('type', 'question_operationnelle')->whereHas("soumission", function ($query) use ($organisationId) {
                $query->where('evaluationId', $this->evaluationDeGouvernance->id)->where('organisationId', $organisationId);
            }) */->count();

            // Initialize the weighted sum
            $weighted_sum = 0;
            $index = 0;
            $question_de_gouvernance->options_de_reponse = collect([]);

            $counts = $question_de_gouvernance->reponses()
                ->selectRaw('optionDeReponseId, COUNT(*) as count')
                ->groupBy('optionDeReponseId')
                ->pluck('count', 'optionDeReponseId');

            foreach ($options_de_reponse as $key => $option_de_reponse) {
                //$reponses_count = $question_de_gouvernance->reponses()->where("optionDeReponseId", $option_de_reponse->id)->count();

                $reponses_count = $counts[$option_de_reponse->id] ?? 0;
                $optionPoint = $option_de_reponse->pivot->point;

                // Accumulate the weighted sum
                $weighted_sum += $moyenne_ponderee_i = $optionPoint * $reponses_count;

                $option = $option_de_reponse;

                $option->reponses_count = $reponses_count;

                $option->moyenne_ponderee_i = $moyenne_ponderee_i;

                $question_de_gouvernance->options_de_reponse[$key] = $option;
            }

            // Calculate the weighted average
            if ($nbre_r > 0) {
                $question_de_gouvernance->moyenne_ponderee = round(($weighted_sum / $nbre_r), 2);
            } else {
                $question_de_gouvernance->moyenne_ponderee = 0; // Avoid division by zero
            }
        });

        // Now, calculate the 'indice_de_perception' for the category
        $total_moyenne_ponderee = $categorie_de_gouvernance->questions_de_gouvernance->sum('moyenne_ponderee');
        $nbre_questions_operationnelle = $categorie_de_gouvernance->questions_de_gouvernance->count();

        // Check to avoid division by zero
        $categorie_de_gouvernance->indice_de_perception = ($nbre_questions_operationnelle > 0) ? round(($total_moyenne_ponderee / $nbre_questions_operationnelle), 2) : 0;

        $principes_de_gouvernance->push(['id' => $categorie_de_gouvernance->categorieable->id, 'nom' => $categorie_de_gouvernance->categorieable->nom, 'indice_de_perception' => $categorie_de_gouvernance->indice_de_perception]);
    });
    $indice_de_perception = round(($results_categories_de_gouvernance->sum('indice_de_perception') / $results_categories_de_gouvernance->count()), 2);
    return [$indice_de_perception, $principes_de_gouvernance, FicheDeSyntheseEvaluationFactuelleResource::collection($results_categories_de_gouvernance) ];
}

function generateResultForFactuelEvaluation(EvaluationGouvernance $evaluation, FormulaireFactuelDeGouvernance $formulaireDeGouvernance, $organisationId)
{

    $principes_de_gouvernance = collect([]);

    $evaluationId = $evaluation->id;

    $results_categories_de_gouvernance = $formulaireDeGouvernance->categories_de_gouvernance()->with(['sousCategoriesDeGouvernance' => function ($query) use ($evaluationId, $organisationId) {
        // Call the recursive function to load nested relationships
        loadCategories($query, $evaluationId, $organisationId);
    }])->get()->each(function ($categorie_de_gouvernance) use ($evaluationId, $organisationId, &$principes_de_gouvernance) {
        $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) use ($evaluationId, $organisationId, &$principes_de_gouvernance) {
            $reponses = interpretData($sous_categorie_de_gouvernance, $evaluationId, $organisationId);

            $indicateurs = getIndicateurs($sous_categorie_de_gouvernance, $organisationId);

            // Calculate indice_factuel
            if (count($indicateurs) > 0 && $reponses->sum('point') > 0) {
                $sous_categorie_de_gouvernance->score_factuel = round(($reponses->sum('point') / count($indicateurs)), 2);
            } else {
                $sous_categorie_de_gouvernance->score_factuel = 0;
            }

            if ($principes_de_gouvernance->count()) {
                // Check if the item exists in the collection
                if ($principes_de_gouvernance->firstWhere('id', $sous_categorie_de_gouvernance->categorieable_id)) {
                    // Update the collection item by transforming it
                    $principes_de_gouvernance = $principes_de_gouvernance->transform(function ($item) use ($sous_categorie_de_gouvernance) {

                        if ($item['id'] === $sous_categorie_de_gouvernance->categorieable_id) {
                            // Update the score_factuel
                            $item['indice_factuel'] += $sous_categorie_de_gouvernance->score_factuel;
                        }
                        return $item;
                    });
                } else {
                    // If the item doesn't exist push the new item
                    $principes_de_gouvernance->push(['id' => $sous_categorie_de_gouvernance->categorieable_id, 'nom' => $sous_categorie_de_gouvernance->categorieable->nom, 'indice_factuel' => $sous_categorie_de_gouvernance->score_factuel]);
                }
            } else {
                // If the collection is empty, push the new item
                $principes_de_gouvernance->push(['id' => $sous_categorie_de_gouvernance->categorieable_id, 'nom' => $sous_categorie_de_gouvernance->categorieable->nom, 'indice_factuel' => $sous_categorie_de_gouvernance->score_factuel]);
            }
        });

        // Calculate indice_factuel
        if ($categorie_de_gouvernance->sousCategoriesDeGouvernance->count() > 0 && $categorie_de_gouvernance->sousCategoriesDeGouvernance->sum('score_factuel') > 0) {
            $categorie_de_gouvernance->indice_factuel = round(($categorie_de_gouvernance->sousCategoriesDeGouvernance->sum('score_factuel') / $categorie_de_gouvernance->sousCategoriesDeGouvernance->count()), 2);
        } else {
            $categorie_de_gouvernance->indice_factuel = 0;
        }
    });

    $indice_factuel = round(($results_categories_de_gouvernance->sum('indice_factuel') / $results_categories_de_gouvernance->count()), 2);

    return [$indice_factuel, $principes_de_gouvernance, FicheDeSyntheseEvaluationFactuelleResource::collection($results_categories_de_gouvernance)];
}

function geometricMean(array $numbers): float
{
    // Filter out non-positive numbers, as geometric mean is undefined for them
    $filteredNumbers = array_filter($numbers, fn($number) => $number > 0);

    // If the filtered array is empty, return 0
    if (empty($filteredNumbers)) {
        return 0;
    }

    // Calculate the product of the numbers
    $product = array_product($filteredNumbers);

    // Count the number of elements
    $n = count($filteredNumbers);

    // Calculate the geometric mean
    $geometricMean = pow($product, 1 / $n);

    // Return the result rounded to 2 decimal places
    return round($geometricMean, 2);
}

function loadCategories($query, $evaluationId, $organisationId)
{
    $query->with(['sousCategoriesDeGouvernance' => function ($query) use ($evaluationId, $organisationId) {
        // Recursively load sousCategoriesDeGouvernance
        loadCategories($query, $evaluationId, $organisationId);
    }, 'questions_de_gouvernance.reponses' => function ($query) use ($evaluationId, $organisationId) {
        $query->whereHas("soumission", function ($query) use ($evaluationId, $organisationId) {
            $query->where('evaluationId', $evaluationId)->where('organisationId', $organisationId);
        })->sum('point');
    }]);
}

function interpretData($categorie_de_gouvernance, $evaluationId, $organisationId)
{
    $reponses = [];
    if ($categorie_de_gouvernance->sousCategoriesDeGouvernance->count()) {
        $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) use (&$reponses, $evaluationId, $organisationId) {
            $reponses_data = interpretData($sous_categorie_de_gouvernance, $evaluationId, $organisationId);
            $reponses = array_merge($reponses, $reponses_data->toArray());
        });
    } else {
        $categorie_de_gouvernance->questions_de_gouvernance->each(function ($question_de_gouvernance) use (&$reponses, $evaluationId, $organisationId) {
            $reponses_de_collecte = $question_de_gouvernance->reponses()->whereHas("soumission", function ($query) use ($evaluationId, $organisationId) {
                $query->where('evaluationId', $evaluationId)->where('organisationId', $organisationId);
            })->get()->toArray();
            $reponses = array_merge($reponses, $reponses_de_collecte);
        });
    }

    return collect($reponses);
}

function getIndicateurs($categorie_de_gouvernance, $organisationId)
{
    $indicateurs = [];
    if ($categorie_de_gouvernance->sousCategoriesDeGouvernance->count()) {
        $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) use (&$indicateurs, $organisationId) {
            $data = getIndicateurs($sous_categorie_de_gouvernance, $organisationId);

            $indicateurs = array_merge($indicateurs, $data->toArray());
        });
    } else {
        $indicateurs = array_merge($indicateurs, $categorie_de_gouvernance->questions_de_gouvernance->toArray());
    }

    return collect($indicateurs);
}
