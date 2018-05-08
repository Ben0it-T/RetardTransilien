<?php

namespace RetardTransilien\DAO;

use RetardTransilien\Domain\Trip;

class TripDAO extends DAO
{
    /**
     * Return a list of Trip, sorted by departure time.
     *
     * @param string $stopPoint1
     * @param string $stopPoint2
     * @param time $time (H:i)
     * @param date $date (Y-m-d)
     * @param string $routeShortName
     * @param string $routeType
     *
     * @return array A list of Trip.
     */
    public function findBy( $stopPoint1, $stopPoint2, $time, $date, $routeShortName, $routeType ) {
        $stopPoint1 = preg_replace('/[^a-zA-Z0-9:]/', '', $stopPoint1);
        $stopPoint2 = preg_replace('/[^a-zA-Z0-9:]/', '', $stopPoint2);
        $date = preg_replace('/[^0-9\-]/', '', $date);
        $time = explode(':', preg_replace('/[^0-9:]/', '', $time));
        $dateTs = strtotime($date);
        $dateYmd = date("Ymd", $dateTs);
        $timeHH = intval($time[0]);
        $timeMM = intval($time[1]);
        
        switch( date("w", $dateTs) ) {
            case 0:
                $dayOfweek = " CALENDAR.sunday = 1 ";
                break;
            
            case 1:
                $dayOfweek = " CALENDAR.monday = 1 ";
                break;
            
            case 2:
                $dayOfweek = " CALENDAR.tuesday = 1 ";
                break;
            
            case 3:
                $dayOfweek = " CALENDAR.wednesday = 1 ";
                break;
            
            case 4:
                $dayOfweek = " CALENDAR.thursday = 1 ";
                break;
            
            case 5:
                $dayOfweek = " CALENDAR.friday = 1 ";
                break;
                
            case 6:
                $dayOfweek = " CALENDAR.saturday = 1 ";
                break;
        }

        $sql = "SELECT TIMES1.trip_id as tripId, TIMES1.departure_time as departureTime, TIMES2.arrival_time as arrivalTime, TRIPS.trip_headsign as headsign, ROUTES.route_id as routeId, TRIPS.service_id as serviceId ";
        $sql .= "FROM transilien_stop_times as TIMES1 ";
        $sql .= "INNER JOIN transilien_stop_times as TIMES2 ON ( TIMES1.trip_id = TIMES2.trip_id AND TIMES1.stop_sequence < TIMES2.stop_sequence ) ";
        $sql .= "INNER JOIN transilien_trips as TRIPS ON TIMES1.trip_id = TRIPS.trip_id ";
        $sql .= "INNER JOIN transilien_routes ROUTES ON ( TRIPS.route_id = ROUTES.route_id AND ROUTES.route_type = '".$routeType."' AND ROUTES.route_short_name = '".$routeShortName."' ) ";
        $sql .= "LEFT JOIN transilien_calendar as CALENDAR ON ( CALENDAR.service_id = TRIPS.service_id AND ".$dayOfweek.") ";
        $sql .= "LEFT JOIN transilien_calendar_dates as CALENDARDATES ON (CALENDARDATES.service_id = TRIPS.service_id AND CALENDARDATES.date = '".$dateYmd."') ";

        $sql .= "WHERE TIMES1.stop_id = '".$stopPoint1."' AND  TIMES2.stop_id = '".$stopPoint2."' ";
        if($timeHH < 23 ) {
            $sql .= "AND (TIMES1.departure_time BETWEEN '".$timeHH.":".$timeMM.":00' AND '".($timeHH+1).":".$timeMM.":00' ) ";
        }
        else {
            $sql .= " AND TIMES1.departure_time >= '".$timeHH.":".$timeMM.":00' ";
        }
        $sql .= "AND ";
        $sql .= "( ";
        $sql .= "  (  CALENDAR.start_date <= '".$dateYmd."' AND CALENDAR.end_date >= '".$dateYmd."'  AND ( CALENDARDATES.exception_type IS NULL OR CALENDARDATES.exception_type != 2 ) ) ";
        $sql .= "  OR ";
        $sql .= "  (  CALENDARDATES.exception_type = 1  ) ";
        $sql .= ") ";
        $sql .= "ORDER BY TIMES1.departure_time ASC ";

        $result = $this->getDb()->fetchAll($sql);

        // Convert query result to an array of objects
        $trips = array();
        foreach ($result as $row) {
            $trips[] = $this->buildDomainObject($row);
        }
        return $trips;
    }

