<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
 * ROUTES PUBLIQUES — Accessibles sans être connecté
 * Utilisées pour l'inscription et la connexion
 */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
 * ROUTES PROTÉGÉES — Nécessitent un token valide (être connecté)
 * Sanctum vérifie automatiquement le token à chaque requête
 */
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Modification du profil
Route::put('/profile', [AuthController::class, 'updateProfile']);

    /*
     * PROJETS — CRUD complet
     * GET    /api/projects         → liste des projets
     * POST   /api/projects         → créer un projet
     * GET    /api/projects/{id}    → détails d'un projet
     * PUT    /api/projects/{id}    → modifier un projet
     * DELETE /api/projects/{id}    → supprimer un projet
     */
    Route::apiResource('projects', ProjectController::class);

    /*
     * TÂCHES — CRUD complet (imbriquées dans les projets)
     * GET    /api/projects/{project}/tasks           → liste des tâches
     * POST   /api/projects/{project}/tasks           → créer une tâche
     * GET    /api/projects/{project}/tasks/{task}    → détails d'une tâche
     * PUT    /api/projects/{project}/tasks/{task}    → modifier une tâche
     * DELETE /api/projects/{project}/tasks/{task}    → supprimer une tâche
     */
    Route::apiResource('projects.tasks', TaskController::class);

    /*
     * STATUT — Route spéciale pour le Kanban (drag & drop)
     * PATCH  /api/projects/{project}/tasks/{task}/status → changer le statut
     */
    Route::patch(
        'projects/{project}/tasks/{task}/status',
        [TaskController::class, 'updateStatus']
    );
});