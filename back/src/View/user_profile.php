<?php

$title = 'Mon profil';
?>
<section class="profile">
    <h1>Mon profil</h1>

    <div class="profile-info">
        <p><strong>Email :</strong> <?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
        <p><strong>Rôle :</strong> <?= htmlspecialchars($currentUser['role'] ?? 'Utilisateur') ?></p>
    </div>

    <section class="reservations">
        <h2>Mes réservations</h2>
        <?php if (empty($reservations)): ?>
            <p>Vous n'avez aucune réservation pour le moment.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr><th>Spectacle</th><th>Date</th><th>Quantité</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $res): ?>
                        <tr>
                            <td><?= htmlspecialchars($res['showTitle'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($res['showDate'] ?? '—') ?></td>
                            <td><?= (int)($res['quantity'] ?? 0) ?></td>
                            <td><?= htmlspecialchars($res['status'] ?? 'confirmé') ?></td>
                            <td>
                                <?php if (($res['cancellable'] ?? false)): ?>
                                    <form action="/reservations/<?= urlencode($res['id']) ?>/cancel" method="post" onsubmit="return confirm('Annuler la réservation ?');">
                                        <button class="btn btn-outline" type="submit">Annuler</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</section>