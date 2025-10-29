<?php

$title = 'Spectacles';
?>
<section class="shows-list">
    <h1>Liste des spectacles</h1>

    <?php if (empty($shows)): ?>
        <p>Aucun spectacle disponible pour le moment.</p>
    <?php else: ?>
        <ul class="cards">
            <?php foreach ($shows as $show): ?>
                <li class="card">
                    <h2><?= htmlspecialchars($show['title']) ?></h2>
                    <p class="meta"><?= htmlspecialchars($show['date'] ?? 'Date non précisée') ?> — <?= htmlspecialchars($show['location'] ?? 'Lieu inconnu') ?></p>
                    <p><?= nl2br(htmlspecialchars(mb_strimwidth($show['description'] ?? '', 0, 250, '...'))) ?></p>
                    <p class="card-actions">
                        <a class="btn" href="/shows/<?= urlencode($show['id']) ?>">Voir la fiche</a>
                        <?php if (!empty($show['price'])): ?><span class="muted">Prix: <?= htmlspecialchars($show['price']) ?>€</span><?php endif; ?>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>