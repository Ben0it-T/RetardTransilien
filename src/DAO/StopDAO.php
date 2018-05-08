<?php

namespace RetardTransilien\DAO;

use RetardTransilien\Domain\Stop;

class StopDAO extends DAO
{
    /**
     * Returns stop point matching the supplied id.
     *
     * @param string $id
     *
     * @return \RetardTransilien\Domain\Stop|throws an exception if no matching stop point is found
     */
    public function find($id) {
        $sql = "select * from transilien_stops where stop_id=?";
        $row = $this->getDb()->fetchAssoc($sql, array( preg_replace('/[^a-zA-Z0-9:]/', '', $id) ));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("No stop point matching");
    }

    /**
     * Return a list of all stop points, sorted by name.
     *
     * @return array A list of all stop points.
     */
    public function findAll() {
        $sql = "select * from transilien_stops order by stop_name asc";
        $result = $this->getDb()->fetchAll($sql);
        
        // Convert query result to an array of domain objects
        $stopPoints = array();
        foreach ($result as $row) {
            $stopPoints[ $row['stop_id'] ] = $this->buildDomainObject($row);
        }
        return $stopPoints;
    }

    /**
     * Returns the nearest stop point.
     *
     * @param float $lat
     * @param float $lon
     *
     * @return id, name, distance
     */
    public function findNearest( $lat , $lon ) {
        $lat = floatval($lat);
        $lon = floatval($lon);
        
        $sql = "select stop_id as id, stop_name as name, ";
        $sql .= "6371*acos(sin(radians(".$lat."))*sin(radians(`stop_lat`))+cos(radians(".$lat."))*cos(radians(`stop_lat`))*cos(radians(`stop_lon`-".$lon."))) as distance ";
        $sql .= "FROM transilien_stops ";
        $sql .= "ORDER BY `distance` ASC ";
        $sql .= "LIMIT 1 ";
        $result = $this->getDb()->fetchAssoc($sql);
        
        $stop = new \stdClass();
        $stop->id = $result['id'];
        $stop->name = $result['name'];
        $stop->distance = $result['distance'];

        return $stop;
    }

    /**
     * Return a list of all stop points, sorted by distance.
     *
     * @param float $lat
     * @param float $lon
     *
     * @return array A list of all stop points.
     */
    public function findAllOrderByDistance( $lat , $lon ) {
        $lat = floatval($lat);
        $lon = floatval($lon);
        
        $sql = "select stop_id as id, stop_name as name, ";
        $sql .= "6371*acos(sin(radians(".$lat."))*sin(radians(`stop_lat`))+cos(radians(".$lat."))*cos(radians(`stop_lat`))*cos(radians(`stop_lon`-".$lon."))) as distance ";
        $sql .= "FROM transilien_stops ";
        $sql .= "ORDER BY `distance` ASC ";
        $result = $this->getDb()->fetchAll($sql);
        
        $stops = array();
        foreach ($result as $row) {
            $stop = new \stdClass();
            $stop->id = $row['id'];
            $stop->name = $row['name'];
            $stop->distance = $row['distance'];
            $stops[] = $stop;
        }
        return $stops;
    }

    /**
     * Creates a Stop object based on a DB row.
     *
     * @param array $row The DB row containing Stop data.
     * @return RetardTransilien\Domain\Stop
     */
    protected function buildDomainObject(array $row) {
        $stop = new Stop();
        $stop->setId($row['stop_id']);
        $stop->setName($row['stop_name']);
        //$stop->setLat($row['stop_lat']);
        //$stop->setLon($row['stop_lon']);
        return $stop;
    }
}