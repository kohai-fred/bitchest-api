# BitChest - Résumé de l'Application

BitChest est une application de gestion de portefeuille de crypto-monnaies avec une interface d'administration développée en React.js et Vite.js. L'application vise à offrir une expérience utilisateur conviviale pour deux types de profils : les Administrateurs et les Clients.

## Fonctionnalités Principales

### Pour les Administrateurs

-   Gestion des données personnelles.
-   Gestion des clients, y compris la création, l'affichage, la modification et la suppression des données utilisateur.
-   Gestion des droits utilisateur (Administrateur ou Client).
-   Consultation des cours des crypto-monnaies.
-   Affichage de la liste des crypto-monnaies et de leurs cours.

### Pour les Clients

-   Gestion des données personnelles.
-   Gestion du portefeuille, y compris l'affichage du contenu, du solde en euro, et la possibilité de vendre une crypto-monnaie.
-   Consultation des cours des crypto-monnaies.
-   Affichage de la liste des crypto-monnaies et de leurs cours.
-   Consultation de la courbe de progression de chaque crypto-monnaie.
-   Achat de crypto-monnaies au cours actuel.

## Le Portefeuille

-   Chaque client possède un portefeuille privé contenant ses achats en crypto-monnaies.
-   Les clients peuvent voir la liste des crypto-monnaies qu'ils possèdent, les détails des achats (date, quantité, cours), et la plus-value actuelle.

---

## L'installation:

#### Prérequis

Assurez-vous d'avoir Docker et Docker Compose, php ^8 (wsl 2 pour les utilisateur windows) installés et à jour sur votre
système.

Pour récupérer le code source du projet, utilisez la commande suivante :

```bash
git clone https://github.com/kohai-fred/bitchest-api.git
cd bitchest-api
```

2. Installez les dépendances de l'API en exécutant la commande :

```bash
composer require laravel/sail --dev
```

3. Copiez le fichier `.env.example` et renommez-le en `.env`. Ensuite, modifiez les informations de connexion à la base
   de données dans le fichier `.env` comme suit :

```bash
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=bitchest_api
DB_USERNAME=sail
DB_PASSWORD=password
```

4. **Configurer un alias Shell pour Sail** (facultatif) :

Par défaut, les commandes Sail sont invoquées en utilisant le script vendor/bin/sail inclus avec toutes les nouvelles
applications Laravel :

```bash
./vendor/bin/sail up
```

Cependant, au lieu de taper à chaque fois `vendor/bin/sail` pour exécuter les commandes Sail, vous pouvez configurer un
alias shell qui vous permet d'exécuter plus facilement les commandes de Sail :

```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

Pour vous assurer que cet alias est toujours disponible, vous pouvez l'ajouter à votre fichier de configuration shell
dans votre répertoire personnel, tel que ~/.zshrc ou ~/.bashrc, puis redémarrer votre shell.

Une fois que l'alias shell a été configuré, vous pouvez exécuter les commandes Sail en tapant simplement `sail`. Les
exemples du reste de cette documentation supposeront que vous avez configuré cet alias.

5. Lancez l'environnement de développement à l'aide de Docker :

```bash
sail up
```

###### Dans un autre terminal

6. Générez la clé de l'application Laravel :

```bash
sail artisan key:generate
```

7. Exécutez les migrations et les seeders pour créer la base de données avec des données de démonstration :

```bash
sail artisan migrate:fresh --seed
```

8. Pour accéder à PHPMyAdmin, vous pouvez visiter l'URL suivante dans votre navigateur :

```
http://localhost:8001
```

Vous pouvez utiliser les informations de connexion suivantes :

```
Username: sail
Password: password
```
