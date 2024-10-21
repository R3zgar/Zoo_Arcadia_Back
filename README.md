
# Zoo Arcadia - Back-End

## Description du Projet

**Zoo Arcadia** est une application web conçue pour gérer efficacement un zoo. Le projet permet la gestion complète des animaux, des habitats, des services offerts par le zoo, ainsi que des consultations vétérinaires. Il comprend également des fonctionnalités d'enregistrement, de connexion, et de gestion des utilisateurs avec une authentification sécurisée. Ce projet est développé en **Symfony 6.4** avec une base de données **MySQL**, et l'API est documentée grâce à **NelmioApiDoc**. Chaque entité est équipée de ses opérations CRUD pour permettre la gestion des données en toute simplicité.

## Fonctionnalités Principales

- **Gestion des Animaux** : CRUD complet (ajout, lecture, mise à jour et suppression) pour la gestion des animaux du zoo.
- **Gestion des Habitats** : Chaque animal est associé à un habitat spécifique, comme la Savane, la Jungle ou le Marais.
- **Gestion des Services** : Gérer les services proposés par le zoo, tels que les visites guidées ou les restaurants.
- **Consultations Vétérinaires** : Gestion des rapports de consultations vétérinaires.
- **Enregistrement et Connexion des Utilisateurs** : Inscription et connexion sécurisées pour les utilisateurs avec un système d'authentification API Key.
- **Documentation de l'API** : Documentation complète de l'API accessible via `/api/doc`.
## Prérequis

Avant de commencer, assurez-vous d'avoir les éléments suivants installés sur votre machine :

- **PHP 8.1 ou supérieur**
- **Composer** pour la gestion des dépendances
- **Symfony CLI** (optionnel mais recommandé)
- **MySQL** comme base de données
- **Git** pour le versionnement du code

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

Modifiez le fichier `.env` pour configurer la connexion à votre base de données MySQL :

```
DATABASE_URL="mysql://[utilisateur]:[motdepasse]@127.0.0.1:3306/zooarcadia"
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

### 5. Démarrer le serveur local

Démarrez le serveur de développement Symfony :

```bash
symfony server:start
```

Ou utilisez PHP :

```bash
php -S 127.0.0.1:8000 -t public/
```

### 6. Accéder à la documentation de l'API

Vous pouvez consulter la documentation complète de l'API à l'adresse suivante :

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
