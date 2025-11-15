#  Santé & Nature — Application Web Symfony

##  Présentation

**Santé & Nature** est une application web interactive dédiée aux **plantes médicinales**, **tisanes** et **recettes saines**.  
Elle permet de rechercher des plantes selon leurs bienfaits (digestion, sommeil, détox, etc.), de découvrir des recettes équilibrées et d’explorer les propriétés naturelles des ingrédients.

> Projet développé dans le cadre du diplôme **Concepteur Développeur d’Applications (CDA 2025)**.

---

##  Fonctionnalités principales

-  **Moteur de recherche** des plantes (par nom, bienfait ou besoin)
-  **Fiches détaillées** sur chaque plante médicinale
-  **Association plantes / tisanes** selon les bienfaits
-  **Recettes saines** avec ingrédients et instructions
-  **Commentaires et avis** sur les recettes ou articles
-  **Interface responsive** et accessible (Bootstrap 5.3)
-  **Back-office sécurisé** pour la gestion des contenus
-  **Déploiement complet via Docker Compose**

---

##  Technologies utilisées

| Technologie | Version | Rôle |
|--------------|----------|------|
| PHP | 8.2 | Langage principal |
| Symfony | 6.4 | Framework backend MVC |
| Doctrine ORM | 3.x | Gestion de la base de données |
| Twig | 3.x | Templates frontend |
| Bootstrap | 5.3 | Mise en page responsive |
| MariaDB | 10.11 | Base de données |
| Docker / Docker Compose | 2.x | Conteneurisation |
| Nginx | 1.27 | Serveur web |

---

##  Installation locale (mode développeur)

###  Prérequis
- [Docker Desktop](https://www.docker.com/products/docker-desktop) installé et lancé
- Git installé

###  Étapes

```bash
# 1. Cloner le projet
git clone https://github.com/ahlembh3/healthyfood.git
cd healthyfood

# 2. Lancer les services Docker
docker compose -f compose.run.yml up -d

# 3. Accéder au site
http://localhost:8082

