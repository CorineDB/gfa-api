<?php

namespace App\Console\Commands\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance;
use App\Notifications\EvaluationNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Commande pour mettre à jour automatiquement le statut des évaluations de gouvernance
 *
 * Statuts:
 * -1 : Non démarrée (date de début future)
 *  0 : En cours (entre date de début et date de fin)
 *  1 : Terminée (date de fin dépassée)
 */
class UpdateEvaluationStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gouvernance:update-evaluation-statuses
                            {--dry-run : Afficher les changements sans les appliquer}
                            {--force : Forcer la mise à jour même si la date n\'est pas atteinte}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Met à jour automatiquement le statut des évaluations de gouvernance selon leurs dates de début et fin';

    /**
     * Date du jour
     *
     * @var string
     */
    protected $today;

    /**
     * URL de base pour les liens
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Mode dry-run (simulation sans modification)
     *
     * @var bool
     */
    protected $dryRun = false;

    /**
     * Compteurs pour les statistiques
     */
    protected $stats = [
        'started' => 0,
        'ended' => 0,
        'reset_to_pending' => 0,
        'notifications_sent' => 0,
        'notifications_failed' => 0,
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->dryRun = $this->option('dry-run');
        $this->today = Carbon::today()->toDateString();
        $this->baseUrl = $this->getBaseUrl();

        $this->info("🔄 Mise à jour des statuts des évaluations de gouvernance");
        $this->info("📅 Date du jour: {$this->today}");

        if ($this->dryRun) {
            $this->warn("⚠️  MODE DRY-RUN: Aucune modification ne sera appliquée");
        }

        $this->newLine();

        try {
            // 1. Démarrer les évaluations qui doivent commencer
            $this->handleStartingEvaluations();

            // 2. Terminer les évaluations qui doivent se clôturer
            $this->handleEndingEvaluations();

            // 3. Remettre en attente les évaluations dont la date de début est repoussée
            $this->handleResetToPendingEvaluations();

            $this->displayStatistics();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erreur critique: {$e->getMessage()}");
            Log::error('UpdateEvaluationStatuses failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Démarrer les évaluations dont la date de début est atteinte
     */
    protected function handleStartingEvaluations(): void
    {
        $this->info("📢 Recherche des évaluations à démarrer...");

        $evaluations = EvaluationDeGouvernance::query()
            ->where('debut', '<=', $this->today)
            ->where('statut', '<', 0)
            ->with(['organisations.user', 'programme'])
            ->get();

        if ($evaluations->isEmpty()) {
            $this->line("   Aucune évaluation à démarrer");
            return;
        }

        $this->line("   {$evaluations->count()} évaluation(s) à démarrer");

        foreach ($evaluations as $evaluation) {
            $this->processStartingEvaluation($evaluation);
        }
    }

    /**
     * Traiter le démarrage d'une évaluation
     */
    protected function processStartingEvaluation(EvaluationDeGouvernance $evaluation): void
    {
        DB::beginTransaction();

        try {
            $this->line("   → Démarrage: {$evaluation->intitule} (Année: {$evaluation->annee_exercice})");

            if (!$this->dryRun) {
                $evaluation->update(['statut' => 0]);
                $this->stats['started']++;
            }

            // Envoyer les notifications aux organisations
            $this->sendStartNotifications($evaluation);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("     ❌ Erreur lors du démarrage: {$e->getMessage()}");
            Log::error('Failed to start evaluation', [
                'evaluation_id' => $evaluation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Terminer les évaluations dont la date de fin est atteinte
     */
    protected function handleEndingEvaluations(): void
    {
        $this->newLine();
        $this->info("🏁 Recherche des évaluations à clôturer...");

        $evaluations = EvaluationDeGouvernance::query()
            ->where('fin', '<=', $this->today)
            ->where('statut', 0) // Correction: utiliser 0 au lieu de '==', 0
            ->with(['organisations.user', 'programme'])
            ->get();

        if ($evaluations->isEmpty()) {
            $this->line("   Aucune évaluation à clôturer");
            return;
        }

        $this->line("   {$evaluations->count()} évaluation(s) à clôturer");

        foreach ($evaluations as $evaluation) {
            $this->processEndingEvaluation($evaluation);
        }
    }

    /**
     * Traiter la clôture d'une évaluation
     */
    protected function processEndingEvaluation(EvaluationDeGouvernance $evaluation): void
    {
        DB::beginTransaction();

        try {
            $this->line("   → Clôture: {$evaluation->intitule} (Année: {$evaluation->annee_exercice})");

            if (!$this->dryRun) {
                $evaluation->update(['statut' => 1]);
                $this->stats['ended']++;
            }

            // Envoyer les notifications aux organisations
            $this->sendEndNotifications($evaluation);

            // Générer le rapport final (en arrière-plan)
            if (!$this->dryRun) {
                $this->scheduleReportGeneration($evaluation);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("     ❌ Erreur lors de la clôture: {$e->getMessage()}");
            Log::error('Failed to end evaluation', [
                'evaluation_id' => $evaluation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remettre en attente les évaluations dont la date de début est repoussée
     */
    protected function handleResetToPendingEvaluations(): void
    {
        $this->newLine();
        $this->info("⏰ Recherche des évaluations à remettre en attente...");

        $evaluations = EvaluationDeGouvernance::query()
            ->where('debut', '>', $this->today)
            ->where('statut', '>=', 0)
            ->get();

        if ($evaluations->isEmpty()) {
            $this->line("   Aucune évaluation à remettre en attente");
            return;
        }

        $this->line("   {$evaluations->count()} évaluation(s) à remettre en attente");

        if (!$this->dryRun) {
            $updated = EvaluationDeGouvernance::query()
                ->where('debut', '>', $this->today)
                ->where('statut', '>=', 0)
                ->update(['statut' => -1]);

            $this->stats['reset_to_pending'] = $updated;
        }

        foreach ($evaluations as $evaluation) {
            $this->line("   → Remise en attente: {$evaluation->intitule}");
        }
    }

    /**
     * Envoyer les notifications de démarrage aux organisations
     */
    protected function sendStartNotifications(EvaluationDeGouvernance $evaluation): void
    {
        if ($this->dryRun) {
            $this->line("     📧 [DRY-RUN] Notifications de démarrage à envoyer: {$evaluation->organisations->count()} organisation(s)");
            return;
        }

        try {
            // Envoyer directement sans Job (les closures ne peuvent pas être sérialisées)
            foreach ($evaluation->organisations as $organisation) {
                $this->sendStartNotificationToOrganisation($evaluation, $organisation);
            }

            $this->stats['notifications_sent'] += $evaluation->organisations->count();
            $this->line("     ✅ Notifications de démarrage envoyées: {$evaluation->organisations->count()} organisation(s)");

        } catch (\Exception $e) {
            $this->stats['notifications_failed'] += $evaluation->organisations->count();
            $this->error("     ❌ Erreur lors de l'envoi des notifications: {$e->getMessage()}");
            Log::error('Failed to send start notifications', [
                'evaluation_id' => $evaluation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Envoyer une notification de démarrage à une organisation
     */
    protected function sendStartNotificationToOrganisation(EvaluationDeGouvernance $evaluation, $organisation): void
    {
        if (empty($organisation->user->email) || !filter_var($organisation->user->email, FILTER_VALIDATE_EMAIL)) {
            Log::warning("Invalid email for organisation", [
                'organisation_id' => $organisation->id,
                'organisation_name' => $organisation->user->nom ?? 'N/A',
            ]);
            return;
        }

        try {
            $data = [
                'module' => 'demarrage evaluation',
                'texte' => "Démarrage de l'évaluation d'auto-gouvernance {$evaluation->intitule}",
                'id' => $evaluation->id,
                'auteurId' => 0,
                'details' => [
                    'view' => 'emails.auto-evaluation.evaluation',
                    'subject' => "L'ENQUÊTE D'AUTO-ÉVALUATION DE GOUVERNANCE POUR L'ANNÉE D'EXERCICE {$evaluation->annee_exercice} A DÉMARRÉ",
                    'content' => [
                        'greeting' => "Salut, Monsieur/Madame! {$organisation->nom_point_focal} {$organisation->prenom_point_focal}",
                        'introduction' => "Nous vous informons du démarrage de l'enquête de collecte d'auto-évaluation de gouvernance pour l'évaluation de l'auto-gouvernance de {$evaluation->intitule}, dans le cadre de l'année d'exercice {$evaluation->annee_exercice}.",
                        'lien' => "{$this->baseUrl}/dashboard/tools-factuel/{$organisation->pivot->token}",
                        'link_text' => "Cliquez ici pour participer à l'enquête",
                    ],
                ],
            ];

            $notification = new EvaluationNotification($data, ['mail', 'database', 'broadcast']);
            $organisation->user->notify($notification);

        } catch (\Exception $e) {
            Log::error('Failed to send start notification to organisation', [
                'evaluation_id' => $evaluation->id,
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Envoyer les notifications de clôture aux organisations
     */
    protected function sendEndNotifications(EvaluationDeGouvernance $evaluation): void
    {
        if ($this->dryRun) {
            $this->line("     📧 [DRY-RUN] Notifications de clôture à envoyer: {$evaluation->organisations->count()} organisation(s)");
            return;
        }

        try {
            // Envoyer directement sans Job (les closures ne peuvent pas être sérialisées)
            foreach ($evaluation->organisations as $organisation) {
                $this->sendEndNotificationToOrganisation($evaluation, $organisation);
            }

            $this->stats['notifications_sent'] += $evaluation->organisations->count();
            $this->line("     ✅ Notifications de clôture envoyées: {$evaluation->organisations->count()} organisation(s)");

        } catch (\Exception $e) {
            $this->stats['notifications_failed'] += $evaluation->organisations->count();
            $this->error("     ❌ Erreur lors de l'envoi des notifications: {$e->getMessage()}");
            Log::error('Failed to send end notifications', [
                'evaluation_id' => $evaluation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Envoyer une notification de clôture à une organisation
     */
    protected function sendEndNotificationToOrganisation(EvaluationDeGouvernance $evaluation, $organisation): void
    {
        if (empty($organisation->user->email) || !filter_var($organisation->user->email, FILTER_VALIDATE_EMAIL)) {
            Log::warning("Invalid email for organisation", [
                'organisation_id' => $organisation->id,
                'organisation_name' => $organisation->user->nom ?? 'N/A',
            ]);
            return;
        }

        try {
            $data = [
                'module' => 'cloture evaluation',
                'texte' => "Clôture de l'enquête d'auto-évaluation de Gouvernance - Année {$evaluation->annee_exercice}",
                'id' => $evaluation->id,
                'auteurId' => 0,
                'details' => [
                    'view' => 'emails.auto-evaluation.evaluation',
                    'subject' => "Clôture de l'enquête d'auto-évaluation de Gouvernance - Année {$evaluation->annee_exercice}",
                    'content' => [
                        'greeting' => "Salut, Monsieur/Madame! {$organisation->nom_point_focal} {$organisation->prenom_point_focal}",
                        'introduction' => "Nous vous informons de la clôture de l'enquête d'auto-évaluation de gouvernance du programme {$evaluation->programme->nom} - Année {$evaluation->annee_exercice}.\nTrouvez dans le lien ci-dessous le résultat de l'enquête d'auto-évaluation.",
                        'lien' => "{$this->baseUrl}/dashboard/synthese/{$evaluation->secure_id}",
                        'link_text' => "Consulter le rapport final",
                    ],
                ],
            ];

            $notification = new EvaluationNotification($data, ['mail', 'database', 'broadcast']);
            $organisation->user->notify($notification);

        } catch (\Exception $e) {
            Log::error('Failed to send end notification to organisation', [
                'evaluation_id' => $evaluation->id,
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Générer les résultats pour l'évaluation terminée
     */
    protected function scheduleReportGeneration(EvaluationDeGouvernance $evaluation): void
    {
        try {
            // Générer les résultats UNIQUEMENT pour cette évaluation
            Artisan::call('gouvernance:generate-results', [
                'evaluationId' => $evaluation->id
            ]);

            $this->line("     📊 Résultats générés pour cette évaluation");

        } catch (\Exception $e) {
            $this->error("     ❌ Erreur lors de la génération des résultats: {$e->getMessage()}");
            Log::error('Failed to generate results for evaluation', [
                'evaluation_id' => $evaluation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Obtenir l'URL de base de l'application
     */
    protected function getBaseUrl(): string
    {
        $url = config('app.url');

        if (strpos($url, 'localhost') === false) {
            $url = config('app.organisation_url');
        }

        return $url;
    }

    /**
     * Afficher les statistiques finales
     */
    protected function displayStatistics(): void
    {
        $this->newLine();
        $this->info("📊 Statistiques:");
        $this->table(
            ['Action', 'Nombre'],
            [
                ['Évaluations démarrées', $this->stats['started']],
                ['Évaluations clôturées', $this->stats['ended']],
                ['Évaluations remises en attente', $this->stats['reset_to_pending']],
                ['Notifications envoyées', $this->stats['notifications_sent']],
                ['Notifications échouées', $this->stats['notifications_failed']],
            ]
        );

        if ($this->dryRun) {
            $this->newLine();
            $this->warn("⚠️  MODE DRY-RUN: Aucune modification n'a été appliquée");
        }
    }
}
