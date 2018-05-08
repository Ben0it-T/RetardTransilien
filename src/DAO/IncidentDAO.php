<?php

namespace RetardTransilien\DAO;

use RetardTransilien\Domain\Incident;

class IncidentDAO extends DAO
{
    /**
     * Returns incident matching the supplied tripId and date.
     *
     * @param string $tripid
     * @param date $date
     *
     * @return \RetardTransilien\Domain\Incident
     */
    public function find($tripid, $date) {
        $sql = "select * from transilien_data where tripid=? and date=?";
        $row = $this->getDb()->fetchAssoc($sql, array($tripid, $date));

        if ($row)
            return $this->buildDomainObject($row);
        else
            return null;
    }

    /**
     * Return a list of all incidents, sorted by date.
     *
     * @return array A list of all incidents (domain objects).
     */
    public function findAll() {
        $sql = "select * from transilien_data order by date asc";
        $result = $this->getDb()->fetchAll($sql);
        
        // Convert query result to an array of domain objects
        $incident = array();
        foreach ($result as $row) {
            $incident[] = $this->buildDomainObject($row);
        }
        return $incident;
    }

    /**
     * Return a list of all incidents, sorted by date.
     *
     * @return array A list of all incidents (array).
     */
    public function findAllRows() {
        $sql = "SELECT tripid, date, SUBSTRING(tripid,6,6) as missionid, serviceid, headsign, routeid , departuretime, arrivaltime, incidenttype, delay , median  ";
        $sql .= " FROM transilien_data ";
        $sql .= " ORDER BY date ASC, departuretime ASC";
        $result = $this->getDb()->fetchAll($sql);
        
        $incidents = array();
        foreach ($result as $row) {
            $incident = array();
            $incident['tripid'] = $row['tripid'];
            $incident['missionid'] = $row['missionid'];
            $incident['date'] = $row['date'];
            $incident['serviceid'] = $row['serviceid'];
            $incident['headsign'] = $row['headsign'];
            $incident['routeid'] = $row['routeid'];
            $incident['departuretime'] = $row['departuretime'];
            $incident['arrivaltime'] = $row['arrivaltime'];
            $incident['incidenttype'] = $row['incidenttype'];
            $incident['delay'] = $row['delay'];
            $incident['median'] = $row['median'];
            $incidents[] = $incident;
        }
        return $incidents;
    }

    /**
     * Return a list of all incidents between dates, sorted by date ASC departuretime ASC.
     *
     * @param date $dateStart
     * @param date $dateEnd
     *
     * @return array A list of all incidents.
     */
    public function findAllRowsBetween($dateStart, $dateEnd) {
        $sql = "SELECT tripid, date, SUBSTRING(tripid,6,6) as missionid, serviceid, headsign, routeid , departuretime, arrivaltime, incidenttype, delay , median  ";
        $sql .= " FROM transilien_data ";
        $sql .= " where date between ? and ? ";
        $sql .= " ORDER BY date ASC, departuretime ASC";
        $result = $this->getDb()->fetchAll($sql, array($dateStart, $dateEnd));
        
        $incidents = array();
        foreach ($result as $row) {
            $incident = array();
            $incident['tripid'] = $row['tripid'];
            $incident['missionid'] = $row['missionid'];
            $incident['date'] = $row['date'];
            $incident['serviceid'] = $row['serviceid'];
            $incident['headsign'] = $row['headsign'];
            $incident['routeid'] = $row['routeid'];
            $incident['departuretime'] = $row['departuretime'];
            $incident['arrivaltime'] = $row['arrivaltime'];
            $incident['incidenttype'] = $row['incidenttype'];
            $incident['delay'] = $row['delay'];
            $incident['median'] = $row['median'];
            $incidents[] = $incident;
        }
        return $incidents;
    }



    /**
     * Return number of all incidents
     *
     * @return interger Number of incidents
     */
    public function getNumber() {
        $sql = "select count(*) as cnt from transilien_data";
        $result = $this->getDb()->fetchAssoc($sql);

        return $result["cnt"];
    }

    /**
     * Return number of incidents between dates
     *
     * @param date $dateStart (Y-m-d)
     * @param date $dateEnd   (Y-m-d)
     *
     * @return interger Number of incidents
     */
    public function getNumberBetween($dateStart , $dateEnd ) {
        $sql = "select count(*) as cnt from transilien_data where date between ? and ?";
        $result = $this->getDb()->fetchAssoc($sql, array($dateStart, $dateEnd));

        return $result["cnt"];
    }

    /**
     * Return number of incidents between dates matching the supplied incidentType
     *
     * @param char(1) $incidenttype
     * @param date $dateStart (Y-m-d)
     * @param date $dateEnd   (Y-m-d)
     *
     * @return interger Number of incidents
     */
    public function getNumberByIncidentTypeBetween($incidenttype, $dateStart , $dateEnd ) {
        $sql = "select count(*) as cnt from transilien_data where incidenttype=? and (date between ? and ?)";
        $result = $this->getDb()->fetchAssoc($sql, array($incidenttype, $dateStart, $dateEnd));

        return $result["cnt"];
    }

    /**
     * Return sum of incidents delay between dates
     *
     * @param date $dateStart (Y-m-d)
     * @param date $dateEnd   (Y-m-d)
     *
     * @return interger Sum of incidents delay
     */
    public function getSumOfMedianBetween($dateStart, $dateEnd) {
        $sql = "select sum(median) as total from transilien_data where date between ? and ?";
        $result = $this->getDb()->fetchAssoc($sql, array($dateStart, $dateEnd));

        return $result["total"];
    }

