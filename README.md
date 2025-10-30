# Site de gestion de spectacles

## Objectif
Développer un site permettant :
- Une page d'accueil publique (message d'accueil, menu)
- La liste des spectacles (publique)
- La fiche d'un spectacle (publique)
- La réservation de places (utilisateurs inscrits)
- L'accès au profil (liste des billets réservés, utilisateurs inscrits)
- L'ajout de spectacles (administrateurs)

L'accès aux pages est conditionné selon le rôle (public, utilisateur, admin).


## Données
Les données sont stockées dans des fichiers JSON dans `/src/Data` :
- `shows.json`
- `users.json`
- `reservations.json`

## Bibliothèques utilisées
- `firebase/php-jwt` : gestion des JWT
- `vlucas/phpdotenv` : gestion de la clé secrète

## Étapes du projet
1. **Données de départ** : création des fichiers JSON
2. **Repositories** : classes pour lire/écrire les fichiers JSON, respect des interfaces
3. **Services métier (Use Cases)** : logique métier (réserver, lister, ajouter, etc.) 
4. **Controllers** : une classe par cas d'utilisation, chaque méthode = une action 
5. **Router** : routeur central qui oriente vers le bon controller 
6. **Vues (front)** : création des pages dans `/src/View` (accueil, liste, fiche spectacle, profil, etc.)
7. **Middleware** : vérification JWT, filtrage par rôle 
8. **JWT & Refresh Token** : génération et vérification des jetons
9. **Tests unitaires** : sur les modèles, use cases, contrôleurs
10. **Sécurité** : hashage des mots de passe, validation des entrées, cookies sécurisés

## Contributeurs
- Oscar JACQUET
- Aryles BEN CHABANE
- Issa ABDOULAYE
