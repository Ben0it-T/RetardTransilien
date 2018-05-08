<?php

/**
 * - load GTFS into database (gtfs)
 * - clean StopPoints
 */

set_time_limit(0);
ini_set('memory_limit', '-1');

// Include configuration
require __DIR__ . '/../app/config/prod.php';

// Vars
$dname = __DIR__ . '/../gtfs';
$routeShortName = $app['retardtransilien']['route_short_name'];
$routeType = $app['retardtransilien']['route_type'];
$dateStart = date("Ymd");
$dateEnd   = date("Ymd", mktime(0, 0, 0, date("m")  , date("d")+7, date("Y")));
$tables = array(
  array('name' => 'transilien_agency', 'file' => 'agency_'.$routeShortName .'.txt'),
  array('name' => 'transilien_calendar', 'file' => 'calendar_'.$routeShortName .'.txt'),
  array('name' => 'transilien_calendar_dates', 'file' => 'calendar_dates_'.$routeShortName .'.txt'),
  array('name' => 'transilien_routes', 'file' => 'routes_'.$routeShortName .'.txt'),
  array('name' => 'transilien_stops', 'file' => 'stops_'.$routeShortName .'.txt'),
  array('name' => 'transilien_stop_times', 'file' => 'stop_times_'.$routeShortName .'.txt'),
  array('name' => 'transilien_trips', 'file' => 'trips_'.$routeShortName .'.txt'),
);



/**
 * Main
 */

// Connect DB
$bdd = new PDO('mysql:host='.$app['db.options']['host'].';dbname='.$app['db.options']['dbname'].';charset='.$app['db.options']['charset'].'',$app['db.options']['user'],$app['db.options']['password'],
    array(
      PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8",
      PDO::MYSQL_ATTR_LOCAL_INFILE => TRUE
    ));

// Set status 'offline'
$bdd->query("UPDATE `transilien_param` SET value='0' WHERE varname = 'app_status' ");

// Update database
foreach ($tables as $table) {
    $bdd->query("TRUNCATE TABLE `" . $table['name'] . "` ");
    $bdd->query("LOAD DATA LOCAL INFILE '".$dname . "/" . $table['file']."' INTO TABLE `" . $table['name'] . "` FIELDS TERMINATED BY ',' ENCLOSED BY '\"' "); 
}

// Clean StopPoints
$req  = "SELECT DISTINCT(STOPS.stop_id) ";
$req .= "FROM transilien_stops as STOPS ";
$req .= "INNER JOIN transilien_stop_times as TIMES on TIMES.stop_id = STOPS.stop_id ";
$req .= "INNER JOIN transilien_trips as TRIPS ON TRIPS.trip_id = TIMES.trip_id ";
$req .= "INNER JOIN transilien_routes ROUTES ON ( ROUTES.route_id = TRIPS.route_id AND ROUTES.route_type = '".$routeType."' AND ROUTES.route_short_name = '".$routeShortName."' ) ";
$req .= "LEFT JOIN transilien_calendar as CALENDAR ON (CALENDAR.service_id = TRIPS.service_id ) ";
$req .= "LEFT JOIN transilien_calendar_dates as CALENDARDATES ON (CALENDARDATES.service_id = TRIPS.service_id AND '".$dateStart."' <= CALENDARDATES.date AND CALENDARDATES.date <= '".$dateEnd."') ";

$req .= "WHERE STOPS.location_type = 0 ";
$req .= "AND  ";
$req .= "(  ";
$req .= "  (  CALENDAR.start_date <= '".$dateStart."' AND '".$dateEnd."' <= CALENDAR.end_date AND ( CALENDARDATES.exception_type IS NULL OR CALENDARDATES.exception_type != 2 ) ) ";
$req .= "  OR ";
$req .= "  (  CALENDARDATES.exception_type = 1  ) ";
$req .= ") ";
$req .="ORDER BY STOPS.stop_name ASC ";
$stopPoints = $bdd->query( $req );

$strStopPoints = "";
foreach ($stopPoints as $stopPoint ) {
    $strStopPoints .= "'" . $stopPoint['stop_id'] . "',";
}
$strStopPoints = rtrim($strStopPoints, ",");

$bdd->query( "DELETE FROM `transilien_stops` WHERE stop_id NOT IN (".$strStopPoints.") " );


// Set db update date
$bdd->query("UPDATE `transilien_param` SET value='".date("Y-m-d")."' WHERE varname = 'db_update' ");

// Set status 'online'
$bdd->query("UPDATE `transilien_param` SET value='1' WHERE varname = 'app_status' ");

exit();
?>