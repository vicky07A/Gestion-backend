<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * LISTER — Retourne toutes les tâches d'un projet
     * avec possibilité de filtrer par statut et priorité
     */
    public function index(Request $request, Project $project)
    {
        // Sécurité : le projet doit appartenir à l'utilisateur connecté
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // On commence la requête sur les tâches du projet
        $query = $project->tasks();

        // Filtre par statut si envoyé (ex: ?status=todo)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par priorité si envoyé (ex: ?priority=high)
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Recherche par titre si envoyé (ex: ?search=mon titre)
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->latest()->get());
    }

    /**
     * CRÉER — Ajoute une nouvelle tâche à un projet
     * Reçoit : title, description, priority, deadline, status
     */
    public function store(Request $request, Project $project)
    {
        // Sécurité : le projet doit appartenir à l'utilisateur connecté
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Validation des données
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'in:low,medium,high', // uniquement ces 3 valeurs
            'deadline'    => 'nullable|date',
            'status'      => 'in:todo,in_progress,done', // uniquement ces 3 valeurs
        ]);

        // On crée la tâche liée au projet
        $task = $project->tasks()->create([
            'title'       => $request->title,
            'description' => $request->description,
            'priority'    => $request->priority ?? 'medium', // medium par défaut
            'deadline'    => $request->deadline,
            'status'      => $request->status ?? 'todo', // todo par défaut
        ]);

        return response()->json($task, 201);
    }

    /**
     * DÉTAILS — Retourne les détails d'une tâche spécifique
     */
    public function show(Request $request, Project $project, Task $task)
    {
        // Sécurité : double vérification projet + tâche
        if ($project->user_id !== $request->user()->id || $task->project_id !== $project->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return response()->json($task);
    }

    /**
     * MODIFIER — Met à jour une tâche
     * Reçoit : title, description, priority, deadline, status
     */
    public function update(Request $request, Project $project, Task $task)
    {
        // Sécurité : double vérification projet + tâche
        if ($project->user_id !== $request->user()->id || $task->project_id !== $project->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'sometimes|in:low,medium,high',
            'deadline'    => 'nullable|date',
            'status'      => 'sometimes|in:todo,in_progress,done',
        ]);

        // On met à jour uniquement les champs envoyés
        $task->update($request->only([
            'title', 'description', 'priority', 'deadline', 'status'
        ]));

        return response()->json($task);
    }

    /**
     * CHANGER STATUT — Met à jour uniquement le statut d'une tâche
     * Utilisé par le Kanban pour le drag & drop
     * Reçoit : status (todo, in_progress, done)
     */
    public function updateStatus(Request $request, Project $project, Task $task)
    {
        // Sécurité : double vérification projet + tâche
        if ($project->user_id !== $request->user()->id || $task->project_id !== $project->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $request->validate([
            'status' => 'required|in:todo,in_progress,done',
        ]);

        // On met à jour uniquement le statut
        $task->update(['status' => $request->status]);

        return response()->json($task);
    }

    /**
     * SUPPRIMER — Supprime une tâche
     */
    public function destroy(Request $request, Project $project, Task $task)
    {
        // Sécurité : double vérification projet + tâche
        if ($project->user_id !== $request->user()->id || $task->project_id !== $project->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Tâche supprimée avec succès.'
        ]);
    }
}