    /**
     * Find trip matching the supplied params
     *
     * @param string $tripId
     * @param string $serviceId
     * @param string $headsign
     * @param string $routeId
     * @param string $date (Y-m-d)
     * @param string $routeShortName
     * @param string $routeType
     *
     * @return \RetardTransilien\Domain\Trip|null if no matching trip is found
     */
    public function findTripMatching( $tripId, $serviceId, $headsign, $routeId, $date, $routeShortName, $routeType ) {
        $dateTs = strtotime($date);
        $dateYmd = date("Ymd", $dateTs);

        switch( date("w", $dateTs) ) {
            case 0:
                $dayOfweek = " CALENDAR.sunday = 1 ";
                break;
            
            case 1:
                $dayOfweek = " CALENDAR.monday = 1 ";
                break;
            
            case 2:
                $dayOfweek = " CALENDAR.tuesday = 1 ";
                break;
            
            case 3:
                $dayOfweek = " CALENDAR.wednesday = 1 ";
                break;
            
            case 4:
                $dayOfweek = " CALENDAR.thursday = 1 ";
                break;
            
            case 5:
                $dayOfweek = " CALENDAR.friday = 1 ";
                break;
                
            case 6:
                $dayOfweek = " CALENDAR.saturday = 1 ";
                break;
        }

        $sql = "SELECT TRIPS.trip_id as tripId, TRIPS.route_id as routeId, TRIPS.service_id as serviceId, TRIPS.trip_headsign as headsign, T0departureTime as departureTime, T2arrivalTime as arrivalTime ";
        $sql .= "FROM transilien_trips as TRIPS ";
        $sql .= "INNER JOIN transilien_routes as ROUTES ON ( TRIPS.route_id = ROUTES.route_id AND ROUTES.route_type = '".$routeType."' AND ROUTES.route_short_name = '".$routeShortName."' ) ";
        $sql .= "LEFT JOIN transilien_calendar as CALENDAR ON ( CALENDAR.service_id = TRIPS.service_id AND ".$dayOfweek.") ";
        $sql .= "LEFT JOIN transilien_calendar_dates as CALENDARDATES ON (CALENDARDATES.service_id = TRIPS.service_id AND CALENDARDATES.date = '".$dateYmd."') ";
        // departure
        $sql .= "LEFT JOIN ";
        $sql .= "( ";
        $sql .= "  SELECT trip_id, stop_sequence, departure_time as T0departureTime  ";
        $sql .= "  FROM transilien_stop_times ";
        $sql .= ") as T0 on ( T0.trip_id = TRIPS.trip_id  AND T0.stop_sequence = 0 ) ";
        // arrival
        $sql .= "INNER JOIN ";
        $sql .= "( ";
        $sql .= " SELECT MAX(stop_sequence) as MAXSTOPSEQUENCE, trip_id ";
        $sql .= " FROM transilien_stop_times ";
        $sql .= " GROUP BY trip_id ";
        $sql .= ") as T1 on ( T1.trip_id = TRIPS.trip_id ) ";
        // arrival
        $sql .= "LEFT JOIN ";
        $sql .= "( ";
        $sql .= "  SELECT trip_id, stop_sequence, arrival_time as T2arrivalTime  ";
        $sql .= "  FROM transilien_stop_times ";
        $sql .= ") as T2 on ( T2.trip_id = TRIPS.trip_id  AND T2.stop_sequence = T1.MAXSTOPSEQUENCE ) ";

        $sql .= "WHERE TRIPS.trip_id = '".$tripId."' ";
        $sql .= "AND TRIPS.service_id = '".$serviceId."' ";
        $sql .= "AND TRIPS.route_id = '".$routeId."' ";
        $sql .= "AND TRIPS.trip_headsign = '".$headsign."' ";
        $sql .= "AND ";
        $sql .= "( ";
        $sql .= "  (  CALENDAR.start_date <= '".$dateYmd."' AND CALENDAR.end_date >= '".$dateYmd."'  AND ( CALENDARDATES.exception_type IS NULL OR CALENDARDATES.exception_type != 2 ) ) ";
        $sql .= "  OR ";
        $sql .= "  (  CALENDARDATES.exception_type = 1  ) ";
        $sql .= ") ";
        $sql .= "ORDER BY TRIPS.trip_id ASC ";

        $row = $this->getDb()->fetchAssoc($sql);

        

        // Convert query result to an array of objects
        if( $row ) {
            return $this->buildDomainObject($row);
        }
        else {
            return null;
        }

    }

