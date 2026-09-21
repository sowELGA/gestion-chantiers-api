<?php

use App\Http\Controllers\Api\Admin\DemandeResetController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ChefProjet\ApprovisionnementController as CpApprovisionnementController;
use App\Http\Controllers\Api\ChefProjet\PlanningController;
use App\Http\Controllers\Api\ChefProjet\ValidationController;
use App\Http\Controllers\Api\Daf\ApprovisionnementController as DafApprovisionnementController;
use App\Http\Controllers\Api\Daf\DepenseController;
use App\Http\Controllers\Api\DirecteurTravaux\ChantierController;
use App\Http\Controllers\Api\Pointeur\PointageController;
use App\Http\Controllers\Api\Pointeur\ReceptionController;
use App\Http\Controllers\Api\Rh\OuvrierController;
use App\Http\Controllers\Api\Rh\PosteController;
use App\Http\Controllers\Api\Rh\SalaireController;
use App\Http\Controllers\Api\Rh\TauxSalaireController;
use App\Http\Controllers\Api\ChefProjet\RapportController as CpRapportController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DirecteurTravaux\RapportController as DtRapportController;
use App\Http\Controllers\Api\ChefProjet\DashboardController as CpDashboardController;
use App\Http\Controllers\Api\Pointeur\DashboardController as PointeurDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn() => response()->json(['status' => 'ok']));

// ── Authentification (routes publiques) ──────────────────────
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:4,1');
Route::post('/mot-de-passe-oublie', [AuthController::class, 'motDePasseOublie'])->middleware('throttle:3,1');

