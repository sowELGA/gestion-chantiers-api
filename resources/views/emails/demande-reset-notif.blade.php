<h2>Nouvelle demande de réinitialisation</h2>
<p>Utilisateur : {{ $utilisateur?->nomComplet ?? $demande->email }}</p>
<p>Email concerné : {{ $demande->email }}</p>
<p>Merci de traiter cette demande depuis l'espace Admin.</p>