# Parfum d'Orient 

Site e-commerce de parfums orientaux développé avec Symfony.

## À propos

Projet réalisé dans le cadre de ma formation **Développeur Web et Web Mobile** chez NEXT FORMATION Vincennes.

Ce site permet aux utilisateurs de consulter un catalogue de parfums, gérer un panier et passer des commandes. Les administrateurs peuvent gérer les produits, catégories et commandes via un back-office.

## Technologies

- **Framework** : Symfony 6.4
- **Langage** : PHP 8.4.14
- **Base de données** : MySQL 8.0
- **Templates** : Twig
- **Design** : Bootstrap 5.3
- **ORM** : Doctrine
- **Emails** : Symfony Mailer (Mailtrap pour les tests)

## Fonctionnalités

### Partie client
- Consultation du catalogue de produits
- Filtrage par catégories (Ambre, Oud, Musc, Elixir)
- Ajout au panier et modification des quantités
- Inscription et connexion sécurisée
- Passation de commandes
- Historique des commandes
- Réception d'emails automatiques (bienvenue, confirmation)

### Partie administrateur
- Dashboard avec statistiques
- Gestion des produits (ajout, modification, suppression)
- Gestion des catégories
- Gestion des commandes (changement de statut)
- Gestion des messages de contact
- Gestion de la newsletter

## Installation

### Prérequis
- PHP 8.4 ou supérieur
- Composer
- MySQL 8.0
- MAMP (ou équivalent)

### Étapes

1. Cloner le dépôt
```bash
git clone https://github.com/BerkaneSelim/-parfums-dorient.git
cd parfums-dorient
```

2. Installer les dépendances
```bash
composer install
```

3. Configuration de la base de données

Créer un fichier `.env.local` à la racine du projet :
```env
DATABASE_URL="mysql://root:root@127.0.0.1:8889/parfums_dorient"
```

4. Créer la base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Lancer le serveur
```bash
symfony server:start
```

Le site est accessible sur `http://localhost:8000`

## Compte de test

**Administrateur :**
- Email : `selim@admin.com`
- Mot de passe : `Selim91200`

## Sécurité

Le projet intègre plusieurs mécanismes de sécurité :
- **Bcrypt** pour le hashage des mots de passe
- **Protection CSRF** sur tous les formulaires (automatique avec Symfony)
- **Protection XSS** via l'échappement automatique de Twig
- **Protection SQL Injection** avec les requêtes préparées de Doctrine
- **Contrôle d'accès** par rôles (ROLE_USER / ROLE_ADMIN)

## Structure de la base de données

7 entités principales :
- **User** : Utilisateurs (clients et admins)
- **Category** : Catégories de produits
- **Product** : Produits en vente
- **Order** : Commandes passées
- **OrderItem** : Articles d'une commande
- **Message** : Messages du formulaire de contact
- **Newsletter** : Inscriptions newsletter

## Difficultés rencontrées

- **Emails asynchrones** : Désactivation du mode async dans messenger.yaml
- **Alignement navbar** : Ajustements CSS personnalisés
- **Templates emails** : Renommage des propriétés pour correspondre aux getters

## Améliorations futures

- Intégration d'un système de paiement en ligne (Stripe)
- Gestion automatique des stocks
- Système d'avis clients
- Graphiques dans le dashboard
- Codes promo et réductions
- Version multilingue

## Auteur

**Berkane Selim**  
Formation DWWM - NEXT FORMATION Vincennes  
Session 2025-2026