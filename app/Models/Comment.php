<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'content',
    ];

    /**
     * Un commentaire appartient à une tâche
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Un commentaire appartient à un utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}