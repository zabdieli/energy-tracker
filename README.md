
# Energy Tracker

Energy Tracker est une application Symfony permettant de suivre la consommation d'énergie. Elle est entièrement conteneurisée avec Docker pour un déploiement facile.
---
## Prérequis
- Docker  
- Docker Compose
---
## Installation
### 1. Cloner le projet
```bash
git clone https://github.com/ton-utilisateur/energy-tracker.git
cd energy-tracker
```
### 2. Lancer les conteneurs
```bash
docker-compose up -d --build
```
### 3. Installer les dépendances Symfony
```bash
docker exec -it symfony_app composer install
```
---
## Accéder à l'application
Ouvrir dans un navigateur :
```
http://localhost:8080
```
---
## Commandes utiles
### Ouvrir un terminal dans le conteneur Symfony
```bash
docker exec -it symfony_app bash
```
### Voir toutes les routes Symfony
```bash
php bin/console debug:router
```
### Mettre à jour le schéma de la base de données
```bash
php bin/console doctrine:schema:update --force
```
---
## Démarrer / arrêter l’application
### Arrêter
```bash
docker-compose stop
```
### Redémarrer
```bash
docker-compose up -d
```
### ouvrir un terminal dans le conteneur mysql
```bash
docker exec -it mysql_db mysql -u root -proot symfony
```
### Supprimer complètement les conteneurs et volumes (attention, supprime les données)
```bash
docker-compose down -v
```
---
## Auteur
Zabdiel Kaye
