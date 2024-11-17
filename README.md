# Documentation de la Pipeline CircleCI

Ce document explique chaque job et chaque workflow.

---

## 📝 Liste des variables d'environnement

Toutes les variables d'environnement accessibles dans ce fichier de configuration se trouvent dans la documentation CircleCI. 

[Consulter la liste des variables d'environnement CircleCI](https://circleci.com/docs/2.0/env-vars/#built-in-environment-variables)

---

## 📦 Exécuteurs 

Les **exécuteurs** définissent l'environnement d'exécution pour chaque job dans le pipeline. Voici la configuration des exécuteurs utilisés dans ce pipeline :

### 1. **php-executor**
- **Image Docker**: `cimg/php:8.3`
- **Commande shell**: `/bin/bash`

### 2. **builder-executor**
- **Image Docker**: `cimg/php:8.3-node`
- **Commande shell**: `/bin/bash`

### 3. **simple-executor**
- **Image Docker**: `cimg/base:stable`
- **Commande shell**: `/bin/bash`

---

## 🔧 Jobs

Voici une explication détaillée des jobs utilisés dans la pipeline :

### 🐞 **debug-info**
Ce job permet de récupérer des informations sur l'environnement d'exécution, utile pour le débogage.

#### Étapes :
- Récupération des informations système (utilisateur, répertoire, etc.)
- Affichage des variables d'environnement

### 🏗️ **build-setup**
Prépare l'environnement pour les étapes suivantes, comme l'installation des dépendances.

### 🛠️ **lint-phpcs**
Vérifie le code PHP avec **PHP_CodeSniffer**, en utilisant un ensemble de règles.

### 📊 **metrics-phpmetrics**
Analyse le code avec **PHP Metrics** pour générer un rapport de qualité du code.

### 📊 **metrics-phploc**
Analyse les métriques du code avec **phploc**.

### 🧹 **lint-phpmd**
Analyse le code à l'aide de **PHP Mess Detector** pour détecter les problèmes de code.

### 🔍 **phpstan**
Effectue une analyse statique du code avec **PHPStan** pour détecter les erreurs et les problèmes potentiels.

### 🧪 **test-phpunit**
Exécute les tests unitaires avec **PHPUnit** si des tests sont présents.

### 🔐 **security-trivy-scan**
Exécute une analyse de sécurité avec **Trivy** sur l'image Docker générée.

### 🐳 **build-docker-image**
Construit une image Docker et la pousse dans le **GitHub Container Registry (GHCR)**.

### 🚀 **deploy-ssh-staging**
Déploie l'application sur un serveur de staging via SSH.

---

## ⚙️ Workflows

### 🛠️ **main_workflow**
Le workflow principal, il contient tous les jobs nécessaires pour configurer, tester et analyser le projet.

#### Jobs inclus :
- `debug-info`
- `build-setup`
- `lint-phpcs`
- `metrics-phpmetrics`
- `metrics-phploc`
- `lint-phpmd`
- `lint-php-doc-check`
- `test-phpunit`
- `phpstan`

### 🐳 **container_workflow**
Ce workflow est spécifique à la construction, l'analyse de sécurité, et le déploiement de l'image Docker.

#### Jobs inclus :
- `build-docker-image`
- `security-trivy-scan`
- `deploy-ssh-staging`

---
