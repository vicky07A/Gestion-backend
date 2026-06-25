<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'deadline',
        'date_debut',
    ];

    protected $casts = [
        'deadline' => 'date',
        'date_debut' => 'date',
    ];

    // On demande à Laravel d'inclure automatiquement ces calculs dans le JSON envoyé au Frontend
    protected $appends = ['is_urgent', 'is_overdue', 'jours_restants'];

    /**
     * Calcule le nombre de jours restants avant la deadline
     */
    public function getJoursRestantsAttribute()
    {
        if (!$this->deadline) {
            return null;
        }

        $now = Carbon::now()->startOfDay();
        $deadline = Carbon::parse($this->deadline)->startOfDay();

        // Retourne la différence en jours (peut être négative si dépassée)
        return $now->diffInDays($deadline, false);
    }

    /**
     * Vérifie si le projet est urgent (Moins de 3 jours restants et non terminé)
     */
    public function getIsUrgentAttribute()
    {
        if (!$this->deadline) {
            return false;
        }

        $jours = $this->jours_restants;
        
        // Urgent si entre 0 et 3 jours restants
        return $jours >= 0 && $jours <= 3;
    }

    /**
     * Vérifie si le projet est en retard (Date limite dépassée)
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->deadline) {
            return false;
        }

        return $this->jours_restants < 0;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}