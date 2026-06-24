<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * LISTER — Retourne tous les projets de l'utilisateur connecté
     * avec le nombre de tâches pour chaque projet
     */
    public function index(Request $request)
    {
        $projects = Project::where('user_id', $request->user()->id)
            // On charge aussi les tâches liées à chaque projet
            ->withCount(['tasks', 'tasks as tasks_done_count' => function ($query) {
                // On compte séparément les tâches terminées
                $query->where('status', 'done');
            }])
            ->latest() // Du plus récent au plus ancien
            ->get();

        return response()->json($projects);
    }

    /**
     * CRÉER — Crée un nouveau projet pour l'utilisateur connecté
     * Reçoit : name, description, deadline
     */
    public function store(Request $request)
    {
        // Validation des données envoyées par le frontend
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline'    => 'nullable|date',
        ]);

        // On crée le projet en liant automatiquement l'utilisateur connecté
        $project = Project::create([
            'user_id'     => $request->user()->id,
            'name'        => $request->name,
            'description' => $request->description,
            'deadline'    => $request->deadline,
        ]);

        return response()->json($project, 201);
    }

    /**
     * DÉTAILS — Retourne les détails d'un projet avec ses tâches
     * Vérifie que le projet appartient bien à l'utilisateur connecté
     */
    public function show(Request $request, Project $project)
    {
        // Sécurité : on vérifie que le projet appartient à l'utilisateur connecté
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // On charge les tâches liées à ce projet
        $project->load('tasks');

        return response()->json($project);
    }

    /**
     * MODIFIER — Met à jour les informations d'un projet
     * Reçoit : name, description, deadline (tous optionnels)
     */
    public function update(Request $request, Project $project)
    {
        // Sécurité : vérification que le projet appartient à l'utilisateur
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'deadline'    => 'nullable|date',
        ]);

        // On met à jour uniquement les champs envoyés
        $project->update($request->only(['name', 'description', 'deadline']));

        return response()->json($project);
    }

    /**
     * SUPPRIMER — Supprime un projet UNIQUEMENT s'il n'a pas de tâches
     * Règle du cahier des charges : pas de suppression si tâches liées
     */
    public function destroy(Request $request, Project $project)
    {
        // Sécurité : vérification que le projet appartient à l'utilisateur
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // On vérifie s'il y a des tâches liées à ce projet
        if ($project->tasks()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer un projet qui contient des tâches.'
            ], 422);
        }

        $project->delete();

        return response()->json([
            'message' => 'Projet supprimé avec succès.'
        ]);
    }
}