<?php

namespace RetardTransilien\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RetardTransilien\Domain\Ongoing;
use RetardTransilien\Domain\Incident;
use RetardTransilien\Form\Type\IncidentType;
use RetardTransilien\Form\Type\DatefilterType;
use RetardTransilien\Utils\Stat;
use RetardTransilien\Utils\Util;

Class XhrController {

    /**
     * Get stop point controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function stopPointAction(Request $request, Application $app) {
        if( $request->isXmlHttpRequest() ){
            $lat = preg_replace('/[^0-9\.]/', '', $request->get('lat') );
            $lon = preg_replace('/[^0-9\.]/', '', $request->get('lon') );
            $stop = $app['dao.stop']->findNearest($lat , $lon);
            return $app->json($stop);
        }
        else {
            $error = array('message' => 'Bad request.');
            return $app->json($error, 400);
        }
    }

    /**
     * Get trips controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function tripsAction(Request $request, Application $app) {
        if( $request->isXmlHttpRequest() ){
            $stopPoint1 = preg_replace('/[^a-zA-Z0-9:]/', '', $request->get('stopPoint1') );
            $stopPoint2 = preg_replace('/[^a-zA-Z0-9:]/', '', $request->get('stopPoint2') );
            $time = preg_replace( '/[^0-9:]/', '', $request->get('time') );
            $RouteShortName = $app['retardtransilien']['route_short_name'];
            $RouteType = $app['retardtransilien']['route_type'];

            $stop1 = $app['dao.stop']->find( $stopPoint1 );
            $stop2 = $app['dao.stop']->find( $stopPoint2 );
        
            $uic1 = $app['dao.uic']->findLike( substr($stopPoint1,13) );
            $uic2 = $app['dao.uic']->findLike( substr($stopPoint2,13) );

            $timeHH = intval(substr($time,0,2));
            
            // find all trips
            $trips = $app['dao.trip']->findBy( $stopPoint1, $stopPoint2, $time, date('Y-m-d'), $app['retardtransilien']['route_short_name'], $app['retardtransilien']['route_type'] );
            
            // if time is between 0h and 2h, find yesterday trips where time is between 24h and 26h
            if( in_array( intval(substr($time,0,2)), array(0,1,2) ) ) {
                $trips2 = $app['dao.trip']->findBy( $stopPoint1, $stopPoint2, (24+intval(substr($time,0,2))).":".substr($time,3,2), date('Y-m-d', mktime(0,0,0,date("n"), date("j")-1, date("Y")) ), $app['retardtransilien']['route_short_name'], $app['retardtransilien']['route_type'] );
                $trips = array_merge($trips2,$trips);
            }
            
            // find real time trip (api transilien)
            $trips_realtime = array();
            if( !is_null($uic1) && !is_null($uic2) && $timeHH >= (date("H")-1) &&  $timeHH <= (date("H")+1) ) {
                $url="http://api.transilien.com/gare/".$uic1->getUic()."/depart/".$uic2->getUic()."/";
                foreach ($app['retardtransilien']['api_transilien'] as $apiuser) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_USERPWD, $apiuser['login'] . ':' . $apiuser['passwd'] );
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    $output = curl_exec($ch);
                    $info = curl_getinfo($ch);
                    curl_close($ch);

                    if( $info['http_code'] == 200 ) {
                        $responseXML = simplexml_load_string($output);
                        foreach($responseXML as $train) {
                            $train_date = sprintf('%s' , $train->date);
                            $trainY = substr($train_date, 6,4);
                            $trainM = substr($train_date, 3,2);
                            $trainD = substr($train_date, 0,2);
                            $trainH = substr($train_date, 11,2);
                            $trainI = substr($train_date, 14,2);
                            
                            $trips_realtime[sprintf('%s' ,$train->num)]["mode"] = sprintf('%s' , $train->date->attributes());
                            $trips_realtime[sprintf('%s' ,$train->num)]["etat"] = sprintf('%s' , $train->etat);
                            $trips_realtime[sprintf('%s' ,$train->num)]["timestamp"] = sprintf('%d', mktime($trainH, $trainI, 0, $trainM, $trainD, $trainY));
                        }
                        break;
                    }
                }
            }

            // Manage results
            $data = array();
            if( count($trips) > 0) {
                foreach ($trips as $trip) {
                    // Trip date, time, code and timestamp
                    // Warning : time between 0h and 2h and trip time between 24h and 26h vs 0h and 2h
                    //      if time is 00:10 and trip time is 24:20, the date of trip is {date - 1 day}
                    //      if time is 23:45 and trip time is 24:20, the date of trip is {today}
                    $tripDate = date('Y-m-d');
                    $tripTime = substr($trip->getDepartureTime(),0,5);
                    $tripTimeStamp = mktime(substr($trip->getDepartureTime(),0,2), substr($trip->getDepartureTime(),3,2), 0, date("n"),  date("j"), date("Y"));
                    $tripCode = intval(substr($trip->getTripId(),5,6));
                    
                    if( in_array( intval(substr($time,0,2)), array(0,1,2) ) && in_array( intval(substr($tripTime,0,2)), array(24,25,26) ) ) {
                        $tripDate = date( 'Y-m-d' , mktime(0,0,0,date("n"), date("j")-1, date("Y")) );
                        $tripTimeStamp = mktime(substr($trip->getDepartureTime(),0,2), substr($trip->getDepartureTime(),3,2), 0, date("n"),  date("j")-1, date("Y"));
                    }

                    // Manage departure time and arrival time
                    $departureTime = substr($trip->getDepartureTime(),0,5);
                    $arrivalTime   = substr($trip->getArrivalTime(),0,5);
                        $hh = intval(substr($departureTime,0,2));
                        if( $hh > 23) {
                            $departureTime = substr_replace($departureTime, "0".($hh-24), 0, 2);
                        }
                        $hh = intval(substr($arrivalTime,0,2));
                        if( $hh > 23) {
                            $arrivalTime = substr_replace($arrivalTime, "0".($hh-24), 0, 2);
                        }
                    
                    $realTimeStatus = "";
                    $realTimeDelay  = "";

                    // Incident
                    $incident = $app['dao.incident']->find($trip->getTripId(), $tripDate );
                    $incidentDelay = "";
                    $incidentType = "";
                    if( ! is_null($incident)) {
                        $incidentDelay = number_format($incident->getMedian(),0);
                        $incidentType = $incident->getIncidentType(); 
                    }

                    // Trip 'mode' and 'etat'
                    if( isset($trips_realtime[$tripCode]) ) {
                        if($trips_realtime[$tripCode]["mode"] == "R" ) {
                            $RealTimeDelayTimeStamp = $trips_realtime[$tripCode]["timestamp"];
                            $realTimeDelay = intval(($RealTimeDelayTimeStamp-$tripTimeStamp)/60);
                        }
                        if( isset($trips_realtime[$tripCode]["etat"]) ) {
                            if($trips_realtime[$tripCode]["etat"] !== "") {
                                $realTimeStatus = $trips_realtime[$tripCode]["etat"];
                            }
                        }
                    }

                    // set data
                    $data[] = array(
                        'tripDate' => $tripDate,
                        'tripTime' => $tripTime,
                        'tripId' => $trip->getTripId() ,
                        'stopPoint1' => $stop1->getName(),
                        'stopPoint2' => $stop2->getName(),
                        'departureTime' => $departureTime,
                        'arrivalTime' => $arrivalTime,
                        'headsign' => $trip->getHeadsign(),
                        'routeId' => $trip->getRouteId(),
                        'serviceId' => $trip->getServiceId(),
                        'realTimeStatus' => $realTimeStatus,
                        'realTimeDelay' => $realTimeDelay,
                        'incidentDelay' => $incidentDelay,
                        'incidentType' => $incidentType,
                        'numTrain' => $tripCode
                    );
                }
            }
            return $app->json($data);
        }
        else {
            $error = array('message' => 'Bad request.');
            return $app->json($error, 400);
        }
    }
    
    /**
     * Ongoing controller.
     * Get real time infos
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function ongoingAction(Request $request, Application $app) {
        $status = intval($app['dao.ongoing']->find('ongoing_status')->getValue());
        $ts = intval($app['dao.ongoing']->find('ongoing_ts')->getValue());
        $currentTime = time();
        $diff = $currentTime - $ts;
        
        // $app['retardtransilien']['realTime_reload']
        $realTimeReload = intval($app['retardtransilien']['realTime_reload']);
        if( $diff < $realTimeReload ) {
            // retry after : x seconds
            return $app->json(array('message' => 'retry-after : '.($realTimeReload-$diff).'s'), 200);
        }
        else if ($diff >= $realTimeReload && $status === 0) {
            // in progress
            return $app->json(array('message' => 'in progress'), 200);
        }
        else {
            // Set ongoing_status to 0
            $ongoing = new Ongoing();
            $ongoing->setVarname('ongoing_status');
            $ongoing->setValue( '0' );
            $app['dao.ongoing']->update($ongoing);
            
            // find trips
            $time = date("H:i");
            $date = date('Y-m-d');
            $trips = $app['dao.trip']->findOngoing( $time, date('Y-m-d'), $app['retardtransilien']['route_short_name'], $app['retardtransilien']['route_type'] );

            // if time is between 0h and 2h, find yesterday trips where time is between 24h and 26h
            if( in_array( intval(substr($time,0,2)), array(0,1,2) ) ) {
                $trips2 = $app['dao.trip']->findOngoing( (24+intval(substr($time,0,2))).":".substr($time,3,2), date('Y-m-d', mktime(0,0,0,date("n"), date("j")-1, date("Y")) ), $app['retardtransilien']['route_short_name'], $app['retardtransilien']['route_type'] );
                $trips = array_merge($trips2,$trips);
            }

            // get unique stop uic code
            $tripsStopUic = array();
            foreach ($trips as $trip) {
                if( ! in_array($trip->stopUic1."|".$trip->stopUic2, $tripsStopUic) ) {
                    $tripsStopUic[] = $trip->stopUic1."|".$trip->stopUic2;
                }
            }

            // find real time trip (api transilien)
            $tripsRealtime = array();
            foreach ($tripsStopUic as $stopUic) {
                $sUic = explode("|", $stopUic);
                $url="http://api.transilien.com/gare/".$sUic[0]."/depart/".$sUic[1]."/";
                foreach ($app['retardtransilien']['api_transilien'] as $apiuser) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_USERPWD, $apiuser['login'] . ':' . $apiuser['passwd'] );
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    $output = curl_exec($ch);
                    $info = curl_getinfo($ch);
                    curl_close($ch);

                    if( $info['http_code'] == 200 ) {
                        $responseXML = simplexml_load_string($output);
                        $departureUic = $responseXML->attributes();
                        foreach($responseXML as $train) {
                            $train_date = sprintf('%s' , $train->date);
                            $trainY = substr($train_date, 6,4);
                            $trainM = substr($train_date, 3,2);
                            $trainD = substr($train_date, 0,2);
                            $trainH = substr($train_date, 11,2);
                            $trainI = substr($train_date, 14,2);
                            
                            $tripsRealtime[sprintf('%s' ,$departureUic)][sprintf('%s' ,$train->num)]["mode"] = sprintf('%s' , $train->date->attributes());
                            $tripsRealtime[sprintf('%s' ,$departureUic)][sprintf('%s' ,$train->num)]["etat"] = sprintf('%s' , $train->etat);
                            $tripsRealtime[sprintf('%s' ,$departureUic)][sprintf('%s' ,$train->num)]["term"] = sprintf('%s' , $train->term);
                            $tripsRealtime[sprintf('%s' ,$departureUic)][sprintf('%s' ,$train->num)]["timestamp"] = sprintf('%d', mktime($trainH, $trainI, 0, $trainM, $trainD, $trainY));
                        }
                        break;
                    }
                }
            }
            
            // set status (on time, delayed, cancelled, unknow) to zero
            $ongoingTripStatus['cancelled'] = 0;
            $ongoingTripStatus['delayed'] = 0;
            $ongoingTripStatus['ontime'] = 0;
            $ongoingTripStatus['unknow'] = 0;
            $ongoingTripStatus['data'] = "";

            // Manage results
            if( count($tripsRealtime)>0 ) {
                foreach ($trips as $trip) {
                    $tripCode = intval(substr($trip->tripId,5,6));
                    if( isset($tripsRealtime[$trip->stopUic1][$tripCode]) ) {
                        // Trip date, time, code and timestamp
                        // Warning : time between 0h and 2h and trip time between 24h and 26h vs 0h and 2h
                        //      if time is 00:10 and trip time is 24:20, the date of trip is {date - 1 day}
                        //      if time is 23:45 and trip time is 24:20, the date of trip is {today}
                        $tripDate = date('Y-m-d');
                        $tripTime = substr($trip->departureTime2,0,5);
                        $tripTimeStamp = mktime(substr($trip->departureTime2,0,2), substr($trip->departureTime2,3,2), 0, date("n"),  date("j"), date("Y"));

                        if( in_array( intval(substr($time,0,2)), array(0,1,2) ) && in_array( intval(substr($trip->departureTime2,0,2)), array(24,25,26) ) ) {
                            $tripDate = date( 'Y-m-d' , mktime(0,0,0,date("n"), date("j")-1, date("Y")) );
                            $tripTimeStamp = mktime(substr($trip->departureTime2,0,2), substr($trip->departureTime2,3,2), 0, date("n"),  date("j")-1, date("Y"));
                        }

                        $realTimeStatus = "";
                        $realTimeDelay  = "";
                        $isDelayed = false;
                        $isCancelled = false;

                        if($tripsRealtime[$trip->stopUic1][$tripCode]['mode'] == "R" ) {
                            // real time schedule
                            $RealTimeDelayTimeStamp = $tripsRealtime[$trip->stopUic1][$tripCode]['timestamp'];
                            $realTimeDelay = intval(($RealTimeDelayTimeStamp-$tripTimeStamp)/60);
                            
                            if( $tripsRealtime[$trip->stopUic1][$tripCode]['etat'] ) {
                                switch ($tripsRealtime[$trip->stopUic1][$tripCode]['etat']) {
                                    case 'Retardé':
                                        $ongoingTripStatus['delayed']++;
                                        $isDelayed = true;
                                        break;

                                    case 'Supprimé':
                                        $ongoingTripStatus['cancelled']++;
                                        $isCancelled = true;
                                        break;
                                    
                                    default:
                                }
                            }

                            if( ($realTimeDelay === 0 || $realTimeDelay < 0 ) && ! $isDelayed && ! $isCancelled) {
                                $ongoingTripStatus['ontime']++;
                            }
                            else {
                                
                                if( $realTimeDelay >= $app['retardtransilien']['realTime_limit'] ) {
                                    $ongoingTripStatus['data'] .= $realTimeDelay.",";

                                    $stat = new Stat();
                                    $incident = new Incident();
                                    $incident->setTripId( $trip->tripId );
                                    $incident->setServiceId( $trip->serviceId );
                                    $incident->setHeadsign( $trip->headsign );
                                    $incident->setRouteId( $trip->routeId );
                                    $incident->setDate( $tripDate );
                                    $incident->setIncidentType( '1' );

                                    $incident->setDepartureTime($trip->departureTime);
                                    $incident->setArrivalTime($trip->arrivalTime);
                                    
                                    $incidentData = $app['dao.incident']->find($trip->tripId, $tripDate);
                                    if( ! is_null($incidentData) ) {
                                        $delay = $incidentData->getDelay() . "," . $realTimeDelay;
                                        $median = $stat->median( explode(",", $delay) );
                                        $incident->setDelay( $delay );
                                        $incident->setMedian( $median );
                                        // update incident
                                        $app['dao.incident']->update($incident);
                                    }
                                    else {
                                        // insert incident
                                        $incident->setDelay( $realTimeDelay );
                                        $incident->setMedian( $realTimeDelay );
                                        $app['dao.incident']->insert($incident);
                                    }


                                    if (! $isDelayed && ! $isCancelled ) {
                                        $ongoingTripStatus['delayed']++;
                                    }

                                }

                                if (! $isDelayed && ! $isCancelled && $realTimeDelay <= $app['retardtransilien']['realTime_limit'] ) {
                                    $ongoingTripStatus['ontime']++;
                                }

                            }
                        }
                        else {
                            // horaire théorique : ['mode'] == "T"
                            $ongoingTripStatus['unknow']++;
                        }

                    }
                    else {
                        // not foud in tripsRealtime (api)
                        // soit le train est entre la gare n-1 et la gare n (son terminus)
                        // soit le train ne circule pas (écart entre horaire théorique GTFS et plan de transport du jour)
                        $ongoingTripStatus['unknow']++;
                    }
                }
            }

            // Update database
            $ongoing->setVarname('ongoing_ts');
            $ongoing->setValue( $currentTime );
            $app['dao.ongoing']->update($ongoing);

            $ongoing->setVarname('ongoing_cancelled');
            $ongoing->setValue( $ongoingTripStatus['cancelled'] );
            $app['dao.ongoing']->update($ongoing);

            $ongoing->setVarname('ongoing_delayed');
            $ongoing->setValue( $ongoingTripStatus['delayed'] );
            $app['dao.ongoing']->update($ongoing);

            $ongoing->setVarname('ongoing_ontime');
            $ongoing->setValue( $ongoingTripStatus['ontime'] );
            $app['dao.ongoing']->update($ongoing);

            $ongoing->setVarname('ongoing_unknow');
            $ongoing->setValue( $ongoingTripStatus['unknow'] );
            $app['dao.ongoing']->update($ongoing);

            $ongoing->setVarname('ongoing_data');
            $ongoing->setValue( rtrim($ongoingTripStatus['data'], ",") );
            $app['dao.ongoing']->update($ongoing);
            
            $ongoing->setVarname('ongoing_status');
            $ongoing->setValue( '1' );
            $app['dao.ongoing']->update($ongoing);

            //return
            return $app->json(array('message' => 'ok'), 200);
        }
    }
}