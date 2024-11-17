### **1. Structure de la pipeline**  
La pipeline est structurée autour des **jobs**, des **executors**, et des **workflows** :  
- 🛠️ **Jobs** : Représentent les tâches exécutées, comme l'installation des dépendances, la vérification de la qualité du code, ou le déploiement.  
- 🏗️ **Executors** : Définissent l'environnement d'exécution pour chaque job.  
- 🔄 **Workflows** : Organisent les jobs en séquences ou parallèles avec des dépendances.  

---

### **2. Recommandations de nommage**  
Pour maintenir une configuration lisible et cohérente, nous adoptons une convention de nommage pour les jobs :  
- **Préfixes** : Indiquent le type de tâche (`build-`, `lint-`, `test-`, etc.).  
- **Suffixes** : Spécifient les outils ou technologies utilisées (ex. `-phpcs`, `-phpunit`).  

---

### 🖥️ **3. Executors**  
Les executors définissent les environnements Docker à utiliser pour les jobs :  
```yaml
executors:
  php-executor:
    resource_class: small
    docker:
      - image: cimg/php:8.3
  builder-executor:
    docker:
      - image: cimg/php:8.3-node
  simple-executor:
    docker:
      - image: cimg/base:stable
```  
- 🐘 **php-executor** : Environnement PHP pour analyser et tester le code.  
- 🔧 **builder-executor** : Environnement combinant PHP et Node.js pour construire des images Docker.  
- 🛠️ **simple-executor** : Basique, utilisé pour des scripts généraux.  

---

### 📋 **4. Description des jobs**  

#### 🔍 **a. Jobs de debug**  
Vérifie les variables d’environnement et les chemins disponibles :  
```yaml
debug-info:
  executor: php-executor
  steps:
    - run:
        name: Debug
        command: |
          echo "Current path: $PATH"
          echo "Working directory: $(pwd)"
          env
```  

#### 🏗️ **b. Jobs de construction**  
Télécharge les dépendances du projet avec `composer` :  
```yaml
build-setup:
  executor: php-executor
  steps:
    - checkout
    - restore_cache:
        keys:
          - v1-dependencies-{{ checksum "composer.json" }}
    - run:
        name: Install dependencies
        command: composer install --no-interaction
    - save_cache:
        paths:
          - ./vendor
        key: v1-dependencies-{{ checksum "composer.json" }}
    - *persist_to_workspace
```  

#### 🧹 **c. Analyse de qualité**  
1. 🛡️ **Lint PHP_CodeSniffer (PHPCS)** :  
   - Vérifie la conformité aux standards de code PHP.  
   ```yaml
   lint-phpcs:
     executor: php-executor
     steps:
       - *attach_workspace
       - run:
           name: Install PHP_CodeSniffer
           command: composer require --dev squizlabs/php_codesniffer
       - run:
           name: Analyse Code
           command: ./vendor/bin/phpcs --standard=phpcs.xml .
       - store_artifacts:
           path: phpcs-report.txt
           destination: phpcs-report
   ```  

2. 🚨 **PHP Mess Detector (PHPMD)** :  
   - Détecte les mauvaises pratiques de codage.  
   ```yaml
   lint-phpmd:
     executor: php-executor
     steps:
       - *attach_workspace
       - run:
           name: Install PHPMD
           command: composer require --dev phpmd/phpmd
       - run:
           name: Analyse Code
           command: ./vendor/bin/phpmd ./ text cleancode
       - store_artifacts:
           path: phpmd-report.txt
           destination: phpmd-report
   ```  

#### 🧪 **d. Tests**  
1. **Tests unitaires avec PHPUnit** :  
   - Exécute les tests unitaires définis.  
   ```yaml
   test-phpunit:
     executor: php-executor
     steps:
       - *attach_workspace
       - run:
           name: Install PHPUnit
           command: composer require --dev phpunit/phpunit
       - run:
           name: Run Tests
           command: ./vendor/bin/phpunit
   ```  

---

### 🔄 **5. Workflows**  

#### **Main workflow**  
Définit une séquence logique de jobs :  
```yaml
workflows:
  main_workflow:
    jobs:
      - build-setup
      - lint-phpcs:
          requires:
            - build-setup
      - lint-phpmd:
          requires:
            - build-setup
      - test-phpunit:
          requires:
            - build-setup
```  

---

### **6. Extensions**  
- 📢 **Alertes** : Les artefacts générés (rapports d’analyse ou de sécurité) sont stockés et peuvent déclencher des alertes en cas d’échec.  
