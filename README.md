# Zoo Arcadia - Back-End

## Description du Projet

**Zoo Arcadia** est une application web avancée conçue pour la gestion d'un zoo moderne. Ce projet back-end permet une gestion complète des animaux, des habitats, des services et des consultations vétérinaires, avec une authentification sécurisée pour différents rôles (administrateur, employé, vétérinaire). Développé avec **Symfony 6.4** et prenant en charge **MySQL** et **MongoDB** pour le stockage de données, le projet est entièrement documenté et accessible via une API REST, facilitée par **NelmioApiDoc**. Les fonctionnalités CRUD pour chaque entité assurent une gestion souple et intuitive.

## Fonctionnalités Principales

- **Gestion des Animaux** : Création, visualisation, mise à jour et suppression des informations des animaux du zoo.
- **Gestion des Habitats** : Attribution des animaux à leurs habitats (e.g., Savane, Jungle, Marais) avec une structure de relation complète.
- **Gestion des Services** : CRUD des services proposés tels que les visites guidées, les restaurants, etc.
- **Consultations Vétérinaires** : Gestion des rapports de santé des animaux.
- **Authentification Sécurisée** : Inscription et connexion avec des rôles et permissions spécifiques, utilisant un système d'API Key.
- **Documentation API** : Documentation complète accessible via `/api/doc`, facilitant l'intégration et les tests avec Postman.

## Technologies Utilisées

### Backend
- **Symfony 6.4** : Framework back-end principal, avec gestion des routes, de la sécurité et des entités.
- **PHP 8.1+** : Langage de programmation pour le développement du projet.
- **MySQL** : Base de données relationnelle pour le stockage structuré des données.
- **MongoDB** : Utilisé pour le stockage NoSQL, notamment pour les données analytiques et statistiques.
- **Node.js** et **Postman** : Outils pour les tests et la documentation des API.

## Prérequis

Avant de commencer, assurez-vous d'avoir les éléments suivants installés :

- **PHP 8.1 ou supérieur**
- **Composer** pour la gestion des dépendances PHP
- **Symfony CLI** (facultatif mais recommandé)
- **MySQL** et **MongoDB** pour la gestion des données
- **Git** pour le versionnement
- **Postman** pour tester les endpoints de l’API

## Installation

### 1. Cloner le dépôt

Clonez le dépôt GitHub du projet et accédez au répertoire :

```bash
git clone https://github.com/R3zgar/Zoo_Arcadia_Back.git
cd Zoo_Arcadia_Back
```

### 2. Installer les dépendances

Installez toutes les dépendances PHP avec Composer :

```bash
composer install
```

### 3. Configurer la base de données

Modifiez le fichier .env pour configurer les connexions aux bases de données MySQL et MongoDB :

```
DATABASE_URL="mysql://[utilisateur]:[motdepasse]@127.0.0.1:3306/zooarcadia"
MONGODB_URL="mongodb://127.0.0.1:27017"
MONGODB_DB="zooarcadia_analytics"
```

Créez la base de données et exécutez les migrations pour générer les tables :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 4. Charger les données fictives (fixtures)

Chargez les données fictives pour tester les fonctionnalités :

```bash
php bin/console doctrine:fixtures:load
```

### 5. Démarrer le serveur de développement

Démarrez le serveur Symfony local pour tester l'application :

```bash
symfony server:start
```

Ou utilisez PHP :

```bash
php -S 127.0.0.1:8000 -t public/
```
L'application sera accessible à l'adresse http://localhost:8000.


### 6. Accéder à la documentation de l'API

L'API est documentée avec NelmioApiDoc. Vous pouvez consulter la documentation complète de l'API à l'adresse suivante :

```
http://127.0.0.1:8000/api/doc
```

## Authentification des Utilisateurs

### 1. Inscription

- **Route** : `POST /api/register`
- **Corps de la requête** :

```json
{
  "fistName": "John",
  "lastName": "Doe",
  "email": "user@example.com",
  "password": "password123"
}
```

- **Réponse** :

```json
{
  "message": "Utilisateur enregistré avec succès."
}
```

### 2. Connexion

- **Route** : `POST /api/login`
- **Corps de la requête** :

```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

- **Réponse** :

```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInRettrcbsddgvsbz..."
}
```

Utilisez ce token JWT pour toutes les futures requêtes protégées dans l'en-tête HTTP :

```
Authorization: API Key 
Key: X-AUTH-TOKEN
Value: [votre-token]
Add to: Header
```

## Exemples de Requêtes API

### 1. Créer un nouvel animal

- **Route** : `POST /api/animal`
- **Corps de la requête** :

```json
{
  "prenom_animal": "Simba",
  "race_animal": "Lion",
  "etat_animal": "En bonne santé",
  "image": "simba.jpg",
  "habitat_id": 1
}
```

- **Réponse** :

```json
{
  "message": "Nouvel animal créé avec succès!",
  "id": 1
}
```

### 2. Lister tous les services

- **Route** : `GET /api/service`
- **Réponse** :

```json
[
  {
    "id": 1,
    "nom_service": "Visite guidée",
    "description_service": "Une visite guidée du zoo avec un guide expérimenté."
  },
  {
    "id": 2,
    "nom_service": "Petit train",
    "description_service": "Un tour du zoo en petit train pour les familles."
  }
]
```

## Tests

Pour exécuter les tests unitaires et fonctionnels du projet :

```bash
application Postman

php bin/phpunit
```

## Déploiement

## Déploiement sur Platform.sh

1. Assurez-vous que votre projet est activé sur **Platform.sh**.

2. **Ajoutez les fichiers Platform.sh** nécessaires :
    - Créez un fichier `.platform.app.yaml` pour configurer l'application sur Platform.sh.

   Voici un exemple basique :

   ```yaml
   name: zooarcadia
   type: 'php:8.1'
   disk: 1024

   build:
       flavor: composer

   relationships:
       database: 'mysql:mysql'

   web:
       locations:
           '/':
               root: 'public'
               passthru: '/index.php'
   ```

3. **Poussez votre code** sur Platform.sh :

   ```bash
   git add .
   git commit -m "Déploiement sur Platform.sh"
   git push platform main
   ```

4. **Base de données** : Vous pouvez importer votre base de données MariaDB directement depuis MySQL Workbench ou via Platform.sh.

   Utilisez l'interface de **Platform.sh** pour définir les variables d'environnement comme `DATABASE_URL`.


## Auteurs

- **R3zgar** - Développeur principal

## Licence

Ce projet est sous licence **MIT**. Veuillez consulter le fichier LICENSE pour plus de détails.
