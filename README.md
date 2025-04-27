# GSB Frais - Gestion des Fiches de Frais

**GSB Frais** est une application web permettant de gérer les frais engagés par les visiteurs médicaux et le suivi des remboursements. Elle a pour but de simplifier et sécuriser le processus de saisie des frais, ainsi que leur validation par les services comptables. 

---

## Table des matières

1. [Présentation du projet](#présentation-du-projet)
2. [Prérequis](#prérequis)
3. [Installation](#installation)
4. [Structure du projet](#structure-du-projet)
5. [Mise en place initial du projet](#mise-en-place-initial-du-projet)
6. [Cas d'utilisation](#cas-dutilisation)
    - [Tuto Visiteur](#tuto-visiteur)
    - [Tuto Comptable](#tuto-comptable)
7. [Compétences travaillées](#compétences-travaillées)
8. [Fonctionnalités principales](#fonctionnalités-principales)
9. [Spécificités du projet](#spécificités-du-projet)
10. [Authentification à Deux Facteurs (A2F)](#authentification-à-deux-facteurs-a2f)
11. [Capture d'écrans](#capture-décrans)
12. [Diagramme de classe](#diagramme-de-classe)

---

## Présentation du projet

L'objectif du projet **GSB Frais** est de centraliser la gestion des frais dans une interface unique, à la fois pour les visiteurs médicaux et les services comptables. L'application permet aux utilisateurs (visiteurs) de saisir leurs frais et aux comptables de valider ces frais pour procéder aux remboursements. 

L'application inclut plusieurs fonctionnalités :

- Saisie des frais forfaitisés et hors forfait
- Validation des frais par le service comptable
- Suivi des états de chaque fiche (créée, validée, en paiement, etc.)
- Sécurisation des données avec un accès authentifié
- Mise en paiement des frais validés

---

## Prérequis

Avant de commencer, assurez-vous que vous avez installé les outils suivants sur votre machine :

- [PHP](https://www.php.net/downloads.php) version 7.4 ou supérieure
- [Composer](https://getcomposer.org/download/)
- [Symfony CLI](https://symfony.com/download)
- [MySQL](https://dev.mysql.com/downloads/installer/) ou une autre base de données compatible avec Doctrine
- [Node.js et npm](https://nodejs.org/)

---

## Installation

### Étape 1 : Cloner le projet
Cloner ce projet dans un répertoire local à l'aide de la commande suivante :

```bash
git clone https://github.com/Nozito/GSB_Frais.git
cd GSB_Frais
```
### Etape 2 : Installer les dépendances PHP
Exécutez la commande suivante pour installer les dépendances via Composer :
```bash
composer install
```

### Etape 3 : Configurer les variables d'environnement
```bash
nano .env.local
```
### Etape 4 : Installer les dépendances
```bash
composer install
```
### Etape 5 : Configurer la base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
```

### Étape 6 : Lancer le serveur Symfony
Une fois l'installation terminée, lancez le serveur Symfony avec la commande suivante :
```bash
symfony serve -d
```
Vous pouvez maintenant accéder à l'application GSB sur votre localhost.

## Structure du Projet
Voici un aperçu de la structure du projet :
```bash
GSB-Frais/
│
├── assets/                   # Contient les fichiers CSS, JavaScript et images
│
├── config/                   # Configuration du projet (routes, services, etc.)
│
├── public/                   # Contient les fichiers accessibles publiquement (images, CSS, JS)
│
├── src/                      # Code source (contrôleurs, entités, etc.)
│   ├── Controller/           # Contrôleurs de l'application
│   ├── Entity/               # Entités de la base de données
│   └── Form/                 # Formulaires de l'application
│   └── Repository/           # Repositories pour les requêtes Doctrine
│
├── templates/                # Templates Twig pour l'affichage
│
├── translations/             # Fichiers de traduction
│
├── var/                      # Fichiers temporaires et caches
│
└── vendor/                   # Dépendances de Composer
```
## Mise en place initiale

### Importer les données :

- Rendez-vous sur la route /import
- Importez chacun des élements

### Rôles utilisateurs

- Visiteur médical (ROLE_VISITEUR) : saisie et consultation des frais.
- Comptable (ROLE_COMPTABLE) : validation, correction et traitement des frais.

### Connexion à l'application :

- Se connecter à /login avec un compte ayant les rôles adaptés.

Attention : sans les bons rôles, certaines parties de l'application ne seront pas accessibles.

## Tuto Visiteur

### 1. Se connecter :

- Saisissez vos identifiants dans le formulaire de connexion.
- Si les informations sont valides, vous serez redirigé vers la page d’accueil où vous pouvez consulter et saisir vos frais.
  
### 2.	Saisir une fiche de frais :

- Une fois connecté, vous pouvez saisir vos frais (forfaitisés et hors forfait) pour un mois donné.
- Les frais forfaitisés sont remplis automatiquement avec les montants définis. Pour les frais hors forfait, vous devrez entrer la date, le libellé et le montant.

![image](https://github.com/user-attachments/assets/59a1721b-efcb-4110-aa63-5b6e76593733)

  
### 3.	Clôturer et soumettre :

- À la fin du mois, vous devez soumettre la fiche de frais. Une fois soumise, la fiche sera envoyée au service comptable pour validation.
  
### 4.	Suivi des remboursements :
   
- Vous pouvez suivre l’état de votre fiche de frais (en cours, validée, remboursée) et consulter le montant total remboursé.

![image](https://github.com/user-attachments/assets/341a23f5-3b35-4214-b693-1f7b72c16975)


## Tuto Comptable

### 1.	Se connecter :

- Comme un comptable, vous vous connectez avec vos identifiants pour accéder à l’interface de gestion.
  
### 2.	Valider une fiche de frais :

- Vous pouvez consulter toutes les fiches soumises par les visiteurs pour un mois donné.

![image](https://github.com/user-attachments/assets/5434e028-e7a5-49d9-84a9-bca920dcd165)


- Vous devez valider les frais forfaitisés et vérifier les frais hors forfait. Les frais invalides peuvent être supprimés ou marqués comme “REFUSE”.

![image](https://github.com/user-attachments/assets/61cca8fd-4b78-47c6-9d04-016bb4f0f307)


 
### 3.	Mettre en paiement :

- Une fois validées, les fiches peuvent être mises en paiement par les comptables.
- Vous pouvez également consulter l’historique des paiements effectués.

## Dashboard Comptable

Le Dashboard de l’application offre un aperçu complet des données relatives aux fiches de frais. Il permet aux comptables de consulter rapidement le nombre total de fiches, le montant des fiches... Le Dashboard affiche aussi des statistiques sur le nombre de fiches de frais par mois.

![image](https://github.com/user-attachments/assets/1119bd0b-edf3-4085-8d61-0d48e18e40b8)


## Compétences travaillées
- Symfony Framework : Utilisation du framework Symfony pour la gestion des routes, des entités, des formulaires, et des requêtes SQL via Doctrine.
- Doctrine ORM : Manipulation des entités et des bases de données avec Doctrine.
- Twig : Création des vues avec Twig et gestion de la logique dans les templates.
- JavaScript / CSS : Gestion des interactions front-end et design avec JavaScript et CSS.
- Validation et Authentification : Mise en place d’un système de validation des données et d’authentification sécurisée pour les utilisateurs et les comptables.


## Fonctionnalités principales

### 1.	Saisie des frais :
- Saisie des frais forfaitisés et hors forfait par les visiteurs.
- Validation des frais par le service comptable.
### 2.	Gestion des utilisateurs :
- Interface pour les visiteurs médicaux et les comptables avec des rôles distincts.
### 3.	Suivi des remboursements :
- Suivi de l’état de la fiche de frais (créée, validée, mise en paiement, remboursée).
### 4.	Traitement des fichiers et rapports :
- Gestion de la clôture des fiches de frais en fin de mois et leur validation.

## Spécificités du projet
- Mise en paiement : Une fois validée, la fiche de frais est mise en paiement par les comptables.
- Remboursement différé : Les frais hors forfait peuvent être reportés au mois suivant.
- Validation dynamique : Le système valide les frais forfaitisés et les frais hors forfait avant de les inscrire dans les comptes de l’entreprise.
- Gestion multi-utilisateur : Le système différencie les rôles des utilisateurs, avec des permissions spécifiques pour les visiteurs et les comptables.

## Authentification à Deux Facteurs (A2F)

![image](https://github.com/user-attachments/assets/ade09c2f-f76c-4835-8705-e4fb9c247179)


Afin d’améliorer la sécurité de l’application, l’Authentification à Deux Facteurs (A2F) est mise en place pour tous les utilisateurs ayant accès à l’application, notamment les visiteurs médicaux et les comptables. Ce mécanisme ajoute une couche de sécurité supplémentaire en demandant à l’utilisateur de confirmer son identité via un second facteur, en plus de son mot de passe.

Fonctionnement de l’A2F :
1. Connexion initiale : L’utilisateur saisit son identifiant et son mot de passe.
2. Ajout de l'A2F : Depuis le profil, il est possible d'ajouter l'A2F à son compte. En scannant le Qr code.
3. Vérification du code : L’utilisateur saisit ce code dans l’interface de l’application pour compléter son authentification.

Cette fonctionnalité est cruciale pour renforcer la sécurité des informations sensibles, notamment pour les services comptables, afin de prévenir les tentatives de piratage et d’accès non autorisé.

## Diagramme de classe

![GSB_Frais_Symfony-diagramme de classe](https://github.com/user-attachments/assets/d66a8386-4c68-4490-8ce4-0dfd8c2bae6b)
