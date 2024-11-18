# Documentation de la Pipeline CircleCI

Ce document explique chaque job et chaque workflow.

---

## 📝 Liste des variables d'environnement

Toutes les variables d'environnement accessibles dans ce fichier de configuration se trouvent dans la documentation CircleCI. 

[Consulter la liste des variables d'environnement CircleCI](https://circleci.com/docs/2.0/env-vars/#built-in-environment-variables)

---

## 📦 Exécuteurs

### 1. **php-executor**
Utilisé pour les jobs PHP, comme les tests ou l'analyse de code. L'image Docker `cimg/php:8.3` contient un environnement PHP préconfiguré.

### 2. **builder-executor**
Cet exécuteur est conçu pour les jobs nécessitant à la fois PHP et Node.js, comme l'installation de dépendances ou la gestion d'assets JavaScript. Il utilise l'image `cimg/php:8.3-node`.

### 3. **simple-executor**
Un exécuteur minimaliste avec l'image `cimg/base:stable`, utilisé pour des tâches génériques comme l'analyse de sécurité, sans dépendances PHP ou Node.js spécifiques.

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
Ce job vérifie le code PHP avec **PHP_CodeSniffer** en utilisant un ensemble de règles. Si des problèmes de style de code sont détectés (par exemple, des violations de règles de formatage ou des erreurs de syntaxe), le job échoue avec un message d'erreur détaillant les violations.

- **Comportement** : Si des problèmes sont trouvés dans le code, le job **échoue**.

### 📊 **metrics-phpmetrics**
Ce job analyse le code avec **PHP Metrics** pour générer un rapport détaillant la qualité du code (comme la complexité cyclomatique, la couverture des tests, etc.). 

- **Amélioration possible** : Une amélioration de ce job pourrait consister à rendre les résultats plus facilement exploitables, notamment en ajoutant une visualisation des données (par exemple, un tableau de bord interactif ou des graphiques). Actuellement, les résultats sont principalement en texte ou en format de rapport brut, ce qui peut être difficile à lire pour certains utilisateurs. Il serait intéressant d'explorer comment intégrer ces résultats de manière plus intuitive et visuelle.

### 📊 **metrics-phploc**
Ce job analyse les métriques du code à l’aide de **phploc**. **phploc** mesure des aspects tels que la taille du code, le nombre de classes, de méthodes, ainsi que la complexité. Ces métriques permettent d'avoir un aperçu général de la structure et de la maintenabilité du projet.

- **Amélioration possible** : Il serait intéressant d'ajouter des **conditions sur certaines métriques pour une gate**. Par exemple, définir un seuil pour la complexité cyclomatique, au-delà duquel la pipeline échoue ou nécessite une révision. 

### 🧹 **lint-phpmd**
Ce job utilise **PHP Mess Detector (phpmd)** pour analyser le code PHP et détecter des problèmes potentiels comme des méthodes trop complexes, des classes trop grosses, ou des codes non utilisés. Il suit un ensemble de règles définies pour identifier des mauvaises pratiques dans le code.

- **Comportement** : En cas de problème, l'analyse fait échouer le job. Cela permet d'assurer que des problèmes sérieux ne passent pas inaperçus et n'entrent pas en production.

### 🔍 **phpstan**
**PHPStan** est un outil d'analyse statique qui examine le code PHP pour détecter des erreurs et des problèmes potentiels, tels que des types mal utilisés, des appels de méthodes incorrects, ou des variables non définies.

- **Comportement** : Si des erreurs sont détectées dans le code, **PHPStan** génère un rapport détaillant les problèmes identifiés. Si des **erreurs sont levées**, le job échoue.

### 🧪 **test-phpunit**
Exécute les tests unitaires avec **PHPUnit** si des tests sont présents.

### 🐳 **build-docker-image**
Ce job construit et pousse une image Docker vers le **GitHub Container Registry (GHCR)**.

#### Étapes :
1. **Vérification de `SKIP_BUILD`** : Si définie, le job s'arrête et l'image n'est pas reconstruite.
2. **Préparation du nom et du tag** : Le nom du repository et le tag sont générés à partir du nom de la branche et de l'ID du commit.
3. **Connexion au GHCR** : Le job se connecte au **GitHub Container Registry** à l'aide des informations d'identification sécurisées.
4. **Construction de l'image** : L'image Docker est construite avec l'ID du commit comme tag.
5. **Envoi au GHCR** : L'image est poussée vers le GHCR avec le tag unique.

### 🔐 **security-trivy-scan**
**Trivy** est un outil de sécurité qui analyse les images Docker à la recherche de vulnérabilités dans les dépendances et les configurations.

- **Comportement** : Le scan Trivy ne peut être lancé qu'une fois l'image Docker construite, car il analyse les composants et les dépendances de l'image générée.
- **Impact** : Le scan **Trivy** échouera uniquement si des vulnérabilités de **niveau critique** ou **élevé** sont détectées dans l'image Docker. Les vulnérabilités de niveau moyen ou faible ne provoqueront pas l'échec du job.
- **Conséquence** : En cas d'échec du scan **Trivy** dû à des failles critiques ou élevées, le **deploy-ssh-staging** ne sera pas exécuté, ce qui empêche le déploiement de l'image vulnérable.

### 🚀 **deploy-ssh-staging**
Ce job déploie l'application sur un serveur de staging via SSH.

#### Étapes :
1. **Ajout des clés SSH** : Les clés SSH nécessaires pour l'accès au serveur sont ajoutées.
2. **Modification du `docker-compose.yml`** : Le tag de l'image Docker est remplacé par l'ID du commit.
3. **Transfert du fichier** : Le fichier `docker-compose.yml` est transféré vers le serveur de staging via `scp`.
4. **Déploiement sur le serveur** : Une fois le fichier transféré, le job se connecte au serveur via SSH pour se connecter au **GitHub Container Registry**, puis déploie l'application en utilisant Docker Compose.

---

## 📊 Remontée des erreurs de scan

Les jobs d'analyse (comme `phpcs`, `phpstan`, `phpmd`, etc.) génèrent des rapports d'erreurs qui sont formatés en **JUnit**. Ce format standardisé permet à CircleCI d'afficher les résultats de manière lisible et d'indiquer clairement les problèmes dans le code. 

- **Avantage** : Ce mécanisme garantit une meilleure visibilité sur les erreurs dans la pipeline et permet aux développeurs de traiter rapidement les problèmes détectés, tout en offrant une vue d'ensemble de la qualité du code.

---
## ⚙️ **Workflows et Relations avec les branches**

### 🛠️ **main_workflow**  
Le workflow principal, qui vérifie la qualité du code à travers des tests, des analyses statiques et des métriques. Il s'exécute à chaque push.

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

---

### 🐳 **container_workflow**  
Ce workflow gère la construction de l'image Docker, l'analyse de sécurité, et le déploiement de l'application. Il s'exécute après un merge.

#### Jobs inclus :
- `build-docker-image` 
- `security-trivy-scan` 
- `deploy-ssh-staging` 

#### Conditions :
1. **Construction de l'image Docker** : Le job `build-docker-image` est le premier à s'exécuter. Il génère l'image Docker avec toutes les dépendances.
2. **Analyse de sécurité avec Trivy** : Le scan Trivy est déclenché après la construction. Si des vulnérabilités critiques ou élevées sont détectées, le workflow échoue.
3. **Approbation manuelle avant déploiement** : Si le scan Trivy réussit, le job `deploy-ssh-staging` est mis en attente d'approbation. Un déploiement manuel peut alors être autorisé.
