<?php
// filepath: d:\WAMPP\www\spectacle\back\src\View\register.php
$title = 'Inscription';
?>
<section class="register">
    <h1>Créer un compte</h1>
    <form method="post" action="?route=user.register">
        <label for="username">Nom d'utilisateur</label>
        <input type="text" name="username" id="username" required>

        <label for="name">Nom</label>
        <input type="text" name="name" id="name" required>

        <label for="firstname">Prénom</label>
        <input type="text" name="firstname" id="firstname" required>

        <label for="lastname">Nom de famille</label>
        <input type="text" name="lastname" id="lastname" required>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Mot de passe</label>
        <input type="password" name="password" id="password" required>

        <button type="submit" class="btn">S'inscrire</button>
    </form>
</section>