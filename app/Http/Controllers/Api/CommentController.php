<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * LISTER — Retourne tous les commentaires d'une tâche
     */
    public function index(Request $request, $projectId, Task $task)
    {
        // Vérifie que la tâche appartient bien au projet
        if ($task->project_id != $projectId) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // On charge les commentaires avec les infos de l'utilisateur
        $comments = $task->comments()->with('user')->latest()->get();

        return response()->json($comments);
    }

    /**
     * CRÉER — Ajoute un commentaire sur une tâche
     */
    public function store(Request $request, $projectId, Task $task)
    {
        // Vérifie que la tâche appartient bien au projet
        if ($task->project_id != $projectId) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        // On crée le commentaire lié à la tâche et à l'utilisateur connecté
        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        // On charge les infos de l'utilisateur pour les retourner
        $comment->load('user');

        return response()->json($comment, 201);
    }

    /**
     * SUPPRIMER — Supprime un commentaire
     * Seul l'auteur peut supprimer son commentaire
     */
    public function destroy(Request $request, $projectId, Task $task, Comment $comment)
    {
        // Vérifie que la tâche appartient bien au projet
        if ($task->project_id != $projectId) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Vérifie que l'utilisateur est bien l'auteur du commentaire
        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Commentaire supprimé.']);
    }
}