@component('mail::message')
# 👋 Bonjour {{ $userName }} !

Nous vous rappelons que votre projet **"{{ $projectName }}"** arrive bientôt à échéance.

@component('mail::panel')
📅 **Date limite :** {{ $deadline }}
⏰ **Jours restants :** {{ $daysLeft }} jour(s)
@endcomponent

Pensez à vérifier l'avancement de vos tâches pour respecter le délai.

@component('mail::button', ['url' => 'http://localhost:3000'])
Voir mes projets
@endcomponent

Bonne continuation !
**L'équipe Gestion Projets**
@endcomponent