// ── Routes protégées (nécessitent un token valide) ───────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/changer-mot-de-passe', [AuthController::class, 'changePassword']);

    Route::get('/roles', [RoleController::class, 'index']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    // ── Espace Admin : gestion des utilisateurs ──────────────
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/utilisateurs', [UserController::class, 'index']);
        Route::post('/utilisateurs', [UserController::class, 'store']);
        Route::get('/utilisateurs/{user}', [UserController::class, 'show']);
        Route::put('/utilisateurs/{user}', [UserController::class, 'update']);
        Route::patch('/utilisateurs/{user}/toggle', [UserController::class, 'toggleActif']);
        Route::patch('/utilisateurs/{user}/reinitialiser', [UserController::class, 'reinitialiserMotDePasse']);

        Route::get('/demandes-reset', [DemandeResetController::class, 'index']);
        Route::patch('/demandes-reset/{demande}/resoudre', [DemandeResetController::class, 'resoudre']);
    });

    // ── Directeur des travaux : gestion des chantiers ────────────
    Route::middleware('role:directeur_travaux')->group(function () {

        Route::prefix('chantiers')->group(function () {
            Route::get('/', [ChantierController::class, 'index']);
            Route::get('/chefs-projets-disponibles', [ChantierController::class, 'chefsProjetsDisponibles']);
            Route::post('/', [ChantierController::class, 'store']);
            Route::get('/{chantier}', [ChantierController::class, 'show']);
            Route::put('/{chantier}', [ChantierController::class, 'update']);
            Route::delete('/{chantier}', [ChantierController::class, 'destroy']);
            Route::patch('/{chantier}/chef-projet', [ChantierController::class, 'affecterChefProjet']);
            Route::patch('/{chantier}/pointeur', [ChantierController::class, 'affecterPointeur']);
            Route::patch('/{chantier}/statut/{statut}', [ChantierController::class, 'changerStatut']);
        });

        Route::prefix('dt/rapports')->group(function () {
            Route::get('/form-options', [DtRapportController::class, 'formOptions']);
            Route::get('/', [DtRapportController::class, 'index']);
            Route::get('/{rapport}', [DtRapportController::class, 'show']);
            Route::get('/{rapport}/pdf', [DtRapportController::class, 'telechargerPdf']);
        });
    });

    // ── DAF : Approvisionnements ──────────────────────────────────
    Route::middleware(['role:daf', 'daf.permission:approvisionnements'])->prefix('daf/approvisionnements')->group(function () {
        Route::get('/', [DafApprovisionnementController::class, 'index']);
        Route::get('/historique', [DafApprovisionnementController::class, 'historique']);
        Route::patch('/{demande}/valider', [DafApprovisionnementController::class, 'valider']);
        Route::patch('/{demande}/rejeter', [DafApprovisionnementController::class, 'rejeter']);
        Route::patch('/{demande}/commander', [DafApprovisionnementController::class, 'passerCommande']);
        Route::patch('/{demande}/date-livraison', [DafApprovisionnementController::class, 'definirDateLivraison']);
    });

    // ── DAF : Dépenses ─────────────────────────────────────────────
    Route::middleware(['role:daf', 'daf.permission:depenses'])->prefix('daf/depenses')->group(function () {
        Route::get('/', [DepenseController::class, 'index']);
        Route::get('/{chantier}', [DepenseController::class, 'show']);
        Route::post('/{chantier}', [DepenseController::class, 'store']);
        Route::delete('/{depense}', [DepenseController::class, 'destroy']);
    });

    Route::middleware('role:chef_projet')->group(function () {

        // Dashboard
        Route::get('/chef-projet/dashboard', [CpDashboardController::class, 'index']);

        Route::prefix('mes-chantiers')->group(function () {

            // Chantiers
            Route::get('/', [ChantierController::class, 'indexChefProjet']);
            Route::get('/{chantier}', [ChantierController::class, 'showChefProjet']);

            // Phases
            Route::get('/{chantier}/phases', [PlanningController::class, 'indexPhases']);
            Route::get('/{chantier}/phases/prochain-ordre', [PlanningController::class, 'prochainOrdre']);
            Route::post('/{chantier}/phases', [PlanningController::class, 'storePhase']);
            Route::put('/{chantier}/phases/{phase}', [PlanningController::class, 'updatePhase']);
            Route::delete('/{chantier}/phases/{phase}', [PlanningController::class, 'destroyPhase']);

            // Tâches
            Route::get('/{chantier}/phases/{phase}/taches', [PlanningController::class, 'indexTaches']);
            Route::get('/{chantier}/phases/{phase}/taches-disponibles', [PlanningController::class, 'tachesDisponibles']);
            Route::post('/{chantier}/phases/{phase}/taches', [PlanningController::class, 'storeTache']);
            Route::put('/{chantier}/phases/{phase}/taches/{tache}', [PlanningController::class, 'updateTache']);
            Route::delete('/{chantier}/phases/{phase}/taches/{tache}', [PlanningController::class, 'destroyTache']);
            Route::patch('/{chantier}/phases/{phase}/taches/{tache}/avancement', [PlanningController::class, 'mettreAJourAvancement']);

            // Récap / validation du pointage
            Route::get('/{chantier}/recap', [ValidationController::class, 'index']);
            Route::post('/{chantier}/recap/valider', [ValidationController::class, 'valider']);
            Route::post('/{chantier}/recap/rejeter', [ValidationController::class, 'rejeter']);
        });

        Route::prefix('approvisionnements')->group(function () {
            Route::get('/chantiers-disponibles', [CpApprovisionnementController::class, 'chantiersDisponibles']);
            Route::get('/', [CpApprovisionnementController::class, 'index']);
            Route::post('/', [CpApprovisionnementController::class, 'store']);
            Route::put('/{demande}', [CpApprovisionnementController::class, 'update']);
            Route::delete('/{demande}', [CpApprovisionnementController::class, 'destroy']);
        });

        Route::prefix('rapports')->group(function () {
            Route::get('/form-options', [CpRapportController::class, 'formOptions']);
            Route::get('/', [CpRapportController::class, 'index']);
            Route::post('/', [CpRapportController::class, 'store']);
            Route::get('/{rapport}', [CpRapportController::class, 'show']);
            Route::put('/{rapport}', [CpRapportController::class, 'update']);
            Route::delete('/{rapport}', [CpRapportController::class, 'destroy']);
        });
    });

    // ── Pointeur : réception des livraisons ──────────────────────
    Route::middleware('role:pointeur')->group(function () {

        Route::get('/pointeur/dashboard', [PointeurDashboardController::class, 'index']);

        //réception des livraisons
        Route::prefix('pointeur/livraisons')->group(function () {
            Route::get('/', [ReceptionController::class, 'livraisons']);
            Route::get('/historique', [ReceptionController::class, 'historiqueLivraisons']);
            Route::post('/{demande}/receptionner', [ReceptionController::class, 'validerReception']);
            Route::get('/bons/{bon}/pdf', [ReceptionController::class, 'bonReceptionPdf']);
        });

        //pointage
        Route::prefix('pointeur/pointage')->group(function () {
            Route::get('/fiche', [PointageController::class, 'ficheJour']);
            Route::post('/fiche', [PointageController::class, 'enregistrerFiche']);
            Route::get('/recap', [PointageController::class, 'recapSemaine']);
            Route::post('/soumettre', [PointageController::class, 'soumettreSemaine']);
            Route::get('/modifier/{date}', [PointageController::class, 'modifierJour'])->where('date', '\d{4}-\d{2}-\d{2}');
            Route::post('/modifier', [PointageController::class, 'enregistrerModificationJour']);
        });
    });

    Route::middleware('role:responsable_rh')->prefix('rh')->group(function () {
        Route::get('/ouvriers', [OuvrierController::class, 'index']);
        Route::get('/ouvriers/form-options', [OuvrierController::class, 'formOptions']);
        Route::post('/ouvriers', [OuvrierController::class, 'store']);
        Route::put('/ouvriers/{ouvrier}', [OuvrierController::class, 'update']);
        Route::patch('/ouvriers/{ouvrier}/toggle', [OuvrierController::class, 'toggleStatut']);
        Route::delete('/ouvriers/{ouvrier}', [OuvrierController::class, 'destroy']);

        Route::get('/postes', [PosteController::class, 'index']);
        Route::post('/postes', [PosteController::class, 'store']);
        Route::put('/postes/{poste}', [PosteController::class, 'update']);
        Route::delete('/postes/{poste}', [PosteController::class, 'destroy']);

        Route::get('/taux-salaires', [TauxSalaireController::class, 'index']);
        Route::get('/taux-salaires/{chantier}', [TauxSalaireController::class, 'show']);
        Route::put('/taux-salaires/{chantier}', [TauxSalaireController::class, 'update']);

        Route::prefix('salaires')->group(function () {
            Route::get('/', [SalaireController::class, 'index']);
            Route::get('/{chantier}/apercu', [SalaireController::class, 'apercu']);
            Route::get('/{chantier}/pdf', [SalaireController::class, 'genererPdf']);
        });
    });
});
