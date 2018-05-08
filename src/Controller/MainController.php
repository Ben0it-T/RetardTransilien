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

Class MainController {

    /**
     * Main page controller.
     * Declare Incident
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function indexAction(Request $request, Application $app) {
        $stat = new Stat();
        $util = new Util();
        
        // Maintenance Mode
        if( $app['dao.param']->find('app_status')->getValue() == 0) {
            return $app['twig']->render(
                'maintenance.html.twig', 
                array(
                    'routeShortName' => $app['retardtransilien']['route_short_name'],
                )
            ); 
        }

        // Stops
        $stops = $app['dao.stop']->findAll();
        
        // Now
        $now = floor(intval(date("i"))/15)*15;
        if( $now == 0 ) {
            $now = '00';
        }
        $now = strval( date("H") . ":" . $now);
        
        // Times
        $times = array();
        for( $i=0 ; $i < 24 ; $i++ ) {
            if($i < 10) { $zeroH = "0"; } else { $zeroH = ""; }
            for( $j=0 ; $j <= 45 ; $j += 15 ) {
                if($j == 0) { $zeroM = "0"; } else { $zeroM = ""; }
                $times[] = strval( $zeroH . $i . ":" . $zeroM . $j );
            }
        }

        // Incidents Nb : today
        if ( intval(date("w")) == 0) { $theday_sub = 6; }
        else { $theday_sub = intval(date("w")) - 1; }
        $weekFirstDay = date("Y-m-d", mktime(0, 0, 0, date('m'), date('d')-$theday_sub+0, date('Y')));
        $weekLastDay  = date("Y-m-d", mktime(0, 0, 0, date('m'), date('d')-$theday_sub+6, date('Y')));
        $nbIncidentsToday = $app['dao.incident']->getNumberBetween(date('Y-m-d'), date('Y-m-d') );
        
        // Incidents Delay : today
        $todayDelaySum = $util->convertMtoDHM(round($app['dao.incident']->getSumOfMedianBetween( date('Y-m-d'), date('Y-m-d') ) ) );
        
        // Incidents Median : today
        $data = $app['dao.incident']->findMedianInIncidentTypeBetween(array(1, 2, 4), date('Y-m-d') , date('Y-m-d'));
        $median = $stat->quartiles($data);

        // Ongoing
        $ongoingTripStatus['cancelled'] = intval($app['dao.ongoing']->find('ongoing_cancelled')->getValue());
        $ongoingTripStatus['delayed'] = intval($app['dao.ongoing']->find('ongoing_delayed')->getValue());
        $ongoingTripStatus['ontime'] = intval($app['dao.ongoing']->find('ongoing_ontime')->getValue());
        $ongoingTripStatus['unknow'] = intval($app['dao.ongoing']->find('ongoing_unknow')->getValue());
        $ongoingTripStatus['date'] = date( "d/m/Y H:i", intval($app['dao.ongoing']->find('ongoing_ts')->getValue()));
        $ongoingTripStatus['nb'] = $ongoingTripStatus['cancelled'] + $ongoingTripStatus['delayed'] + $ongoingTripStatus['ontime'] + $ongoingTripStatus['unknow'];
        $ongoingTripStatus['median'] = $stat->quartiles( explode(",", $app['dao.ongoing']->find('ongoing_data')->getValue()) );
        $ongoingTripStatus['data'] = $app['dao.ongoing']->find('ongoing_data')->getValue();
        $ongoingTripStatus['delay'] = 0;
        if( $app['dao.ongoing']->find('ongoing_data')->getValue() != '' ) {
            $ongoingTripStatus['delay'] = array_sum(explode ( ',' , $app['dao.ongoing']->find('ongoing_data')->getValue()) );
        }

        // Form
        $incident = new Incident();
        $incidentForm = $app['form.factory']->create(IncidentType::class, $incident);
        $incidentForm->handleRequest($request);
        if ($incidentForm->isSubmitted() && $incidentForm->isValid()) {
            $tripId = preg_replace('/[^a-zA-Z0-9_-]/', '', $incident->getTripId() );
            $serviceId = preg_replace('/[^a-zA-Z0-9]/', '', $incident->getServiceId() );
            $headsign = preg_replace('/[^a-zA-Z0-9]/', '', $incident->getHeadsign() );
            $routeId = preg_replace('/[^a-zA-Z0-9]/', '', $incident->getRouteId() );
            $incidentType = preg_replace('/[^0-9]/', '', $incident->getIncidentType() );
            $delay = preg_replace('/[^0-9]/', '', $incident->getDelay() );
            $date = preg_replace('/[^0-9\-]/', '', $incident->getDate());
            
            // check if the trip exist (tripId, serviceId, headsign, routeId and date)
            $validTrip = $app['dao.trip']->findTripMatching( $tripId, $serviceId, $headsign, $routeId, $date, $app['retardtransilien']['route_short_name'], $app['retardtransilien']['route_type'] );
            $ua = $request->headers->get('User-Agent');
            $validUA = true;
            if( preg_match("/[^e]crawler|spider|bot|custo |web(cow|moni|capture)|slurp|wysigot|httrack|wget|xenu/i",$ua) ) {
                $validUA = false;
            }
            
            if( ! is_null($validTrip) && $validUA ) {
                // incidentType
                // 1 => Retard
                // 2 => Suppression
                // 3 => A l'heure
                // 4 => Modif desserte
                if( $incidentType == 1 && $delay == "0" ) {
                    $incidentType = 3;
                }
                if( $incidentType == 3 && $delay > 0 ) {
                    $incidentType = 1;
                }

                // find Incident
                $incidentData = $app['dao.incident']->find($tripId, $date);
                if( ! is_null($incidentData) ) {
                    $delay = $incidentData->getDelay() . "," . $delay;
                    $sample = explode(",", $delay);
                    $median = $stat->median($sample);

                    // incidentType
                    if( $incidentData->getIncidentType() == 3 && $incidentData->getMedian() > 0 ) {
                        $incidentType = 1;
                    }
                    else {
                        $incidentType = $incidentData->getIncidentType();
                    }
                    $insert = false; // update Incident
                }
                else {
                    // insert
                    $median = $delay;
                    $insert = true; // insert Incident
                }

                // set Incident
                $incident->setTripId( $tripId );
                $incident->setServiceId( $serviceId );
                $incident->setHeadsign( $headsign );
                $incident->setRouteId( $routeId );
                $incident->setDepartureTime( $validTrip->getDepartureTime() );
                $incident->setArrivalTime( $validTrip->getArrivalTime() );
                $incident->setIncidentType( $incidentType );
                $incident->setDelay( $delay );
                $incident->setDate( $date );
                $incident->setMedian( $median );

                // input or update Incident
                if( $insert) {
                    $app['dao.incident']->insert($incident);
                }
                else {
                    $app['dao.incident']->update($incident);
                }
                
                $app['session']->getFlashBag()->add('success', 'Votre déclaration a été enregistrée. Merci !');
            }
            else {
                $app['session']->getFlashBag()->add('error', 'Votre déclaration n\'a été enregistrée.');
            }

            return $app->redirect($app["url_generator"]->generate("declareIncident"));
        }
        $incidentFormView = $incidentForm->createView();
        
        return $app['twig']->render(
            'declare.html.twig',
            array(
                'routeShortName' => $app['retardtransilien']['route_short_name'],
                'stops' => $stops,
                'times' => $times,
                'now' => $now,
                'incidentForm' => $incidentFormView,
                'nbIncidentsToday' => $nbIncidentsToday,
                'todayDelaySum' => $todayDelaySum,
                'ongoingTripStatus' => $ongoingTripStatus,
                'median' => $median,
            )
        );
    }

    /**
     * Export data page controller.
     *
     * @param Application $app Silex application
     */
    public function exportAction(Application $app) {
        return $app['twig']->render(
            'export.html.twig',
            array(
                'routeShortName' => $app['retardtransilien']['route_short_name'],
            )
        );
    }

    /**
     * Export data to CSV controller.
     *
     * @param Application $app Silex application
     */
    public function exportCsvAction(Application $app) {
        $rows = $app['dao.incident']->findAllRows();
        $stream = function() use ($rows) {
            $headers = array( "tripId", "missionId", "tripDate", "serviceId", "tripHeadsign", "routeId", "departureTime", "arrivalTime", "type", "delay", "median");
            $delimiter = ";";
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers, $delimiter);
            foreach ($rows as $row) {
                // incidentType
                // 1 => Retard
                // 2 => Suppression
                // 3 => A l'heure
                // 4 => Modif desserte
                switch($row['incidenttype']) {
                    case "1" :
                        $row['incidenttype'] = "retard";
                        break;
                        
                    case "2" :
                        $row['incidenttype'] = "suppression";
                        break;
                    
                    case "3" :
                        $row['incidenttype'] = "a l'heure";
                        break;
                    
                    case "4" :
                        $row['incidenttype'] = "modif. desserte";
                        break;
                }
                // comma, point and pipe
                $row['delay'] = str_replace ( "," , "|" ,  $row['delay']);
                $row['median'] = str_replace ( "." , "," ,  $row['median']);
                fputcsv($output, $row, $delimiter);
            }
            fclose($output);
        };
        
        return $app->stream($stream, 200, array(
            'Content-Type' => 'text/csv',
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename=transilien.csv',
            'Expires' => '0',
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'no-cache',
        ));
    }

    /**
     * Consult Incidents data page controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function dataAction(Request $request, Application $app) {
        $stat = new Stat();
        $util = new Util();

        // first day & last day of week
        if ( intval(date("w")) == 0) { $theday_sub = 6; }
        else { $theday_sub = intval(date("w")) - 1; }
        $weekFirstDay = date("Y-m-d", mktime(0, 0, 0, date('m'), date('d')-$theday_sub+0, date('Y')));
        $weekLastDay  = date("Y-m-d", mktime(0, 0, 0, date('m'), date('d')-$theday_sub+6, date('Y')));

        $data = array(
            'dateStart' => new \DateTime($weekFirstDay),
            'dateEnd'   => new \DateTime($weekLastDay),
        );

        $form = $app['form.factory']->create(DatefilterType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
        }
        $formView = $form->createView();

        // List of Incidents
        $incidents  = $app['dao.incident']->findAllRowsBetween($data['dateStart']->format('Y-m-d'), $data['dateEnd']->format('Y-m-d'));

        //Nb of incidents
        $nbIncidents[0]  = $app['dao.incident']->getNumberBetween($data['dateStart']->format('Y-m-d'), $data['dateEnd']->format('Y-m-d'));
        for( $i=1 ; $i<=4 ; $i++ ) {
            $nbIncidents[$i] = $app['dao.incident']->getNumberByIncidentTypeBetween($i, $data['dateStart']->format('Y-m-d'), $data['dateEnd']->format('Y-m-d'));
        }
        // Data for chart
        $chartData = array();
        for($i=0 ; $i < 24 ; $i++ ) {
            $chartData[$i] = 0;
        }
        foreach ($incidents as $incident) {
            if( $incident['incidenttype'] == 1) {
                $departure = intval(substr($incident['departuretime'],0,2));
                $arrival = intval(substr($incident['arrivaltime'],0,2));
                if($departure > 23) {
                    $departure -= 24;
                }
                if($arrival > 23) {
                    $arrival -= 24;
                }
                $chartData[$departure]++;
                if( $departure != $arrival) {
                    $chartData[$arrival]++;
                }
            }
        }
        
        // Medians
        $sampleData[0] = $app['dao.incident']->findMedianInIncidentTypeBetween(array(1, 2, 4), $data['dateStart']->format('Y-m-d'), $data['dateEnd']->format('Y-m-d'));
        $sampleData[1] = $app['dao.incident']->findMedianByIncidentTypeBetween(1, $data['dateStart']->format('Y-m-d'), $data['dateEnd']->format('Y-m-d'));
        $sampleData[2] = $app['dao.incident']->findMedianByIncidentTypeBetween(2, $data['dateStart']->format('Y-m-d'), $data['dateEnd']->format('Y-m-d'));
        $sampleData[4] = $app['dao.incident']->findMedianByIncidentTypeBetween(4, $data['dateStart']->format('Y-m-d'), $data['dateEnd']->format('Y-m-d'));
        $medians['global'] = $stat->quartiles($sampleData[0]);
        $medians['delayed'] = $stat->quartiles($sampleData[1]);
        $medians['deleted'] = $stat->quartiles($sampleData[2]);
        $medians['modified'] = $stat->quartiles($sampleData[4]);
        
        // Delays
        $delays[0] = round(array_sum($sampleData[0]));
        $delays[1] = round(array_sum($sampleData[1]));
        $delays[2] = round(array_sum($sampleData[2]));
        $delays[4] = round(array_sum($sampleData[4]));
        $delaysDHMS[0] = $util->convertMtoDHM($delays[0]);
        $delaysDHMS[1] = $util->convertMtoDHM($delays[1]);
        $delaysDHMS[2] = $util->convertMtoDHM($delays[2]);
        $delaysDHMS[4] = $util->convertMtoDHM($delays[4]);

        return $app['twig']->render(
            'data.html.twig', 
            array(
                'routeShortName' => $app['retardtransilien']['route_short_name'],
                'formView' => $formView,
                'nbIncidents' => $nbIncidents,
                'chartData' => $chartData,
                'medians' => $medians,
                'delays' => $delays,
                'delaysDHMS' => $delaysDHMS,
            )
        );
    }

    /**
     * About page controller.
     *
     * @param Application $app Silex application
     */
    public function aboutAction(Application $app) {
        $dbDate = $app['dao.param']->find('db_update')->getValue();
        $agency = $app['dao.agency']->find($app['retardtransilien']['agency_id']);
        
        return $app['twig']->render(
            'about.html.twig',
            array(
                'routeShortName' => $app['retardtransilien']['route_short_name'],
                'dbDate' => $dbDate,
                'agency' => $agency,
            )
        );
    }

}