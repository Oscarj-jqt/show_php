<?php
// filepath: d:\WAMPP\www\spectacle\back\src\View\show_list.php
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
                    <h2><?= htmlspecialchars($show->titre ?? $show['titre']) ?></h2>
                    <p class="meta">
                        <?= htmlspecialchars($show->date ?? $show['date']) ?>
                        — Places : <?= htmlspecialchars($show->seats ?? $show['seats']) ?>
                    </p>
                    <p>
                        <?= nl2br(htmlspecialchars(mb_strimwidth($show->description ?? $show['description'], 0, 250, '...'))) ?>
                    </p>
                    <p class="card-actions">
                        <a class="btn" href="?route=show.detail&id=<?= urlencode($show->id ?? $show['id']) ?>">Voir la fiche</a>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>