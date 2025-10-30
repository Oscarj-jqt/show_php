<?php

$title = $show['title'] ?? 'Fiche spectacle';
?>
<article class="show-detail">
    <h1><?= htmlspecialchars($show['title']) ?></h1>
    <p class="meta">
        <strong>Date :</strong> <?= htmlspecialchars($show['date'] ?? '—') ?>
        <?php if (!empty($show['location'])): ?> — <strong>Lieu :</strong> <?= htmlspecialchars($show['location']) ?><?php endif; ?>
    </p>

    <?php if (!empty($show['image'])): ?>
        <div class="show-image"><img src="<?= htmlspecialchars($show['image']) ?>" alt="<?= htmlspecialchars($show['title']) ?>"/></div>
    <?php endif; ?>

    <div class="show-description">
        <?= nl2br(htmlspecialchars($show['description'] ?? 'Pas de description.')) ?>
    </div>

    <div class="show-meta">
        <p>Places restantes : <strong><?= isset($show['availableSeats']) ? (int)$show['availableSeats'] : '—' ?></strong></p>
        <?php if (!empty($show['price'])): ?><p>Prix : <?= htmlspecialchars($show['price']) ?>€</p><?php endif; ?>
    </div>

    <div class="show-actions">
        <?php if (!isset($currentUser)): ?>
            <p>Vous devez <a href="/?route=user.login">vous connecter</a> pour réserver des places.</p>
        <?php else: ?>
            <?php if (!empty($show['availableSeats'])): ?>
                <form action="/shows/<?= urlencode($show['id']) ?>/reserve" method="post" class="form-inline">
                    <label for="qty">Nombre de places</label>
                    <input id="qty" name="quantity" type="number" min="1" max="<?= (int)$show['availableSeats'] ?>" value="1" required>
                    <button class="btn" type="submit">Réserver</button>
                </form>
            <?php else: ?>
                <p class="muted">Spectacle complet.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (($currentUser['role'] ?? null) === 'admin'): ?>
            <p class="admin-actions">
                <a class="btn btn-outline" href="/admin/shows/<?= urlencode($show['id']) ?>/edit">Modifier</a>
                <form action="/admin/shows/<?= urlencode($show['id']) ?>/delete" method="post" style="display:inline" onsubmit="return confirm('Supprimer ce spectacle ?');">
                    <button class="btn btn-danger" type="submit">Supprimer</button>
                </form>
            </p>
        <?php endif; ?>
    </div>
</article>