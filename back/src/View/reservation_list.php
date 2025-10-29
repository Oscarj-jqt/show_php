<?php

$title = 'Réservations';
?>
<section class="reservations-list">
    <h1>Réservations</h1>
    <?php if (empty($reservations)): ?>
        <p>Aucune réservation.</p>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>ID</th><th>Utilisateur</th><th>Spectacle</th><th>Date</th><th>Quantité</th><th>Statut</th></tr></thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['userEmail'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['showTitle'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['showDate'] ?? '—') ?></td>
                        <td><?= (int)($r['quantity'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($r['status'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>