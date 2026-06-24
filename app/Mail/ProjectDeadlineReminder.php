<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectDeadlineReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * On passe l'utilisateur et le projet au constructeur
     * pour les utiliser dans le template email
     */
    public function __construct(
        public User $user,
        public Project $project
    ) {}

    /**
     * L'objet de l'email (ce qui apparaît dans la boîte mail)
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Rappel : Le projet "' . $this->project->name . '" arrive à échéance !',
        );
    }

    /**
     * Le contenu de l'email — pointe vers notre template Markdown
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.project-deadline',
            // On passe les variables disponibles dans le template
            with: [
                'userName'    => $this->user->name,
                'projectName' => $this->project->name,
                'deadline'    => $this->project->deadline->format('d/m/Y'),
                'daysLeft'    => now()->diffInDays($this->project->deadline),
            ],
        );
    }
}