    /**
     * Return a list of current Trip, sorted by departure time.
     *
     * @param time $time (H:i)
     * @param date $date (Y-m-d)
     * @param string $routeShortName
     * @param string $routeType
     *
     * @return array A list of Trip.
     */
    public function findOngoing( $time, $date, $routeShortName, $routeType ) {
        
        $date = preg_replace('/[^0-9\-]/', '', $date);
        $time = preg_replace('/[^0-9:]/', '', $time);
        $dateTs = strtotime($date);
        $dateYmd = date("Ymd", $dateTs);
        
        switch( date("w", $dateTs) ) {
            case 0:
                $dayOfweek = " CALENDAR.sunday = 1 ";
                break;
            
            case 1:
                $dayOfweek = " CALENDAR.monday = 1 ";
                break;
            
            case 2:
                $dayOfweek = " CALENDAR.tuesday = 1 ";
                break;
            
            case 3:
                $dayOfweek = " CALENDAR.wednesday = 1 ";
                break;
            
            case 4:
                $dayOfweek = " CALENDAR.thursday = 1 ";
                break;
            
            case 5:
                $dayOfweek = " CALENDAR.friday = 1 ";
                break;
                
            case 6:
                $dayOfweek = " CALENDAR.saturday = 1 ";
                break;
        }
        
        $sql = "SELECT TIMES.trip_id as tripId, TIMES.departure_time as departureTime, T4arrivalTime as arrivalTime, TRIPS.trip_headsign as headsign, ROUTES.route_id as routeId, TRIPS.service_id as serviceId, ";
        $sql .= " T3stopId, T3uic, T4stopId, T4uic, T3departureTime ";

        $sql .= "FROM transilien_stop_times as TIMES ";
        $sql .= "INNER JOIN transilien_trips as TRIPS ON TRIPS.trip_id = TIMES.trip_id ";
        // -- arrival
        $sql .= "INNER JOIN ";
        $sql .= "( ";
        $sql .= " SELECT MAX(stop_sequence) as MAXSTOPSEQUENCE, trip_id ";
        $sql .= " FROM transilien_stop_times ";
        $sql .= " GROUP BY trip_id ";
        $sql .= ") as T2 on ( T2.trip_id = TIMES.trip_id ) ";
        // -- before arrival
        $sql .= "LEFT JOIN  ";
        $sql .= "( ";
        $sql .= "  SELECT trip_id, stop_sequence, transilien_stops.stop_id as T3stopId, arrival_time as T3arrivalTime, uic as T3uic, departure_time as T3departureTime  ";
        $sql .= "  FROM transilien_stop_times ";
        $sql .= "  INNER JOIN transilien_stops ON transilien_stops.stop_id = transilien_stop_times.stop_id ";
        $sql .= "  LEFT JOIN transilien_uic ON SUBSTRING(uic,1,7) = SUBSTRING(transilien_stops.stop_id,14) ";
        $sql .= ") as T3 on ( T3.trip_id = T2.trip_id  AND T3.stop_sequence = T2.MAXSTOPSEQUENCE-1 ) ";
        // -- arrival
        $sql .= "LEFT JOIN  ";
        $sql .= "( ";
        $sql .= "  SELECT trip_id, stop_sequence, transilien_stops.stop_id as T4stopId, uic as T4uic, arrival_time as T4arrivalTime ";
        $sql .= "  FROM transilien_stop_times ";
        $sql .= "  INNER JOIN transilien_stops ON transilien_stops.stop_id = transilien_stop_times.stop_id ";
        $sql .= "  LEFT JOIN transilien_uic ON SUBSTRING(uic,1,7) = SUBSTRING(transilien_stops.stop_id,14) ";
        $sql .= ") as T4 on ( T4.trip_id = T2.trip_id  AND T4.stop_sequence = T2.MAXSTOPSEQUENCE ) ";

        $sql .= "INNER JOIN transilien_routes as ROUTES ON ( ROUTES.route_id = TRIPS.route_id AND ROUTES.route_type = '".$routeType."' AND ROUTES.route_short_name = '".$routeShortName."' ) ";
        $sql .= "LEFT JOIN transilien_calendar as CALENDAR ON (CALENDAR.service_id = TRIPS.service_id AND ".$dayOfweek.") ";
        $sql .= "LEFT JOIN transilien_calendar_dates as CALENDARDATES ON ( CALENDARDATES.service_id = TRIPS.service_id AND CALENDARDATES.date = '".$dateYmd."' ) ";

        $sql .= "WHERE ";

        $sql .= "TIMES.stop_sequence = 0 AND TIMES.departure_time <= '".$time.":00' ";
        $sql .= "AND T4.T4arrivalTime > '".$time.":00' ";
        $sql .= "AND ";
        $sql .= "(  ";
        $sql .= "  (  CALENDAR.start_date <= '".$dateYmd."' AND CALENDAR.end_date >= '".$dateYmd."' AND ( CALENDARDATES.exception_type IS NULL OR CALENDARDATES.exception_type != 2 ) ) ";
        $sql .= "  OR ";
        $sql .= "  (  CALENDARDATES.exception_type = 1  ) ";
        $sql .= ") ";

        $sql .= "ORDER BY TIMES.departure_time ASC ";
        
        $result = $this->getDb()->fetchAll($sql);

        
        // Convert query result to an array of objects
        $trips = array();
        foreach ($result as $row) {
            $trip = new \stdClass();
            $trip->tripId = $row['tripId'];
            $trip->departureTime = $row['departureTime'];
            $trip->arrivalTime = $row['arrivalTime'];
            $trip->stopId1 = $row['T3stopId'];
            $trip->stopId2 = $row['T4stopId'];
            $trip->stopUic1 = $row['T3uic'];
            $trip->stopUic2 = $row['T4uic'];
            $trip->departureTime2 = $row['T3departureTime'];
            $trip->routeId = $row['routeId'];
            $trip->serviceId = $row['serviceId'];
            $trip->headsign = $row['headsign'];
            $trips[] = $trip;
        }
        return $trips;
    }

    /**
     * Creates a Trips object based on a DB row.
     *
     * @param array $row The DB row containing Trip data.
     * @return RetardTransilien\Domain\Trip
     */
    protected function buildDomainObject(array $row) {
        $trip = new Trip();
        $trip->setTripId($row['tripId']);
        $trip->setRouteId($row['routeId']);
        $trip->setServiceId($row['serviceId']);
        $trip->setHeadsign($row['headsign']);
        $trip->setDepartureTime($row['departureTime']);
        $trip->setArrivalTime($row['arrivalTime']);
        return $trip;
    }
}