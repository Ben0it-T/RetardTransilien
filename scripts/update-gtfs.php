<?php

/**
 * - get GTFS files
 * - extract data from GTFS files
 */

set_time_limit(0);
ini_set('memory_limit', '-1');

// Include configuration
require __DIR__ . '/../app/config/prod.php';

// Vars
$url = $app['retardtransilien']['gtfs_transilien'];
$agencyId = $app['retardtransilien']['agency_id'];
$routeShortName = $app['retardtransilien']['route_short_name'];
$routeType = $app['retardtransilien']['route_type'];
$dname = __DIR__ . '/../gtfs';
$fname = 'export-TN-GTFS-'.date('Ymd').'.zip';

$aryRoutesId = array(); // array of 'route_id'
$aryServiceId = array(); // array of 'service_id'
$aryTripId = array(); // array of 'trip_id'
$aryStopId = array(); // array of 'stop_id'

$length = 1000;
$delimiter = ","; 
$enclosure = '"';
$escape = "\\";



/**
 * Main
 */

// Download GTFS files
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_HEADER, 0);
$out = curl_exec($ch);
curl_close($ch);
$fp = fopen($dname.'/'.$fname, 'w');
fwrite($fp, $out);
fclose($fp);

// Extract files
$zip = new ZipArchive;
if ($zip->open( $dname.'/'.$fname ) === TRUE) {
    $zip->extractTo( $dname );
    $zip->close();
}

// Extract 'agency.txt'
$fp = fopen($dname . '/agency_'.$routeShortName.'.txt', 'w');
//fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
if( ($handle = fopen($dname . '/agency.txt', 'r')) !== FALSE ) {
    while (($data = fgetcsv($handle, $length, $delimiter, $enclosure)) !== FALSE) {
        if( $data[0] != 'agency_id' ) {
            fputcsv($fp, $data);
        }
    }
    fclose($handle);
}
fclose($fp);


// Extract 'routes.txt'
$fp = fopen($dname . '/routes_'.$routeShortName.'.txt', 'w');
//fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
if( ($handle = fopen($dname . '/routes.txt', 'r')) !== FALSE ) {
    while (($data = fgetcsv($handle, $length, $delimiter, $enclosure)) !== FALSE) {
        if( $data[1] == $agencyId && $data[2] == $routeShortName && $data[5] == $routeType ) {
            $aryRoutesId[] = $data[0];
            fputcsv($fp, $data);
        }
    }
    fclose($handle);
}
fclose($fp);

// Extract 'trips.txt'
$aryServiceIdTmp = array();
$fp = fopen($dname . '/trips_'.$routeShortName.'.txt', 'w');
//fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
if( ($handle = fopen($dname . '/trips.txt', 'r')) !== FALSE ) {
    while (($data = fgetcsv($handle, $length, $delimiter, $enclosure)) !== FALSE) {
        if( in_array( $data[0], $aryRoutesId) ) {
            $aryServiceIdTmp[] = $data[1];
            $aryTripId[]    = $data[2];
            fputcsv($fp, $data);
        }
    }
    fclose($handle);
}
fclose($fp);
$aryServiceId = array_unique($aryServiceIdTmp, SORT_STRING);

// Extract 'calendar.txt' 
$fp = fopen($dname . '/calendar_'.$routeShortName.'.txt', 'w');
//fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
if( ($handle = fopen($dname . '/calendar.txt', 'r')) !== FALSE ) {
    while (($data = fgetcsv($handle, $length, $delimiter, $enclosure)) !== FALSE) {
        if( in_array( $data[0], $aryServiceId) ) {
            fputcsv($fp, $data);
        }
    }
    fclose($handle);
}
fclose($fp);

// Extract 'calendar_dates.txt' 
$fp = fopen($dname . '/calendar_dates_'.$routeShortName.'.txt', 'w');
//fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
if( ($handle = fopen($dname . '/calendar_dates.txt', 'r')) !== FALSE ) {
    while (($data = fgetcsv($handle, $length, $delimiter, $enclosure)) !== FALSE) {
        if( in_array( $data[0], $aryServiceId) ) {
            fputcsv($fp, $data);
        }
    }
    fclose($handle);
}
fclose($fp);

// Extract 'stop_times.txt'
$aryStopIdTmp = array();
$fp = fopen($dname . '/stop_times_'.$routeShortName.'.txt', 'w');
//fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
if( ($handle = fopen($dname . '/stop_times.txt', 'r')) !== FALSE ) {
    while (($data = fgetcsv($handle, $length, $delimiter, $enclosure)) !== FALSE) {
        if( in_array( $data[0], $aryTripId) ) {
            fputcsv($fp, $data);
            $aryStopIdTmp[] = $data[3];
        }
    }
    fclose($handle);
}
fclose($fp);
$aryStopId = array_unique($aryStopIdTmp, SORT_STRING);

// Extract 'stops.txt'
$fp = fopen($dname . '/stops_'.$routeShortName.'.txt', 'w');
//fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
if( ($handle = fopen($dname . '/stops.txt', 'r')) !== FALSE ) {
    while (($data = fgetcsv($handle, $length, $delimiter, $enclosure)) !== FALSE) {
        if( in_array( $data[0], $aryStopId) ) {
            fputcsv($fp, $data);
            
        }
    }
    fclose($handle);
}
fclose($fp);

exit();
?>