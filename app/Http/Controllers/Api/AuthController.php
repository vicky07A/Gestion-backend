<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * INSCRIPTION — Crée un nouveau compte utilisateur
     * Reçoit : name, email, password, password_confirmation
     * Retourne : les infos de l'utilisateur + un token de connexion
     */
    public function register(Request $request)
    {
        // On valide les données envoyées par le frontend
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users', // email unique en base
            'password' => 'required|string|min:8|confirmed', // confirmed = doit avoir password_confirmation
        ]);

        // On crée l'utilisateur en base de données
        // Hash::make() chiffre le mot de passe (jamais stocker en clair !)
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // On génère un token unique pour cet utilisateur (sert à rester connecté)
        $token = $user->createToken('auth_token')->plainTextToken;

        // On retourne l'utilisateur + le token au frontend
        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201); // 201 = "Created" en HTTP
    }

    /**
     * CONNEXION — Vérifie les identifiants et connecte l'utilisateur
     * Reçoit : email, password
     * Retourne : les infos de l'utilisateur + un token de connexion
     */
    public function login(Request $request)
    {
        // On valide que l'email et le mot de passe sont bien envoyés
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // On cherche l'utilisateur par son email en base de données
        $user = User::where('email', $request->email)->first();

        // Si l'utilisateur n'existe pas OU que le mot de passe est incorrect
        // Hash::check() compare le mot de passe saisi avec celui chiffré en base
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        // Tout est bon : on génère un nouveau token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /**
     * DÉCONNEXION — Supprime le token actuel de l'utilisateur
     * L'utilisateur devra se reconnecter pour obtenir un nouveau token
     */
    public function logout(Request $request)
    {
        // On supprime uniquement le token utilisé pour cette session
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * MON PROFIL — Retourne les infos de l'utilisateur actuellement connecté
     * Utilisé par le frontend pour savoir qui est connecté
     */
    public function me(Request $request)
    {
        // $request->user() retourne automatiquement l'utilisateur lié au token
        return response()->json($request->user());
    }

    /**
 * MODIFIER PROFIL — Met à jour les informations de l'utilisateur connecté
 * Reçoit : name, email, password (optionnel)
 */
public function updateProfile(Request $request)
{
    $user = $request->user();

    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email,' . $user->id,
        'password' => 'nullable|string|min:8|confirmed',
    ]);

    // On met à jour le nom et l'email
    $user->name  = $request->name;
    $user->email = $request->email;

    // On met à jour le mot de passe seulement s'il est fourni
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return response()->json($user);
}
}