    /**
     * Return array of 'median' rows
     *
     * @return array A list of all 'median' rows.
     */
    public function findAllMedian() {
        $sql = "select median from transilien_data";
        $result = $this->getDb()->fetchAll($sql);
        
        // Convert query result to an array
        $median = array();
        foreach ($result as $row) {
            $median[] = $row['median'];
        }
        return $median;
    }

    /**
     * Return array of 'median' rows matching the supplied incidentType
     *
     * @param sting $incidenttype (char(1))
     *
     * @return array A list of all 'median' rows.
     */
    public function findMedianByIncidentType($incidenttype) {
        $sql = "select median from transilien_data where incidenttype=?";
        $result = $this->getDb()->fetchAll($sql, array($incidenttype) );
        
        // Convert query result to an array
        $median = array();
        foreach ($result as $row) {
            $median[] = $row['median'];
        }
        return $median;
    }

    /**
     * Return array of 'median' rows matching the supplied incidentType between dates
     *
     * @param string $incidenttype (char(1))
     * @param date $dateStart (Y-m-d)
     * @param date $dateEnd   (Y-m-d)
     *
     * @return array A list of 'median' rows.
     */
    public function findMedianByIncidentTypeBetween($incidenttype, $dateStart , $dateEnd) {
        $sql = "select median from transilien_data where incidenttype=? and (date between ? and ?)";
        $result = $this->getDb()->fetchAll($sql, array($incidenttype, $dateStart, $dateEnd) );
        
        // Convert query result to an array
        $median = array();
        foreach ($result as $row) {
            $median[] = $row['median'];
        }
        return $median;
    }

    /**
     * Return array of 'median' rows matching the supplied incidentType
     *
     * @param array $incidenttype
     *
     * @return array A list of all 'median' rows.
     */
    public function findMedianInIncidentType($incidenttype) {
        $sql = "select median from transilien_data where incidenttype in (?)";
        $result = $this->getDb()->fetchAll($sql, array($incidenttype), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY) );
        
        // Convert query result to an array
        $median = array();
        foreach ($result as $row) {
            $median[] = $row['median'];
        }
        return $median;
    }

    /**
     * Return array of 'median' rows matching the supplied incidentType between dates
     *
     * @param array $incidenttype
     * @param date $dateStart (Y-m-d)
     * @param date $dateEnd   (Y-m-d)
     *
     * @return array A list of 'median' rows.
     */
    public function findMedianInIncidentTypeBetween($incidenttype, $dateStart , $dateEnd) {
        $sql = "select median from transilien_data where (date between ? and ?) and incidenttype in (?)";
        $result = $this->getDb()->fetchAll(
                $sql,
                array(
                    $dateStart,
                    $dateEnd,
                    $incidenttype
                ),
                array(
                    \PDO::PARAM_STR,
                    \PDO::PARAM_STR,
                    \Doctrine\DBAL\Connection::PARAM_INT_ARRAY
                )
        );
        
        // Convert query result to an array
        $median = array();
        foreach ($result as $row) {
            $median[] = $row['median'];
        }
        return $median;
    }

    /**
     * Insert an incident into the database.
     *
     * @param \RetardTransilien\Domain\Incident $incident The incident to insert
     */
    public function insert(Incident $incident) {
        $incidentData = array(
            'tripid' => $incident->getTripId(),
            'date' => $incident->getDate(),
            'serviceid' => $incident->getServiceId(),
            'headsign' => $incident->getHeadsign(),
            'routeid' => $incident->getRouteId(),
            'departuretime' => $incident->getDepartureTime(),
            'arrivaltime' => $incident->getArrivalTime(),
            'incidentType' => $incident->getIncidentType(),
            'delay' => $incident->getDelay(),
            'median' => $incident->getMedian()
            );
        $this->getDb()->insert('transilien_data', $incidentData);
    }

    /**
     * Update an incident into the database.
     *
     * @param \RetardTransilien\Domain\Incident $incident The incident to update
     */
    public function update(Incident $incident) {
        $incidentData = array(
            'tripid' => $incident->getTripId(),
            'date' => $incident->getDate(),
            'serviceid' => $incident->getServiceId(),
            'headsign' => $incident->getHeadsign(),
            'routeid' => $incident->getRouteId(),
            'departuretime' => $incident->getDepartureTime(),
            'arrivaltime' => $incident->getArrivalTime(),
            'incidentType' => $incident->getIncidentType(),
            'delay' => $incident->getDelay(),
            'median' => $incident->getMedian()
            );
        $this->getDb()->update('transilien_data', $incidentData, array('tripid' => $incident->getTripId(), 'date' => $incident->getDate()) );
    }



    /**
     * Creates an Incident object based on a DB row.
     *
     * @param array $row The DB row containing Incident data.
     * @return RetardTransilien\Domain\Incident
     */
    protected function buildDomainObject(array $row) {
        $incident = new Incident();
        $incident->setTripId($row['tripid']);
        $incident->setDate($row['date']);
        $incident->setServiceId($row['serviceid']);
        $incident->setHeadsign($row['headsign']);
        $incident->setRouteId($row['routeid']);
        $incident->setDepartureTime($row['departuretime']);
        $incident->setArrivalTime($row['arrivaltime']);
        $incident->setIncidentType($row['incidenttype']);
        $incident->setDelay($row['delay']);
        $incident->setMedian($row['median']);
        return $incident;
    }
}