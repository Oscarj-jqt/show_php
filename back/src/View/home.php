<?php

$title = 'Accueil';
?>
<section class="home">
    <h1>Bienvenue sur la gestion des spectacles</h1>
    <p>Consultez la liste des spectacles, réservez vos places et gérez vos réservations depuis votre profil.</p>
    <div class="home-actions">
        <a class="btn" href="?route=show.list">Voir les spectacles</a>
        <?php if (!isset($currentUser)): ?>
            <a class="btn btn-outline" href="?route=user.register">Créer un compte</a>

        <?php else: ?>
            <a class="btn btn-outline" href="?route=user.profile">Accéder à mon profil</a>
        <?php endif; ?>
    </div>
</section>