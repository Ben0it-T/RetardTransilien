# RetardTransilien n'est plus maintenu.

# Présentation

RetardTransilien est une application web permettant de visualiser les retards et suppressions en temps réel sur le réseau Transilien, et, d'enregistrer et de partager les incidents subis lors de vos trajets quotidiens.

L'application utilise le framework Silex.

## Comment ça marche ?

L'application utilise plusieurs sources de données :
- les horaires théoriques des lignes Transilien (GTFS)
- l'API Transilien temps réel
- les déclarations des utilisateurs

Les déclarations enregistrés sont attachés à une mission. Ainsi, les retards subis par les voyageurs empruntants le même train ne se cumulent pas : ils renforcent la robustesse des données.

### Prérequis

- php : 5.6
- MySQL : 5.5
- composer

L'utilisation de l'API Transilien temps réel nécessite des identifiants d'accès. La demande d'identifiants à faire depuis la page de l'API : https://ressources.data.sncf.com/explore/dataset/api-temps-reel-transilien/

### Installation

1. Récupérer les sources de l'application.
2. Créer la base de données
3. Importer la structure de la base de données : /db/transilien.sql
4. Récupération de Silex et des différentes dépendances via Composer
```
composer install
```

5. Paramétrer l'application :
Copier le fichier default.prod.php situé dans le répertoire /app/config/ et le renommer prod.php

```
// Doctrine (db)
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'charset'  => 'utf8',
    'host'     => '',
    'port'     => '',
    'dbname'   => '',
    'user'     => '',
    'password' => '',
);

// Apps
$app['retardtransilien'] = array(
    'agency_id'        => 'DUA854',
    'route_short_name' => 'J',
    'route_type'       => '2',
    'api_transilien'    => array(
        0 => array('login' => '', 'passwd' => ''),
    ),
    'gtfs_transilien' => 'http://files.transilien.com/horaires/gtfs/export-TN-GTFS-LAST.zip',
    'realTime_limit' => 5,
    'realTime_reload' => 290,
);
```

```
agency_id : identifiant du réseaux (DUA854 => Paris St Lazare)
route_short_name : nom de la ligne
route_type : type de la ligne
        0 : Tramway
        1 : Métro
        2 : Train
        3 : Bus
        7 : Funiculaire
api_transilien : tableau contenant les identifiants de l'API Transilien temps réel
gtfs_transilien : lien de téléchargement des fichiers GTFS
realTime_limit :  retard minimum (en minutes)
realTime_reload : délai de refraichissement des informations temps rééls (en secondes) 
```

6. Peupler la base de données
- Lancer le script /scripts/update-gtfs.php
- Lancer le script /scripts/update-db.php

## License

RetardTransilien est un logiciel libre sous [Licence Publique Générale GNU Version 2](http://www.linux-france.org/article/these/gpl.html).


