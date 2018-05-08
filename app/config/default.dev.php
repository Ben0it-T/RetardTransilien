<?php

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

// Enable the debug mode
$app['debug'] = true;