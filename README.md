# roye0040_levi0009/symfony-for-sale
## Authors
 - Royer Lucas
 - Levis Lohan 
## Installation / Configuration
### Install Symfony

```bash
composer install
```
### Get React on the project
```bash
npm install
```

### Get the database with docker
```bash
docker-compose up
```

### Install database
```bash
composer db
```

### Build front assets with Webpack Encore

```bash
npm run watch
```


### Run Symfony server
```bash
composer start
```


## script composer:
1. "start" lance le serveur local de symfony
2. "fix:cs" lance le fix de syntaxe de cs fixer
3. "test:cs" cherche les erreurs de syntax selon cs fixer
4. "test:yaml" test les fichiers yaml dans le répertoire config/ 
5. "test:twig" test les fichiers twig dans le répertoire templates/ 
6. "db" Supprime puis reconstruit la base de données avec ses migrations et enfin insère les données factices

## Identifiants des Users (fixtures)

### admin1:
- email: admin@example.com
- MDP: test

### admin2:
- email: admin2@example.com
- MDP: test

### user1:
- email: user@example.com
- MDP: test

### user1:
- email: user2@example.com
- MDP: test

## Liens pour accèder au projet:
- localhost:8000 (site)
- localhost:8080 (adminer)
- localhost:1080 (mailcatcher)