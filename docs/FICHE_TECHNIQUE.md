FICHE TECHNIQUE — Santé & Nature
1. Contexte et objectifs

Santé & Nature est une application web développée dans le cadre du diplôme Concepteur Développeur d’Applications 2025.
Elle a pour but de sensibiliser les utilisateurs à la phytothérapie et la nutrition saine à travers une base de données interactive sur :

Les plantes médicinales et leurs bienfaits

Les tisanes associées selon le besoin (sommeil, digestion, détox…)

Les recettes saines liées à certains ingrédients naturels.

Le projet vise à démontrer la maîtrise de Symfony, Doctrine, Twig, Docker, et des bonnes pratiques de déploiement.

2. Architecture générale
   2.1 Schéma global (3 conteneurs principaux)
   Service	Rôle	Image utilisée
   db	Base de données MariaDB 10.11	mariadb:10.11
   app	Application PHP Symfony (backend)	ahlembh/healthyfood:<tag>
   web	Serveur web Nginx (front public)	ahlembh/healthyfood-web:<tag>
   2.2 Flux simplifié
   Navigateur → Nginx (web) → PHP-FPM (app) → MariaDB (db)


Les utilisateurs accèdent au site via http://localhost:8082
.

3. Technologies et versions
   Composant	Version	Rôle
   PHP	8.2	Langage principal (Symfony)
   Symfony	6.4	Framework MVC
   Doctrine ORM	3.x	Accès aux données
   Twig	3.x	Moteur de templates
   MariaDB	10.11	Base de données relationnelle
   Bootstrap	5.3	Mise en page responsive
   Docker Compose	2.x	Orchestration des conteneurs
   Nginx	1.27	Serveur HTTP pour la prod
4. Structure du projet
   /src/                → Code PHP (contrôleurs, entités, repositories)
   /templates/          → Vues Twig
   /public/             → Ressources publiques (images, CSS, JS)
   /docker/             → Configurations Docker (nginx.prod.conf, etc.)
   /db-init/            → Script SQL pour données de test
   /docs/               → Documentation technique (fiche technique)
   /var/                → Cache et logs Symfony

5. Base de données

SGBD : MariaDB

Nom de la base : healthytest

Tables principales :

plante : stocke les plantes médicinales

bienfait : propriétés/besoins (ex : sommeil, digestion…)

tisane : associations de plantes

recette : plats sains

tables pivot : plante_bienfait, tisane_plante, etc.

Relation exemple
Plante (n..n) Bienfait
Tisane (n..n) Plante

6. Variables d’environnement

Exemple .env.run :

APP_ENV=prod
APP_DEBUG=0
APP_SECRET=4d5e7914180023991ce2be19d083c342
DATABASE_URL=mysql://hf:hfpass@db:3306/healthytest?charset=utf8mb4

7. Lancement en production (avec Docker Hub)
   7.1 Prérequis

Docker Desktop installé et lancé.

7.2 Étapes
# 1. Créer un dossier healthyfood-run
mkdir healthyfood-run && cd healthyfood-run

# 2. Copier les fichiers nécessaires
# compose.yml
# .env.run
# db-init/healthytest.sql (optionnel)

# 3. Lancer les conteneurs
docker compose -f compose.yml up -d

# 4. Accéder au site
http://localhost:8082

8. Lancement pour développement
   git clone https://github.com/ahlembh3/healthyfood.git
   cd healthyfood
   docker compose -f compose.run.yml up -d


 Symfony tourne avec APP_ENV=dev, utile pour tester ou ajouter du code.

9. Commandes utiles
   Objectif	Commande
   Vérifier les logs Symfony	docker compose exec app tail -n 200 var/log/dev.log
   Vérifier les logs Nginx	docker compose logs -f web
   Recompiler les assets	docker compose exec app php bin/console asset-map:compile
   Effacer / regénérer le cache	docker compose exec app php bin/console cache:clear --env=prod
   Appliquer les migrations	docker compose exec app php bin/console doctrine:migrations:migrate -n --env=prod
10. Publication Docker Hub
    Image App :
    docker build -t ahlembh/healthyfood:1.3 --build-arg APP_ENV=prod .
    docker push ahlembh/healthyfood:1.3

Image Web :
docker build -f Dockerfile.web \
--build-arg APP_IMAGE=ahlembh/healthyfood:1.3 \
-t ahlembh/healthyfood-web:1.3 .
docker push ahlembh/healthyfood-web:1.3

11. Dépannage courant
    Symptôme	Cause possible	Solution
    Page sans style	Vérifier public/assets ou asset-map manquant
    Erreur 500	Cache non généré → cache:warmup
    DB inaccessible	Vérifier DATABASE_URL et service db
    Port 8082 déjà pris	Modifier dans compose.yml → ports: - "8083:80"
12. Sécurité et bonnes pratiques

APP_DEBUG=0 en prod

APP_SECRET unique et confidentiel

Pas de migration auto sur Dockerfile (toujours manuelle)

Logs séparés (app vs web)

Volumes restreints (uploads_run en lecture seule côté web)

13. Évolutions prévues

Ajout de stat

Ajout de nouvelles tisanes et recettes

Moteur de recherche multi-critères amélioré (nom, bienfait, partie utilisée